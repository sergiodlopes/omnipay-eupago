<?php

namespace Omnipay\Eupago\Tests;

use Mockery;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Eupago\MultibancoGateway;
use Omnipay\Eupago\MBWayGateway;
use Omnipay\Eupago\PayShopGateway;
use Omnipay\Eupago\PagaquiGateway;
use Omnipay\Eupago\Message\MultibancoRequest;
use Omnipay\Eupago\Message\MBWayRequest;
use Omnipay\Eupago\Message\PayShopRequest;
use Omnipay\Eupago\Message\PagaquiRequest;
use Omnipay\Eupago\Message\ReferenceStatusRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class GatewayTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function httpClient(): ClientInterface
    {
        return Mockery::mock(ClientInterface::class);
    }

    private function httpRequest(): HttpRequest
    {
        return HttpRequest::create('/');
    }

    public function testMultibancoName()
    {
        $gw = new MultibancoGateway($this->httpClient(), $this->httpRequest());
        $this->assertSame('Multibanco', $gw->getName());
    }

    public function testMBWayName()
    {
        $gw = new MBWayGateway($this->httpClient(), $this->httpRequest());
        $this->assertSame('MBWay', $gw->getName());
    }

    public function testPayShopName()
    {
        $gw = new PayShopGateway($this->httpClient(), $this->httpRequest());
        $this->assertSame('PayShop', $gw->getName());
    }

    public function testPagaquiName()
    {
        $gw = new PagaquiGateway($this->httpClient(), $this->httpRequest());
        $this->assertSame('Pagaqui', $gw->getName());
    }

    public function testMultibancoPurchaseReturnsCorrectRequest()
    {
        $gw = new MultibancoGateway($this->httpClient(), $this->httpRequest());
        $request = $gw->purchase(['apiKey' => 'demo-key', 'amount' => '10.00', 'currency' => 'EUR', 'transactionId' => 'TXN001']);
        $this->assertInstanceOf(MultibancoRequest::class, $request);
    }

    public function testMBWayPurchaseReturnsCorrectRequest()
    {
        $gw = new MBWayGateway($this->httpClient(), $this->httpRequest());
        $request = $gw->purchase(['apiKey' => 'demo-key', 'amount' => '10.00', 'currency' => 'EUR', 'transactionId' => 'TXN001']);
        $this->assertInstanceOf(MBWayRequest::class, $request);
    }

    public function testPayShopPurchaseReturnsCorrectRequest()
    {
        $gw = new PayShopGateway($this->httpClient(), $this->httpRequest());
        $request = $gw->purchase(['apiKey' => 'demo-key', 'amount' => '10.00', 'currency' => 'EUR', 'transactionId' => 'TXN001']);
        $this->assertInstanceOf(PayShopRequest::class, $request);
    }

    public function testPagaquiPurchaseReturnsCorrectRequest()
    {
        $gw = new PagaquiGateway($this->httpClient(), $this->httpRequest());
        $request = $gw->purchase(['apiKey' => 'demo-key', 'amount' => '10.00', 'currency' => 'EUR', 'transactionId' => 'TXN001']);
        $this->assertInstanceOf(PagaquiRequest::class, $request);
    }

    public function testCheckStatusReturnsReferenceStatusRequest()
    {
        $gateways = [
            new MultibancoGateway($this->httpClient(), $this->httpRequest()),
            new MBWayGateway($this->httpClient(), $this->httpRequest()),
            new PayShopGateway($this->httpClient(), $this->httpRequest()),
            new PagaquiGateway($this->httpClient(), $this->httpRequest()),
        ];
        foreach ($gateways as $gw) {
            $request = $gw->checkStatus(['apiKey' => 'demo-key', 'transactionReference' => 'REF001']);
            $this->assertInstanceOf(ReferenceStatusRequest::class, $request);
        }
    }

    public function testAcceptNotificationDoesNotExist()
    {
        $gateways = [
            new MultibancoGateway($this->httpClient(), $this->httpRequest()),
            new MBWayGateway($this->httpClient(), $this->httpRequest()),
            new PayShopGateway($this->httpClient(), $this->httpRequest()),
            new PagaquiGateway($this->httpClient(), $this->httpRequest()),
        ];
        foreach ($gateways as $gw) {
            $this->assertFalse(method_exists($gw, 'acceptNotification'));
        }
    }
}
