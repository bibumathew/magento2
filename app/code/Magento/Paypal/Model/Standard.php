<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * PayPal Standard Checkout Module
 */
class Magento_Paypal_Model_Standard extends Magento_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_code  = Magento_Paypal_Model_Config::METHOD_WPS;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento_Paypal_Block_Standard_Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento_Paypal_Block_Payment_Info';

    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;

    /**
     * Config instance
     *
     * @var Magento_Paypal_Model_Config
     */
    protected $_config;

    /**
     * @var Magento_Paypal_Model_Session
     */
    protected $_paypalSession;

    /**
     * @var Magento_Checkout_Model_Session
     */
    protected $_checkoutSession;

    /**
     * @var Magento_Core_Model_UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento_Sales_Model_OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Magento_Paypal_Model_Api_StandardFactory
     */
    protected $_apiStandardFactory;

    /**
     * @var Magento_Paypal_Model_CartFactory
     */
    protected $_cartFactory;

    /**
     * @var Magento_Paypal_Model_Config_Factory
     */
    protected $_configFactory;

    /**
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Payment_Helper_Data $paymentData
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_Session_Generic $paypalSession
     * @param Magento_Checkout_Model_Session $checkoutSession
     * @param Magento_Core_Model_UrlInterface $urlBuilder
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Sales_Model_OrderFactory $orderFactory
     * @param Magento_Paypal_Model_Api_StandardFactory $apiStandardFactory
     * @param Magento_Paypal_Model_CartFactory $cartFactory
     * @param Magento_Paypal_Model_Config_Factory $configFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Payment_Helper_Data $paymentData,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Session_Generic $paypalSession,
        Magento_Checkout_Model_Session $checkoutSession,
        Magento_Core_Model_UrlInterface $urlBuilder,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Sales_Model_OrderFactory $orderFactory,
        Magento_Paypal_Model_Api_StandardFactory $apiStandardFactory,
        Magento_Paypal_Model_CartFactory $cartFactory,
        Magento_Paypal_Model_Config_Factory $configFactory,
        array $data = array()
    ) {
        $this->_paypalSession = $paypalSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->_orderFactory = $orderFactory;
        $this->_apiStandardFactory = $apiStandardFactory;
        $this->_cartFactory = $cartFactory;
        $this->_configFactory = $configFactory;
        parent::__construct(
            $eventManager, $paymentData, $coreStoreConfig, $data
        );
    }

    /**
     * Whether method is available for specified currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getConfig()->isCurrencyCodeSupported($currencyCode);
    }

    /**
     * Get paypal session namespace
     *
     * @return Magento_Core_Model_Session_Generic
     */
    public function getSession()
    {
        return $this->_paypalSession;
    }

    /**
     * Get checkout session namespace
     *
     * @return Magento_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get current quote
     *
     * @return Magento_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Create main block for standard form
     *
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('Magento_Paypal_Block_Standard_Form', $name)
            ->setMethod('paypal_standard')
            ->setPayment($this->getPayment())
            ->setTemplate('standard/form.phtml');

        return $block;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
          return $this->_urlBuilder->getUrl('paypal/standard/redirect', array('_secure' => true));
    }

    /**
     * Return form field array
     *
     * @return array
     */
    public function getStandardCheckoutFormFields()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
        /* @var $api Magento_Paypal_Model_Api_Standard */
        $api = $this->_apiStandardFactory->create()->setConfigObject($this->getConfig());
        $api->setOrderId($orderIncrementId)
            ->setCurrencyCode($order->getBaseCurrencyCode())
            //->setPaymentAction()
            ->setOrder($order)
            ->setNotifyUrl($this->_urlBuilder->getUrl('paypal/ipn/'))
            ->setReturnUrl($this->_urlBuilder->getUrl('paypal/standard/success'))
            ->setCancelUrl($this->_urlBuilder->getUrl('paypal/standard/cancel'));

        // export address
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();
        if ($isOrderVirtual) {
            $api->setNoShipping(true);
        } elseif ($address->validate()) {
            $api->setAddress($address);
        }

        // add cart totals and line items
        $parameters = array('params' => array($order));
        $api->setPaypalCart($this->_cartFactory->create($parameters))
            ->setIsLineItemsEnabled($this->_config->lineItemsEnabled);
        $api->setCartSummary($this->_getAggregatedCartSummary());
        $api->setLocale($api->getLocaleCode());
        $result = $api->getStandardCheckoutRequest();
        return $result;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param object $stateObject
     * @return \Magento_Payment_Model_Abstract|null
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Magento_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    /**
     * Config instance getter
     * @return Magento_Paypal_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->_code);
            $store = $this->getStore();
            if ($store) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }
            $this->_config = $this->_configFactory->create('Magento_Paypal_Model_Config', array('params' => $params));
        }
        return $this->_config;
    }

    /**
     * Check whether payment method can be used
     * @param Magento_Sales_Model_Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote) && $this->getConfig()->isMethodAvailable()) {
            return true;
        }
        return false;
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getConfig()->$field;
    }

    /**
     * Aggregated cart summary label getter
     *
     * @return string
     */
    private function _getAggregatedCartSummary()
    {
        if ($this->_config->lineItemsSummary) {
            return $this->_config->lineItemsSummary;
        }
        return $this->_storeManager->getStore($this->getStore())->getFrontendName();
    }
}
