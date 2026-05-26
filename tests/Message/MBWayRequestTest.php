<?php

namespace Omnipay\Eupago\Tests\Message;

use Mockery;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Eupago\Message\MBWayRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class MBWayRequestTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function makeRequest(array $params = []): MBWayRequest
    {
        $request = new MBWayRequest(
            Mockery::mock(ClientInterface::class),
            HttpRequest::create('/')
        );
        $request->initialize(array_merge([
            'apiKey'        => 'demo-abc123',
            'amount'        => '5.00',
            'currency'      => 'EUR',
            'transactionId' => 'TXN002',
            'alias'         => '912345678',
        ], $params));
        return $request;
    }

    public function testIsValidWithValidParams()
    {
        $request = $this->makeRequest();
        $this->assertTrue($request->isValid());
    }

    public function testIsValidFailsWithMissingAlias()
    {
        $request = new MBWayRequest(
            Mockery::mock(ClientInterface::class),
            HttpRequest::create('/')
        );
        $request->initialize([
            'apiKey'        => 'demo-abc123',
            'amount'        => '5.00',
            'currency'      => 'EUR',
            'transactionId' => 'TXN002',
        ]);
        $this->assertFalse($request->isValid());
    }

    public function testAliasValidationRejectsNonNineDigitNumber()
    {
        $this->expectException(\Exception::class);
        $request = $this->makeRequest();
        $request->alias('12345');
    }

    public function testAliasValidationRejectsNumberNotStartingWithNine()
    {
        $this->expectException(\Exception::class);
        $request = $this->makeRequest();
        $request->alias('812345678');
    }

    public function testAliasValidationAcceptsValidNumber()
    {
        $request = $this->makeRequest();
        $request->alias('961234567');
        $this->assertSame(961234567, $request->getAlias());
    }
}
