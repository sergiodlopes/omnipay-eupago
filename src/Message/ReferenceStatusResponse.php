<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Response;

/**
 * Eupago checkStatusResponse
 */
class ReferenceStatusResponse extends Response {

    public function getStatus() {
        return $this->_getData('estado_referencia');
    }

    public function isPaid() {
        $status = $this->getStatus();
        return $status === 'paga' || $status === 'transferida';
    }

    public function getPaymentDate() {
        return $this->_getData('data_pagamento');
    }

}