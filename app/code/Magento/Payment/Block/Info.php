<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Payment
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Base payment iformation block
 *
 */
namespace Magento\Payment\Block;

class Info extends \Magento\Core\Block\Template
{
    /**
     * Payment rendered specific information
     *
     * @var \Magento\Object
     */
    protected $_paymentSpecificInformation = null;

    protected $_template = 'Magento_Payment::info/default.phtml';

    /**
     * Retrieve info model
     *
     * @return \Magento\Payment\Model\Info
     */
    public function getInfo()
    {
        $info = $this->getData('info');
        if (!($info instanceof \Magento\Payment\Model\Info)) {
            \Mage::throwException(__('We cannot retrieve the payment info model object.'));
        }
        return $info;
    }

    /**
     * Retrieve payment method model
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    public function getMethod()
    {
        return $this->getInfo()->getMethodInstance();
    }

    /**
     * Render as PDF
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Magento_Payment::info/pdf/default.phtml');
        return $this->toHtml();
    }

    /**
     * Getter for children PDF, as array. Analogue of $this->getChildHtml()
     *
     * Children must have toPdf() callable
     * Known issue: not sorted
     * @return array
     */
    public function getChildPdfAsArray()
    {
        $result = array();
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
            if (method_exists($child, 'toPdf')) {
                $result[] = $child->toPdf();
            }
        }
        return $result;
    }

    /**
     * Get some specific information in format of array($label => $value)
     *
     * @return array
     */
    public function getSpecificInformation()
    {
        return $this->_prepareSpecificInformation()->getData();
    }

    /**
     * Render the value as an array
     *
     * @param mixed $value
     * @param bool $escapeHtml
     * @return $array
     */
    public function getValueAsArray($value, $escapeHtml = false)
    {
        if (empty($value)) {
            return array();
        }
        if (!is_array($value)) {
            $value = array($value);
        }
        if ($escapeHtml) {
            foreach ($value as $_key => $_val) {
                $value[$_key] = $this->escapeHtml($_val);
            }
        }
        return $value;
    }

    /**
     * Check whether payment information should show up in secure mode
     * true => only "public" payment information may be shown
     * false => full information may be shown
     *
     * @return bool
     */
    public function getIsSecureMode()
    {
        if ($this->hasIsSecureMode()) {
            return (bool)(int)$this->_getData('is_secure_mode');
        }
        if (!$payment = $this->getInfo()) {
            return true;
        }
        if (!$method = $payment->getMethodInstance()) {
            return true;
        }
        return !\Mage::app()->getStore($method->getStore())->isAdmin();
    }

    /**
     * Prepare information specific to current payment method
     *
     * @param \Magento\Object|array $transport
     * @return \Magento\Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null === $this->_paymentSpecificInformation) {
            if (null === $transport) {
                $transport = new \Magento\Object;
            } elseif (is_array($transport)) {
                $transport = new \Magento\Object($transport);
            }
            $this->_eventManager->dispatch('payment_info_block_prepare_specific_information', array(
                'transport' => $transport,
                'payment'   => $this->getInfo(),
                'block'     => $this,
            ));
            $this->_paymentSpecificInformation = $transport;
        }
        return $this->_paymentSpecificInformation;
    }
}
