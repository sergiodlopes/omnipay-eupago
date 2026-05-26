<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Response;

/**
 * Manual Response
 */
class PagaquiResponse extends Response {

    public function getTransactionReference() {
        return $this->_getData('referencia') ?: $this->_getData('transactionID');
    }

    public function getTransactionId() {
        return $this->getRequest()->getTransactionId();
    }

}
