<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Request;
use Omnipay\Eupago\Message\ReferenceStatusResponse;
use SoapClient;
use SoapFault;
use Exception;

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
 * @return string
 * @return string
 */
    protected function _makeRequest($data) {
        if (!$this->isValid()) {
            return $this->response = new ReferenceStatusResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        // Basic required data
        $data = array(
            'chave' => $this->getApiKey(),
            'referencia' => $this->getTransactionReference()
        );

        $result = $this->_soapCall($this->getUrl(), 'informacaoReferencia', $data);

        return $this->response = new ReferenceStatusResponse($this, $result);
    }

}
