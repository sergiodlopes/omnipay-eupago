<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Request;
use Omnipay\Eupago\Message\ReferenceStatusResponse;

/**
 * Eupago ReferenceStatusRequest
 */
class ReferenceStatusRequest extends Request {

    public function getTransactionReference() {
        return $this->getParameter('transactionReference');
    }

/**
 * Check if data is valid.
 *
 * @return boolean True if valid, false otherwise
 */
    public function isValid() {
        $transactionId = $this->getTransactionReference();

        if (empty($transactionId)) {
            $this->_errors[] = 'Missing Transaction Reference';
        }

        return empty($this->_errors);
    }

/**
 * Make the request.
 *
 * @param array $data Request data
 * @return \Omnipay\Eupago\Message\ReferenceStatusResponse
 */
    protected function _makeRequest($data) {
        if (!$this->isValid()) {
            return $this->response = new ReferenceStatusResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        $data = array(
            'chave'      => $this->getApiKey(),
            'referencia' => $this->getTransactionReference()
        );

        $result = $this->_restCall($this->getUrl() . '/multibanco/info', $data);

        return $this->response = new ReferenceStatusResponse($this, $result);
    }

}
