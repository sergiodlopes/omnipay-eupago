<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Response;

/**
 * Manual Response
 */
class MultibancoResponse extends Response {

    public function getTransactionReference() {
        return $this->_getData('referencia') ?: $this->_getData('transactionID');
    }

    public function getTransactionId() {
        return $this->getRequest()->getTransactionId();
    }

    public function getEntity() {
        return $this->_getData('entidade') ?: $this->_getData('entity');
    }

    public function getReference() {
        return $this->getTransactionReference();
    }

    public function getValue() {
        return $this->_getData('valor') ?: $this->_getData('amount');
    }

    public function getStatus() {
        return $this->_getData('estado') ?: $this->_getData('status');
    }

}
