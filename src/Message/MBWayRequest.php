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
 * Get phone number associated with MBWay.
 *
 * @return string MBWay phone number
 */
    public function getAlias() {
        return $this->getParameter('alias');
    }

/**
 * @inherited
 */
    public function alias($value = null) {
        $value = $this->_validateAlias($value);
        return $this->setParameter('alias', $value);
    }

/**
 * Get MBWay transaction description.
 *
 * @return string MBWay transaction description
 */
    public function getDescription() {
        return $this->getParameter('campos_extra');
    }

/**
 * Get MBWay transaction description.
 *
 * @param string $value MBWay transaction description
 * @return \Omnipay\Eupago\Message\MBWayRequest
 */
    public function setDescription($value) {
        return $this->setParameter('campos_extra', $value);
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
 * @param array $data Request data
 * @return \Omnipay\Eupago\Message\MBWayResponse
 */
    protected function _makeRequest($data) {
        if (!$this->isValid()) {
            return $this->response = new MBWayResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        // Data
        $data = [
            'chave' => $this->apiKey(),
            'id' => $this->getTransactionId(),
            'valor' => $this->getAmount(),
            'alias' => $this->getAlias(),
            'campos_extra' => $this->getDescription()
        ];

        $result = $this->_soapCall($this->getUrl(), 'pedidoMBW', $data);

        return $this->response = new MBWayResponse($this, $result);
    }

}
