<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Request;
use Exception;

/**
 * Eupago Request
 */
class PagaquiRequest extends Request {

/**
 * Validation errors.
 *
 * @var array
 */
    protected $_errors;


/**
 * Make the request.
 *
 * @return string
 * @return string
 */
    protected function _makeRequest($data) {
        if (!$this->isValid()) {
            return $this->response = new PagaquiResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        // Basic required data
        $data = array(
            'chave' => $this->apiKey(),
            'id' => $this->getTransactionId(),
            'valor' => $this->getAmount()
        );

        $result = $this->_soapCall($this->getUrl(), 'gerarReferenciaPQ', $data);

        return $this->response = new PagaquiResponse($this, $result);
    }

}
