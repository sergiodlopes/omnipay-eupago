<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Common Response.
 */
class Response extends AbstractResponse {

/**
 * Check if transaction creation was successful.
 *
 * @return bool
 */
    public function isSuccessful() {
        return $this->_getData('resposta') === 'OK';
    }

/**
 * Get message.
 *
 * @return string
 */
    public function getMessage() {
        return $this->_getData('resposta');
    }

/**
 * Helper method to extract properties from response data.
 *
 * @param string $property Property name to return value
 * @return mixed
 */
    protected function _getData($property = null) {
        $data = $this->getData();

        if ($property === null) {
            return $data;
        }

        return isset($data->{$property}) ? $data->{$property} : null;
    }

/**
 * Helper method to extract properties from response data.
 *
 * @param string $property Property name to return value
 * @return mixed
 */
    public function __get($property) {
        return $this->_getData($property);
    }

}
