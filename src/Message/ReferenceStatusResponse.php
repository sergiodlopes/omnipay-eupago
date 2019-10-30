<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Response;

/**
 * Eupago checkStatusResponse
 */
class ReferenceStatusResponse extends Response {

/**
 * Check if transaction creation was successful.
 *
 * @return bool
 */
    public function isSuccessful() {
        $status = $this->getStatus();
        return $status === 'paga' || $status === 'transferida';
    }

    public function getStatus() {
        return $this->_getData('estado_referencia');
    }

    public function isPending() {
        $status = $this->getStatus();
        return $status === 'pendente';
    }

    public function isPaid() {
        return $this->isSuccessful();
    }

    public function getPaymentDate() {
        return $this->_getData('data_pagamento');
    }

}