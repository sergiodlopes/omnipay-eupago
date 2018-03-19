<?php

namespace Omnipay\Eupago;

use Omnipay\Common\AbstractGateway;
use Omnipay\Eupago\ParametersTrait;
use DateTime;

/**
 * Eupago Pagaqui.
 */
class PagaquiGateway extends AbstractGateway {
    
    use ParametersTrait;

/**
 * Get gateway name.
 *
 * @return string Gateway name
 */
    public function getName() {
        return 'Eupago Pagaqui';
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
        return $this->createRequest('\Omnipay\Eupago\Message\PagaquiRequest', $parameters);
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
