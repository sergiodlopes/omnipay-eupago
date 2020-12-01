<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Eupago\ParametersTrait;
use Exception;
use SoapClient;
use SoapFault;

/**
 * Common Request.
 */
class Request extends AbstractRequest {

    use ParametersTrait;

/**
 * SOAP endpoints.
 *
 * @var array
 */
    protected $_soapEndpoints = array(
        'test' => 'https://sandbox.eupago.pt/replica.eupagov8.wsdl',
        'live' => 'https://seguro.eupago.pt/eupagov8.wsdl'
    );

/**
 * REST endpoints.
 *
 * @var array
 */
    protected $_restEndpoints = array(
        'test' => 'https://sandbox.eupago.pt/clientes/rest_api',
        'live' => 'https://seguro.eupago.pt/clientes/rest_api'
    );


/**
 * Set API key.
 *
 * @param string $apiKey API Key
 * @return \Omnipay\Eupago\Message\Request
 */
    public function setApiKey($apiKey) {
        return $this->setParameter('apiKey', $apiKey);
    }

/**
 * Set API key.
 *
 * @param string $apiKey API Key
 * @return \Omnipay\Eupago\Message\Request
 */
    public function getAmount() {
        return $this->getParameter('amount');
    }

/**
 * Set API key.
 *
 * @param string $apiKey API Key
 * @return \Omnipay\Eupago\Message\Request
 */
    public function getTransactionId() {
        return $this->getParameter('transactionId');
    }

/**
 * Get data/parameters.
 *
 * @return array
 */
    public function getData() {
        return $this->getParameters();
    }

/**
 * Get URL.
 * Checks API key to return endpoint (test or live).
 *
 * @return string Endpoint URL
 */
    public function getUrl() {
        $parts = explode('-', $this->getApiKey());

        $endpoints = $this->_soapEndpoints;

        // @todo Support for REST API calls
        $apiType = $this->getParameter('apiType');
        if ($apiType && strtoupper($apiType) === 'REST') {
            $endpoints = $this->_restEndpoints;
        }

        return $parts[0] === 'demo' ? $endpoints['test'] : $endpoints['live'];
    }

/**
 * Check if data is valid.
 *
 * @return boolean True if valid, false otherwise
 */
    public function isValid() {
        $apiKey = $this->getApiKey();
        $amount = (float)$this->getAmount();
        $currency = $this->getCurrency();
        $transactionId = $this->getTransactionId();

        if (empty($apiKey)) {
            $this->_errors[] = 'euPago API key missing';
        }

        if ($amount <= 0 || empty($amount)) {
            $this->_errors[] = 'Amount must be greater then 0';
        }

        if (empty($currency)) {
            $this->_errors[] = 'Empty currency. euPago currently only accepts "EUR" or "€"';
        } elseif ($currency != '€' && $currency != 'EUR') {
            $this->_errors[] = 'euPago currently only accepts currency as "EUR" or "€"';
        }

        if (empty($transactionId)) {
            $this->_errors[] = 'Missing transaction ID';
        }

        return empty($this->_errors);
    }

/**
 * Make the request.
 *
 * @param array $data Data to be sent.
 * @return \Omnipay\Eupago\Message\Response
 */
    public function sendData($data) {
        // Make request
        return $this->_makeRequest($data);
    }

/**
 * Make SOAP request.
 *
 * @param string $url Data to be sent.
 * @param string $action Action to be run.
 * @param array $data Data to be sent.
 * @return \Omnipay\Eupago\Message\Response
 */
    protected function _soapCall($url, $action, $data) {

        // SOAP 1.2 client
        $params = [
            'encoding' => 'UTF-8',
            'cache_wsdl' => WSDL_CACHE_NONE,
            'soap_version' => SOAP_1_2,
            'keep_alive' => false,
            'connection_timeout' => 180,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ])
        ];

        try {
            $client = new SoapClient($url, $params);
            $result = $client->{$action}($data);
        } catch (SoapFault $sf) {
            throw new Exception($sf->getMessage(), $sf->getCode());
        }

        return $result;
    }

/**
 * @todo REST API implementation
 *
 * Make REST request.
 *
 * @param string $url Data to be sent.
 * @param string $action Action to be run.
 * @param array $data Data to be sent.
 * @return \Omnipay\Eupago\Message\Response
 */
    protected function _restCall($url, $action, $data) {}

/**
 * Get validation errors in case.
 *
 * @return boolean True if valid, false otherwise
 */
    public function getErrors() {
        return $this->_errors;
    }

}
