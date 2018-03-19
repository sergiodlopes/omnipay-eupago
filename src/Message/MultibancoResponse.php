<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Response;

/**
 * Manual Response
 */
class MultibancoResponse extends Response {

    public function getTransactionReference() {
        return $this->_getData('referencia');
    }

    public function getTransactionId() {
        return $this->getRequest()->getTransactionId();
    }

    public function getEntity() {
        return $this->_getData('entidade');
    }

    public function getReference() {
        return $this->getTransactionReference();
    }

    public function getValue() {
        return $this->_getData('valor');
    }

    public function getStatus() {
        return $this->_getData('estado');
    }

}
