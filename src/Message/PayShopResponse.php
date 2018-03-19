<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Response;

/**
 * Manual Response
 */
class PayShopResponse extends Response {

    public function getTransactionReference() {
        return $this->_getData('referencia');
    }

    public function getTransactionId() {
        return $this->getRequest()->getTransactionId();
    }

}
