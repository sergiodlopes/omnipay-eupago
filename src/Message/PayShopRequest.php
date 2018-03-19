<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Request;
use Exception;
use DateTime;

/**
 * Eupago Request
 */
class PayShopRequest extends Request {

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
            return $this->response = new PayShopResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        // Basic required data
        $data = array(
            'chave' => $this->apiKey(),
            'id' => $this->getTransactionId(),
            'valor' => $this->getAmount()
        );

        $result = $this->_soapCall($this->getUrl(), 'gerarReferenciaPS', $data);

        return $this->response = new PayShopResponse($this, $result);
    }

}
