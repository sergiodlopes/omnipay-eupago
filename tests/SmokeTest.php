<?php

namespace Omnipay\Eupago\Tests;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Eupago\Message\MBWayRequest;
use Omnipay\Eupago\Message\MBWayResponse;
use Omnipay\Eupago\Message\MultibancoRequest;
use Omnipay\Eupago\Message\MultibancoResponse;
use Omnipay\Eupago\Message\PagaquiRequest;
use Omnipay\Eupago\Message\PagaquiResponse;
use Omnipay\Eupago\Message\PayShopRequest;
use Omnipay\Eupago\Message\PayShopResponse;
use Omnipay\Eupago\Message\ReferenceStatusRequest;
use Omnipay\Eupago\Message\ReferenceStatusResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class SmokeTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function psr7Response(int $status, array $body): Psr7Response
    {
        return new Psr7Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    private function httpClient(): Mockery\MockInterface
    {
        return Mockery::mock(ClientInterface::class);
    }

    private function newRequest(string $class, array $params = []): array
    {
        $client = $this->httpClient();
        $request = new $class($client, HttpRequest::create('/'));
        foreach ($params as $key => $value) {
            $request->{'set' . ucfirst($key)}($value);
        }
        return [$request, $client];
    }

    private function baseParams(): array
    {
        return [
            'apiKey'        => 'demo-0000-0000-0000',
            'transactionId' => 'ORDER-001',
            'amount'        => '10.00',
            'currency'      => 'EUR',
        ];
    }

    // -------------------------------------------------------------------------
    // URL routing
    // -------------------------------------------------------------------------

    public function testDemoKeyRoutesToSandboxLegacyRest()
    {
        [$req] = $this->newRequest(MultibancoRequest::class, ['apiKey' => 'demo-0000-0000-0000']);
        $this->assertStringContainsString('sandbox.eupago.pt/clientes/rest_api', $req->getUrl());
    }

    public function testLiveKeyRoutesToProductionLegacyRest()
    {
        [$req] = $this->newRequest(MultibancoRequest::class, ['apiKey' => 'live-0000-0000-0000']);
        $this->assertStringContainsString('clientes.eupago.pt/clientes/rest_api', $req->getUrl());
        $this->assertStringNotContainsString('sandbox', $req->getUrl());
    }

    public function testDemoKeyRoutesToSandboxApiKeyEndpoint()
    {
        [$req] = $this->newRequest(MBWayRequest::class, ['apiKey' => 'demo-0000-0000-0000']);
        $this->assertStringContainsString('sandbox.eupago.pt/api/v1.02', $req->getApiKeyUrl());
    }

    public function testLiveKeyRoutesToProductionApiKeyEndpoint()
    {
        [$req] = $this->newRequest(MBWayRequest::class, ['apiKey' => 'live-0000-0000-0000']);
        $this->assertStringContainsString('clientes.eupago.pt/api/v1.02', $req->getApiKeyUrl());
        $this->assertStringNotContainsString('sandbox', $req->getApiKeyUrl());
    }

    // -------------------------------------------------------------------------
    // Multibanco — legacy body-auth REST
    // -------------------------------------------------------------------------

    public function testMultibancoPostsToCorrectEndpointWithCorrectBody()
    {
        [$req, $client] = $this->newRequest(MultibancoRequest::class, $this->baseParams());

        $client->shouldReceive('request')
            ->once()
            ->with(
                'POST',
                'https://sandbox.eupago.pt/clientes/rest_api/multibanco/create',
                Mockery::on(fn($h) => ($h['Content-Type'] ?? '') === 'application/json'),
                Mockery::on(function ($body) {
                    $d = json_decode($body, true);
                    return $d['chave'] === 'demo-0000-0000-0000'
                        && $d['id']    === 'ORDER-001'
                        && $d['valor'] === '10.00';
                })
            )
            ->andReturn($this->psr7Response(200, [
                'resposta'   => 'OK',
                'referencia' => '123456789',
                'entidade'   => '11111',
                'valor'      => '10.00',
                'estado'     => 'por pagar',
            ]));

        $response = $req->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('123456789', $response->getTransactionReference());
        $this->assertEquals('11111', $response->getEntity());
        $this->assertEquals('10.00', $response->getValue());
        $this->assertEquals('por pagar', $response->getStatus());
    }

    // -------------------------------------------------------------------------
    // PayShop — legacy body-auth REST
    // -------------------------------------------------------------------------

    public function testPayShopPostsToCorrectEndpoint()
    {
        [$req, $client] = $this->newRequest(PayShopRequest::class, $this->baseParams());

        $client->shouldReceive('request')
            ->once()
            ->with('POST', 'https://sandbox.eupago.pt/clientes/rest_api/payshop/create', Mockery::any(), Mockery::any())
            ->andReturn($this->psr7Response(200, ['resposta' => 'OK', 'referencia' => '987654321']));

        $response = $req->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('987654321', $response->getTransactionReference());
    }

    // -------------------------------------------------------------------------
    // MBWay — v1.02 ApiKey REST
    // -------------------------------------------------------------------------

    public function testMBWayPhoneFormattedWithCountryCode()
    {
        [$req] = $this->newRequest(MBWayRequest::class, array_merge($this->baseParams(), ['alias' => '912345678']));
        // Alias is stored after validation; phone prefix applied during _makeRequest
        $this->assertEquals('912345678', $req->getAlias());
    }

    public function testMBWayPostsToV102WithApiKeyHeaderAndPaymentObject()
    {
        [$req, $client] = $this->newRequest(MBWayRequest::class, array_merge($this->baseParams(), ['alias' => '912345678']));

        $client->shouldReceive('request')
            ->once()
            ->with(
                'POST',
                'https://sandbox.eupago.pt/api/v1.02/mbway/create',
                Mockery::on(fn($h) => ($h['Authorization'] ?? '') === 'ApiKey demo-0000-0000-0000'),
                Mockery::on(function ($body) {
                    $d = json_decode($body, true);
                    return isset($d['payment'])
                        && $d['payment']['amount']     === '10.00'
                        && $d['payment']['identifier'] === 'ORDER-001'
                        && $d['payment']['phone']      === '351#912345678';
                })
            )
            ->andReturn($this->psr7Response(201, ['transactionID' => 'TXN-MB-001']));

        $response = $req->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('TXN-MB-001', $response->getTransactionReference());
    }

    // -------------------------------------------------------------------------
    // Pagaqui — v1.02 ApiKey REST
    // -------------------------------------------------------------------------

    public function testPagaquiPostsToV102WithApiKeyHeaderAndPaymentObject()
    {
        [$req, $client] = $this->newRequest(PagaquiRequest::class, $this->baseParams());

        $client->shouldReceive('request')
            ->once()
            ->with(
                'POST',
                'https://sandbox.eupago.pt/api/v1.02/pagaqui/create',
                Mockery::on(fn($h) => ($h['Authorization'] ?? '') === 'ApiKey demo-0000-0000-0000'),
                Mockery::on(function ($body) {
                    $d = json_decode($body, true);
                    return isset($d['payment'])
                        && $d['payment']['amount']     === '10.00'
                        && $d['payment']['identifier'] === 'ORDER-001';
                })
            )
            ->andReturn($this->psr7Response(201, ['transactionID' => 'TXN-PQ-001']));

        $response = $req->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('TXN-PQ-001', $response->getTransactionReference());
    }

    // -------------------------------------------------------------------------
    // Reference status check — legacy body-auth REST
    // -------------------------------------------------------------------------

    public function testReferenceStatusPostsToMultibancoInfo()
    {
        [$req, $client] = $this->newRequest(ReferenceStatusRequest::class, [
            'apiKey'               => 'demo-0000-0000-0000',
            'transactionReference' => '123456789',
        ]);

        $client->shouldReceive('request')
            ->once()
            ->with(
                'POST',
                'https://sandbox.eupago.pt/clientes/rest_api/multibanco/info',
                Mockery::any(),
                Mockery::on(function ($body) {
                    $d = json_decode($body, true);
                    return $d['chave']      === 'demo-0000-0000-0000'
                        && $d['referencia'] === '123456789';
                })
            )
            ->andReturn($this->psr7Response(200, [
                'resposta'          => 'OK',
                'estado_referencia' => 'paga',
                'data_pagamento'    => '2026-05-26',
            ]));

        $response = $req->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isPaid());
        $this->assertEquals('paga', $response->getStatus());
        $this->assertEquals('2026-05-26', $response->getPaymentDate());
    }

    // -------------------------------------------------------------------------
    // BC fallbacks — response field aliases
    // -------------------------------------------------------------------------

    public function testMultibancoResponseFallsBackToNewFieldNames()
    {
        $mockReq = Mockery::mock(\Omnipay\Common\Message\RequestInterface::class);
        $mockReq->shouldReceive('getTransactionId')->andReturn('ORDER-001');

        $data = (object)[
            'resposta'      => 'OK',
            'transactionID' => 'TXN-NEW',
            'entity'        => '22222',
            'amount'        => '25.00',
            'status'        => 'por pagar',
        ];

        $response = new MultibancoResponse($mockReq, $data);

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('TXN-NEW', $response->getTransactionReference());
        $this->assertEquals('22222', $response->getEntity());
        $this->assertEquals('25.00', $response->getValue());
        $this->assertEquals('por pagar', $response->getStatus());
    }

    public function testMultibancoResponsePrefersLegacyFieldsWhenBothPresent()
    {
        $mockReq = Mockery::mock(\Omnipay\Common\Message\RequestInterface::class);
        $mockReq->shouldReceive('getTransactionId')->andReturn('ORDER-001');

        $data = (object)[
            'resposta'      => 'OK',
            'referencia'    => 'REF-LEGACY',
            'transactionID' => 'TXN-NEW',
            'entidade'      => '11111',
            'entity'        => '22222',
            'valor'         => '10.00',
            'amount'        => '25.00',
        ];

        $response = new MultibancoResponse($mockReq, $data);

        $this->assertEquals('REF-LEGACY', $response->getTransactionReference());
        $this->assertEquals('11111', $response->getEntity());
        $this->assertEquals('10.00', $response->getValue());
    }

    public function testMBWayResponseFallsBackToTransactionId()
    {
        $mockReq = Mockery::mock(\Omnipay\Common\Message\RequestInterface::class);
        $mockReq->shouldReceive('getTransactionId')->andReturn('ORDER-001');

        $data = (object)['resposta' => 'OK', 'transactionID' => 'TXN-MBWAY'];
        $response = new MBWayResponse($mockReq, $data);

        $this->assertEquals('TXN-MBWAY', $response->getTransactionReference());
    }

    public function testPayShopResponseFallsBackToTransactionId()
    {
        $mockReq = Mockery::mock(\Omnipay\Common\Message\RequestInterface::class);
        $mockReq->shouldReceive('getTransactionId')->andReturn('ORDER-001');

        $data = (object)['resposta' => 'OK', 'transactionID' => 'TXN-PS'];
        $response = new PayShopResponse($mockReq, $data);

        $this->assertEquals('TXN-PS', $response->getTransactionReference());
    }

    public function testPagaquiResponseFallsBackToTransactionId()
    {
        $mockReq = Mockery::mock(\Omnipay\Common\Message\RequestInterface::class);
        $mockReq->shouldReceive('getTransactionId')->andReturn('ORDER-001');

        $data = (object)['resposta' => 'OK', 'transactionID' => 'TXN-PQ'];
        $response = new PagaquiResponse($mockReq, $data);

        $this->assertEquals('TXN-PQ', $response->getTransactionReference());
    }

    public function testReferenceStatusResponseFallsBackToNewFieldNames()
    {
        $mockReq = Mockery::mock(\Omnipay\Common\Message\RequestInterface::class);

        $response = new ReferenceStatusResponse($mockReq, (object)[
            'resposta' => 'OK',
            'status'   => 'paga',
        ]);
        $this->assertEquals('paga', $response->getStatus());
        $this->assertTrue($response->isPaid());

        $response2 = new ReferenceStatusResponse($mockReq, (object)[
            'resposta'       => 'OK',
            'status'         => 'pendente',
            'paymentDate'    => '2026-05-26',
        ]);
        $this->assertTrue($response2->isPending());
        $this->assertEquals('2026-05-26', $response2->getPaymentDate());

        $response3 = new ReferenceStatusResponse($mockReq, (object)[
            'resposta'     => 'OK',
            'status'       => 'paga',
            'payment_date' => '2026-05-25',
        ]);
        $this->assertEquals('2026-05-25', $response3->getPaymentDate());
    }

    // -------------------------------------------------------------------------
    // HTTP 201 normalization (v1.02 success without resposta field)
    // -------------------------------------------------------------------------

    public function testHttp201WithoutRespostaIsNormalisedToSuccessful()
    {
        [$req, $client] = $this->newRequest(MBWayRequest::class, array_merge($this->baseParams(), ['alias' => '912345678']));

        $client->shouldReceive('request')
            ->once()
            ->andReturn($this->psr7Response(201, ['transactionID' => 'TXN-NORM']));

        $response = $req->send();

        $this->assertTrue($response->isSuccessful());
    }

    public function testEmptyResponseBodyDoesNotCrash()
    {
        [$req, $client] = $this->newRequest(MBWayRequest::class, array_merge($this->baseParams(), ['alias' => '912345678']));

        $client->shouldReceive('request')
            ->once()
            ->andReturn(new Psr7Response(201, [], ''));

        $response = $req->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getTransactionReference());
    }

    // -------------------------------------------------------------------------
    // Validation failures stay local (no HTTP call made)
    // -------------------------------------------------------------------------

    public function testMBWayValidationFailsWithoutAlias()
    {
        [$req, $client] = $this->newRequest(MBWayRequest::class, $this->baseParams());

        $client->shouldNotReceive('request');

        $response = $req->send();

        $this->assertFalse($response->isSuccessful());
    }

    public function testRequestValidationFailsWithoutApiKey()
    {
        [$req, $client] = $this->newRequest(MultibancoRequest::class, [
            'transactionId' => 'ORDER-001',
            'amount'        => '10.00',
            'currency'      => 'EUR',
        ]);

        $client->shouldNotReceive('request');

        $response = $req->send();

        $this->assertFalse($response->isSuccessful());
    }
}
