<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Request;
use Exception;

/**
 * Eupago MBWay Request
 */
class MBWayRequest extends Request {

/**
 * Validation errors.
 *
 * @var array
 */
    protected $_errors;


/**
 * Set phone number associated with MBWay.
 *
 * @param int $value Phone number
 * @return \Omnipay\Eupago\Message\MBWayRequest
 */
    public function setAlias($value) {
        return $this->alias($value);
    }

/**
 * @inherited
 */
    public function alias($value) {
        $value = $this->_validateAlias($value);
        return $this->setParameter('alias', $value);
    }

/**
 * Check if data is valid.
 *
 * @return boolean True if valid, false otherwise
 */
    public function isValid() {
        parent::isValid();

        $alias = $this->getAlias();

        if (empty($alias)) {
            $this->_errors[] = 'Missing phone number associated with MBWay';
        }

        return empty($this->_errors);
    }

/**
 * Validate phone number.
 */
    protected function _validateAlias($value) {
        $value = (int)str_replace(' ', '', $value);

        if (empty($value)) {
            throw new Exception('Missing phone number for MBWay');
        }

        if (strlen($value) !== 9 || substr($value, 0, 1) !== '9') {
            throw new Exception('Invalid phone number');
        }

        return $value;
    }

/**
 * Make the request.
 *
 * @return string
 * @return string
 */
    protected function _makeRequest($data) {
        if (!$this->isValid()) {
            return $this->response = new MBWayResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        // Basic required data
        $data = array(
            'chave' => $this->apiKey(),
            'id' => $this->getTransactionId(),
            'valor' => $this->getAmount(),
            'alias' => $this->getAlias()
        );

        $result = $this->_soapCall($this->getUrl(), 'pedidoMBW', $data);

        return $this->response = new MBWayResponse($this, $result);
    }

}
