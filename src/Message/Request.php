<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Eupago\ParametersTrait;
use Exception;

/**
 * Common Request.
 */
class Request extends AbstractRequest {

    use ParametersTrait;

/**
 * Legacy REST endpoints (body auth) — Multibanco, PayShop, Reference Info.
 *
 * @var array
 */
    protected $_restEndpoints = array(
        'test' => 'https://sandbox.eupago.pt/clientes/rest_api',
        'live' => 'https://clientes.eupago.pt/clientes/rest_api'
    );

/**
 * REST API v1.02 endpoints (ApiKey header auth) — MBWay, Pagaqui.
 *
 * @var array
 */
    protected $_apiKeyEndpoints = array(
        'test' => 'https://sandbox.eupago.pt/api/v1.02',
        'live' => 'https://clientes.eupago.pt/api/v1.02'
    );

/**
 * Validation errors.
 *
 * @var array
 */
    protected $_errors;


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
 * Get amount.
 *
 * @return string
 */
    public function getAmount() {
        return $this->getParameter('amount');
    }

/**
 * Get transaction ID.
 *
 * @return string
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
 * Returns true if the API key indicates a test/sandbox environment.
 *
 * @return bool
 */
    protected function isTest() {
        return explode('-', $this->getApiKey())[0] === 'demo';
    }

/**
 * Get legacy REST base URL (body auth — Multibanco, PayShop, Reference Info).
 *
 * @return string
 */
    public function getUrl() {
        return $this->isTest() ? $this->_restEndpoints['test'] : $this->_restEndpoints['live'];
    }

/**
 * Get v1.02 REST base URL (ApiKey header auth — MBWay, Pagaqui).
 *
 * @return string
 */
    public function getApiKeyUrl() {
        return $this->isTest() ? $this->_apiKeyEndpoints['test'] : $this->_apiKeyEndpoints['live'];
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
        return $this->_makeRequest($data);
    }

/**
 * POST JSON to a legacy body-auth endpoint.
 * Used by Multibanco, PayShop, Reference Info.
 *
 * @param string $url  Full endpoint URL.
 * @param array  $data Request payload (API key included as 'chave').
 * @return \stdClass Decoded JSON response.
 */
    protected function _restCall($url, $data) {
        try {
            $response = $this->httpClient->request(
                'POST',
                $url,
                ['Content-Type' => 'application/json'],
                json_encode($data)
            );

            return json_decode((string) $response->getBody()) ?: new \stdClass();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

/**
 * POST JSON to a v1.02 ApiKey header-auth endpoint.
 * Used by MBWay, Pagaqui.
 *
 * The v1.02 API returns HTTP 201 on success with a different JSON structure.
 * This method normalises the response so existing Response classes work
 * unchanged: it injects resposta=OK when the HTTP status indicates success.
 *
 * @param string $url  Full endpoint URL.
 * @param array  $data Request payload (API key NOT in body — sent as header).
 * @return \stdClass Decoded JSON response.
 */
    protected function _apiKeyRestCall($url, $data) {
        try {
            $response = $this->httpClient->request(
                'POST',
                $url,
                [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'ApiKey ' . $this->getApiKey()
                ],
                json_encode($data)
            );

            $result = json_decode((string) $response->getBody()) ?: new \stdClass();

            if (in_array($response->getStatusCode(), [200, 201]) && !isset($result->resposta)) {
                $result->resposta = 'OK';
            }

            return $result;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

/**
 * Get validation errors.
 *
 * @return array
 */
    public function getErrors() {
        return $this->_errors;
    }

}
