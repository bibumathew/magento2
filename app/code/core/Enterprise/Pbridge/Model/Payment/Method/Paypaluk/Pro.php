<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * PayPal UK Website Payments Pro implementation for payment method instaces
 * This model was created because right now PayPal Direct and PayPal Express payment methods cannot have same abstract
 */
class Enterprise_Pbridge_Model_Payment_Method_Paypaluk_Pro extends Mage_PaypalUk_Model_Pro
{

    /**
     * Payment Bridge Payment Method Instance
     *
     * @var Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    protected $_pbridgeMethodInstance = null;

    /**
     * Paypal pbridge payment method
     * @var Enterprise_Pbridge_Model_Payment_Method_Paypal
     */
    protected $_pbridgePaymentMethod = null;

    /**
     * Pbridge payment method setter
     *
     * @param Enterprise_Pbridge_Model_Payment_Method_Paypal $pbridgePaymentMethod
     */

    public function setPaymentMethod($pbridgePaymentMethod)
    {
        $this->_pbridgePaymentMethod = $pbridgePaymentMethod;
    }

    /**
     * Return Payment Bridge method instance
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    public function getPbridgeMethodInstance()
    {
        if ($this->_pbridgeMethodInstance === null) {
            $this->_pbridgeMethodInstance = Mage::helper('Mage_Payment_Helper_Data')->getMethodInstance('pbridge');
            $this->_pbridgeMethodInstance->setOriginalMethodInstance($this->_pbridgePaymentMethod);
        }
        return $this->_pbridgeMethodInstance;
    }

    /**
     * Attempt to capture payment
     * Will return false if the payment is not supposed to be captured
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return false|null
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $result = $this->getPbridgeMethodInstance()->capture($payment, $amount);
        if (false !== $result) {
            $result = new Varien_Object($result);
            $this->_importCaptureResultToPayment($result, $payment);
        }
        return $result;
    }


    /**
     * Refund a capture transaction
     *
     * @param Varien_Object $payment
     * @param float $amount
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $result = $this->getPbridgeMethodInstance()->refund($payment, $amount);

        if ($result) {
            $result = new Varien_Object($result);
            $canRefundMore = $payment->getOrder()->canCreditmemo();
            $this->_importRefundResultToPayment($result, $payment, $canRefundMore);
        }

        return $result;
    }

    /**
     * Refund a capture transaction
     *
     * @param Varien_Object $payment
     */
    public function void(Varien_Object $payment)
    {
        $result = $this->getPbridgeMethodInstance()->void($payment);
        Mage::getModel('Mage_Paypal_Model_Info')->importToPayment(new Varien_Object($result), $payment);
        return $result;
    }

    /**
     * Cancel payment
     *
     * @param Varien_Object $payment
     */
    public function cancel(Varien_Object $payment)
    {
        if (!$payment->getOrder()->getInvoiceCollection()->count()) {
            $result = $this->getPbridgeMethodInstance()->void($payment);
            Mage::getModel('Mage_Paypal_Model_Info')->importToPayment(new Varien_Object($result), $payment);
        }
    }


    /**
     * Parent transaction id getter
     *
     * @param Varien_Object $payment
     * @return string
     */
    protected function _getParentTransactionId(Varien_Object $payment)
    {
        return $payment->getParentTransactionId();
    }


    /**
     * Import capture results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     */
    protected function _importCaptureResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(false);
        $payment->setPreparedMessage(
            Mage::helper('Enterprise_Pbridge_Helper_Data')->__('Payflow PNREF: #%s.', $api->getData(self::TRANSPORT_PAYFLOW_TXN_ID))
        );
        Mage::getModel('Mage_Paypal_Model_Info')->importToPayment($api, $payment);
    }

    /**
     * Import refund results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp $api
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param bool $canRefundMore
     */
    protected function _importRefundResultToPayment($api, $payment, $canRefundMore)
    {
        $payment->setTransactionId($api->getTransactionId())
                ->setIsTransactionClosed(1) // refund initiated by merchant
                ->setShouldCloseParentTransaction(!$canRefundMore)
                ->setTransactionAdditionalInfo(self::TRANSPORT_PAYFLOW_TXN_ID, $api->getPayflowTrxid());

        $payment->setPreparedMessage(
            Mage::helper('Enterprise_Pbridge_Helper_Data')->__('Payflow PNREF: #%s.', $api->getData(self::TRANSPORT_PAYFLOW_TXN_ID))
        );
        Mage::getModel('Mage_Paypal_Model_Info')->importToPayment($api, $payment);
    }
}
