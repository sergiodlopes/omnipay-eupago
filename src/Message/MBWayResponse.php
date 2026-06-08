<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Response;

/**
 * Manual Response
 */
class MBWayResponse extends Response {

    public function getTransactionReference() {
        // v1.02 API returns 'reference' (numeric, used for status checks) and 'transactionID'.
        return $this->_getData('reference') ?: $this->_getData('transactionID');
    }

    public function getTransactionId() {
        return $this->getRequest()->getTransactionId();
    }

}
