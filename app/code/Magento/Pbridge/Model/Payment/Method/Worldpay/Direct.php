<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Pbridge
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Worldpay dummy payment method model
 *
 * @category    Magento
 * @package     Magento_Pbridge
 * @author      Magento
 */
class Magento_Pbridge_Model_Payment_Method_Worldpay_Direct extends Magento_Payment_Model_Method_Cc
{
    /**
     * Payment method code
     * @var string
     */
    protected $_code  = 'worldpay_direct';
    protected $_allowCurrencyCode = array('USD');

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;

    /**
     * Form block type for the frontend
     *
     * @var string
     */
    protected $_formBlockType = 'Magento_Pbridge_Block_Checkout_Payment_Worldpay_Direct';

    /**
     * Form block type for the backend
     *
     * @var string
     */
    protected $_backendFormBlockType = 'Magento_Pbridge_Block_Adminhtml_Sales_Order_Create_Worldpay_Direct';

    /**
     * Payment Bridge Payment Method Instance
     *
     * @var Magento_Pbridge_Model_Payment_Method_Pbridge
     */
    protected $_pbridgeMethodInstance = null;
    /**
     * Pbridge data
     *
     * @var Magento_Pbridge_Helper_Data
     */
    protected $_pbridgeData = null;

    /**
     * Store manager
     *
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param Magento_Core_Model_Logger $logger
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_ModuleListInterface $moduleList
     * @param Magento_Payment_Helper_Data $paymentData
     * @param Magento_Core_Model_Log_AdapterFactory $logAdapterFactory
     * @param Magento_Core_Model_LocaleInterface $locale
     * @param Magento_Centinel_Model_Service $centinelService
     * @param Magento_Pbridge_Helper_Data $pbridgeData
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Magento_Core_Model_Logger $logger,
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_ModuleListInterface $moduleList,
        Magento_Payment_Helper_Data $paymentData,
        Magento_Core_Model_Log_AdapterFactory $logAdapterFactory,
        Magento_Core_Model_LocaleInterface $locale,
        Magento_Centinel_Model_Service $centinelService,
        Magento_Pbridge_Helper_Data $pbridgeData,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->_pbridgeData = $pbridgeData;
        $this->_storeManager = $storeManager;
        parent::__construct($logger, $eventManager, $coreStoreConfig, $moduleList, $paymentData, $logAdapterFactory,
            $locale, $centinelService, $data);
    }

    /**
     * Return that current payment method is dummy
     * @return boolean
     */
    public function getIsDummy()
    {
        return true;
    }
    /**
     * Return Payment Bridge method instance
     *
     * @return Magento_Pbridge_Model_Payment_Method_Pbridge
     */
    public function getPbridgeMethodInstance()
    {
        if ($this->_pbridgeMethodInstance === null) {
            $this->_pbridgeMethodInstance = $this->_paymentData->getMethodInstance('pbridge');
            if ($this->_pbridgeMethodInstance) {
                $this->_pbridgeMethodInstance->setOriginalMethodInstance($this);
            }
        }
        return $this->_pbridgeMethodInstance;
    }

    /**
     * Retrieve original payment method code
     *
     * @return string
     */
    public function getOriginalCode()
    {
        return parent::getCode();
    }

    /**
     * Check whether payment method can be used
     *
     * @param Magento_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        if(!$this->getPbridgeMethodInstance() || !$this->getPbridgeMethodInstance()->isDummyMethodAvailable($quote)) {
            return false;
        }

        return true;
    }
    /**
     * Retrieve information from payment configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/'.$this->getOriginalCode().'/'.$field;
        return $this->_coreStoreConfig->getConfig($path, $storeId);
    }

    /**
     * Assign data to info model instance
     *
     * @param  mixed $data
     * @return Magento_Payment_Model_Info
     */
    public function assignData($data)
    {
        $this->getPbridgeMethodInstance()->assignData($data);
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return Magento_Pbridge_Model_Payment_Method_Worldpay_Direct
     */
    public function validate()
    {
        $this->getPbridgeMethodInstance()->validate();
        return $this;
    }

    /**
     * PSi Gate method being executed via Payment Bridge
     *
     * @param Magento_Object $payment
     * @param float $amount
     * @return Magento_Pbridge_Model_Payment_Method_Worldpay_Direct
     */
    public function authorize(Magento_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->authorize($payment, $amount);
        $payment->addData((array)$response);
        return $this;
    }
    /**
     * Capturing method being executed via Payment Bridge
     *
     * @param Magento_Object $payment
     * @param float $amount
     * @return Magento_Pbridge_Model_Payment_Method_Worldpay_Direct
     */
    public function capture(Magento_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->capture($payment, $amount);
        if (!$response) {
            $response = $this->getPbridgeMethodInstance()->authorize($payment, $amount);
        }
        $payment->addData((array)$response);
        return $this;
    }

    /**
     * Refunding method being executed via Payment Bridge
     *
     * @param Magento_Object $payment
     * @param float $amount
     * @return Magento_Pbridge_Model_Payment_Method_Worldpay_Direct
     */
    public function refund(Magento_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->refund($payment, $amount);
        $payment->addData((array)$response);
        $payment->setIsTransactionClosed(1);
        return $this;
    }

    /**
     * Voiding method being executed via Payment Bridge
     *
     * @param Magento_Object $payment
     * @return Magento_Pbridge_Model_Payment_Method_Worldpay_Direct
     */
    public function void(Magento_Object $payment)
    {
        $response = $this->getPbridgeMethodInstance()->void($payment);
        $payment->addData((array)$response);
        $payment->setIsTransactionClosed(1);
        return $this;
    }
    /**
     * Cancel method being executed via Payment Bridge
     *
     * @param Magento_Object $payment
     * @return Magento_Pbridge_Model_Payment_Method_Worldpay_Direct
     */
    public function cancel(Magento_Object $payment)
    {
        return $this->void($payment);
    }
    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }
    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        return $this->_storeManager->getStore()->isAdmin() ?
            $this->_backendFormBlockType :
            $this->_formBlockType;
    }

    /**
     * Store id setter, also set storeId to helper
     * @param int $store
     * @return Magento_Pbridge_Model_Payment_Method_Worldpay_Direct
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        $this->_pbridgeData->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }
    /**
     * Set capture transaction ID to invoice for informational purposes
     * @param Magento_Sales_Model_Order_Invoice $invoice
     * @param Magento_Sales_Model_Order_Payment $payment
     * @return Magento_Payment_Model_Method_Abstract
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }
}
