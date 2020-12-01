<?php

namespace Omnipay\Eupago;

use Omnipay\Common\AbstractGateway;
use Omnipay\Eupago\ParametersTrait;

/**
 * Eupago MBWay.
 */
class MBWayGateway extends AbstractGateway {

    use ParametersTrait;

/**
 * Get gateway display name
 *
 * This can be used by carts to get the display name for each gateway.
 */
    public function getName() {
        return 'MBWay';
    }

/**
 * Get gateway short name
 *
 * This name can be used with GatewayFactory as an alias of the gateway class,
 * to create new instances of this gateway.
 */
    public function getShortName() {
        return 'Eupago_MBWay';
    }

/**
 * Get default parameters.
 *
 * # Options
 *
 * 'apiKey' - API key provided by eupago.pt
 *
 * @return array
 */
    public function getDefaultParameters() {
        return array(
            'apiKey' => ''
        );
    }

    public function purchase(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Eupago\Message\MBWayRequest', $parameters);
    }

    public function checkStatus(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Eupago\Message\ReferenceStatusRequest', $parameters);
    }

    /**
    public function completePurchase(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Eupago\Message\CompletePurchaseRequest', $parameters);
    }
    */

    public function acceptNotification(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Common\Message\NotificationInterface', $parameters);
    }

}
