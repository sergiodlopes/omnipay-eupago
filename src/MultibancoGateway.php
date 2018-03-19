<?php

namespace Omnipay\Eupago;

use Omnipay\Common\AbstractGateway;
use Omnipay\Eupago\ParametersTrait;
use DateTime;

/**
 * Payments with portuguese Eupago.pt by Multibanco or Multibanco DL.
 *
 * To use MultibancoDL, just pass the parameters 'startDate' and/or 'endDate',
 * and it will use action "gerarReferenciaMBDL", otherwise,
 * fallsback to default "gerarReferenciaMB".
 * 
 */
class MultibancoGateway extends AbstractGateway {

    use ParametersTrait;

/**
 * Get friendly name.
 *
 * @return string
 */
    public function getName() {
        return 'Eupago Multibanco';
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

/**
 * Initialize a transation by making a request.
 *
 * @param array $parameters Transaction parameters
 * @return \Omnipay\Eupago\Message\MultibancoRequest
 */
    public function purchase(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Eupago\Message\MultibancoRequest', $parameters);
    }

/**
 * Check status information.
 *
 * @param array $parameters Transaction parameters
 * @return \Omnipay\Eupago\Message\Request
 */
    public function checkStatus(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Eupago\Message\ReferenceStatusRequest', $parameters);
    }

    public function acceptNotification(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Common\Message\NotificationInterface', $parameters);
    }

}
