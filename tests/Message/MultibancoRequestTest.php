<?php

namespace Omnipay\Eupago\Tests\Message;

use Mockery;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Eupago\Message\MultibancoRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class MultibancoRequestTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function makeRequest(array $params = []): MultibancoRequest
    {
        $request = new MultibancoRequest(
            Mockery::mock(ClientInterface::class),
            HttpRequest::create('/')
        );
        $request->initialize(array_merge([
            'apiKey'        => 'demo-abc123',
            'amount'        => '10.00',
            'currency'      => 'EUR',
            'transactionId' => 'TXN001',
        ], $params));
        return $request;
    }

    public function testGetAmountReturnsFormattedDecimal()
    {
        $request = $this->makeRequest(['amount' => '10']);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testIsValidWithValidParams()
    {
        $request = $this->makeRequest();
        $this->assertTrue($request->isValid());
    }

    public function testIsValidFailsWithMissingApiKey()
    {
        $request = $this->makeRequest(['apiKey' => '']);
        $this->assertFalse($request->isValid());
        $this->assertContains('euPago API key missing', $request->getErrors());
    }

    public function testIsValidFailsWithZeroAmount()
    {
        $request = $this->makeRequest(['amount' => '0.00']);
        $this->assertFalse($request->isValid());
    }

    public function testIsValidFailsWithWrongCurrency()
    {
        $request = $this->makeRequest(['currency' => 'USD']);
        $this->assertFalse($request->isValid());
    }

    public function testIsValidFailsWithMissingTransactionId()
    {
        $request = $this->makeRequest(['transactionId' => '']);
        $this->assertFalse($request->isValid());
    }

    public function testIsValidFailsWhenEndDateBeforeStartDate()
    {
        $request = $this->makeRequest([
            'startDate' => '2025-12-31',
            'endDate'   => '2025-01-01',
        ]);
        $this->assertFalse($request->isValid());
    }

}
