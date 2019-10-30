<?php

namespace Omnipay\Eupago\Message;

use Omnipay\Eupago\Message\Request;
use Exception;
use DateTime;

/**
 * Eupago Request
 */
class MultibancoRequest extends Request {

/**
 * Validation errors.
 *
 * @var array
 */
    protected $_errors;


/**
 * Set Start Date.
 *
 * @param \DateTime|string $value Date time
 * @return \Omnipay\Eupago\Message\MultibancoDLRequest
 */
    public function setStartDate($value) {
        return $this->startDate($value);
    }

/**
 * Set End Date.
 *
 * @param \DateTime|string $value Date time
 * @return \Omnipay\Eupago\Message\MultibancoDLRequest
 */
    public function setEndDate($value) {
        return $this->endDate($value);
    }

/**
 * Set allow multiple transactions from per reference.
 *
 * @param boolean $value Date time
 * @return \Omnipay\Eupago\Message\MultibancoDLRequest
 */
    public function setAllowDuplicates($value) {
        return $this->allowDuplicates($value);
    }

/**
 * Set minimum amount.
 *
 * @param float $value Minimum allowed amount
 * @return \Omnipay\Eupago\Message\MultibancoDLRequest
 */
    public function setMinAmount($value) {
        return $this->minAmount($value);
    }

/**
 * Set allow multiple transactions from per reference.
 *
 * @param boolean $value Date time
 * @return \Omnipay\Eupago\Message\MultibancoDLRequest
 */
    public function setMaxAmount($value) {
        return $this->maxAmount($value);
    }

/**
 * Check if data is valid.
 *
 * @return boolean True if valid, false otherwise
 */
    public function isValid() {
        parent::isValid();

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if ($startDate && $endDate) {
            if (strtotime($startDate) > strtotime($endDate)) {
                $this->_errors[] = 'Invalid end date. End date must be greater than the start date';
            }
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
            return $this->response = new MultibancoResponse($this, 'Errors: ' . implode("\n\r", $this->_errors));
        }

        // Basic required data
        $data = array(
            'chave' => $this->apiKey(),
            'id' => $this->getTransactionId(),
            'valor' => $this->getAmount()
        );

        $this->_sendDateLimitParameters($data);
        $this->_sendAmountLimitParameters($data);
        $this->_sendAllowDuplicatesParameter($data);

        $result = $this->_soapCall($this->getUrl(), $this->_getAction(), $data);

        return $this->response = new MultibancoResponse($this, $result);
    }

/**
 * Check if any date is set if so, return the corresponding action.
 *
 * @return string Action
 */
    protected function _getAction() {
        return $this->_hasDateLimit() || $this->_hasAmountLimit() ? 'gerarReferenciaMBDL' : 'gerarReferenciaMB';
    }

/**
 * Set start/end date, as well as others in data array.
 *
 * @param array $data Request data
 */
    protected function _sendAllowDuplicatesParameter(&$data) {
        if ($this->getAllowDuplicates() !== null && ($this->_hasDateLimit() || $this->_hasAmountLimit())) {
            $data['per_dup'] = (int)$this->getAllowDuplicates();
        }
    }

/**
 * Check if any date is set.
 *
 * @return boolean True if any date(s) is set
 */
    protected function _hasDateLimit() {
        return $this->getStartDate() || $this->getEndDate();
    }

/**
 * Check if any date is set.
 *
 * @return boolean True if any date(s) is set
 */
    protected function _hasAmountLimit() {
        return $this->getMinAmount() || $this->getMaxAmount();
    }

/**
 * Set start/end date, as well as others in data array.
 *
 * @param array $data Request data
 */
    protected function _sendDateLimitParameters(&$data) {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if ($endDate) {
            $data['data_fim'] = $endDate;

            if (!$startDate) {
                $startDate = $this->setStartDate(new \DateTime)->getStartDate();
            }
        }

        if ($startDate) {
            $data['data_inicio'] = $startDate;
        }

    }

/**
 * Set start/end date, as well as others in data array.
 *
 * @param array $data Request data
 */
    protected function _sendAmountLimitParameters(&$data) {
        if ($minAmount = $this->getMinAmount()) {
            $data['valor_minimo'] = $minAmount;
        }

        if ($maxAmount = $this->getMaxAmount()) {
            $data['valor_maximo'] = $maxAmount;
        }
    }

}
