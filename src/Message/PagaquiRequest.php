<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Request;

/**
 * Eupago Pagaqui Request
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
 * @param array $data Request data
 * @return \Omnipay\Eupago\Message\PagaquiResponse
 */
    protected function _makeRequest($data) {
        if (!$this->isValid()) {
            return $this->response = new PagaquiResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        // v1.02 API uses a 'payment' object with ApiKey header auth
        $data = [
            'payment' => [
                'amount'     => $this->getAmount(),
                'identifier' => $this->getTransactionId()
            ]
        ];

        $result = $this->_apiKeyRestCall($this->getApiKeyUrl() . '/pagaqui/create', $data);

        return $this->response = new PagaquiResponse($this, $result);
    }

}
