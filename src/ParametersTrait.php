<?php

namespace Omnipay\Eupago;

use DateTime;

/**
 * Eupago parameters getter/setter.
 **/
trait ParametersTrait {

/**
 * Parameters mapping.
 *
 * @var array
 */
    protected $_paramMap = [];

/**
 * Get/set parameters.
 *
 * # Usage
 *  $gateway->apiKey('api-key')->fromDate(new DateTime)->untilDate()
 *
 *
 * @return \Omnipay\Eupago\MultibancoGateway|string
 */
    public function __call($name, array $args) {
        $paramName = $this->_getParam($name);

        if (empty($args)) {
            return $this->parameters->get($paramName);
        }

        $arg = array_shift($args);

        if ($arg instanceof DateTime) {
            $arg = $arg->format('Y-m-d');
        }

        return $this->setParameter($paramName, $arg);
    }

/**
 * Get/set parameters.
 *
 * # Usage
 *  $gateway->apiKey('api-key')->fromDate(new DateTime)->untilDate()
 *
 *
 * @return string|null Parameter name if valid
 */
    protected function _getParam($name) {
        if (substr($name, 0, 3) === 'set') {
            $name = ltrim($name, 'set');
        } elseif (substr($name, 0, 3) === 'get') {
            $name = ltrim($name, 'get');
        }
        return lcfirst($name);
    }

}
