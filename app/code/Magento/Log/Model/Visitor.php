<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Log
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * @method Magento_Log_Model_Resource_Visitor _getResource()
 * @method Magento_Log_Model_Resource_Visitor getResource()
 * @method string getSessionId()
 * @method Magento_Log_Model_Visitor setSessionId(string $value)
 * @method Magento_Log_Model_Visitor setFirstVisitAt(string $value)
 * @method Magento_Log_Model_Visitor setLastVisitAt(string $value)
 * @method int getLastUrlId()
 * @method Magento_Log_Model_Visitor setLastUrlId(int $value)
 * @method int getStoreId()
 * @method Magento_Log_Model_Visitor setStoreId(int $value)
 *
 * @category    Magento
 * @package     Magento_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Log_Model_Visitor extends Magento_Core_Model_Abstract
{
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;
    const VISITOR_TYPE_CUSTOMER = 'c';
    const VISITOR_TYPE_VISITOR  = 'v';

    /**
     * @var bool
     */
    protected $_skipRequestLogging = false;

    /**
     * Core http
     *
     * @var Magento_Core_Helper_Http
     */
    protected $_coreHttp = null;

    /**
     * @var array
     */
    protected $_ignoredUserAgents;

    /**
     * Core event manager proxy
     *
     * @var Magento_Core_Model_Event_Manager
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var Magento_Core_Model_Store_Config
     */
    protected $_coreStoreConfig;

    /**
     * @var Magento_Core_Model_Config
     */
    protected $_coreConfig;

    /**
     * Ignored Modules
     *
     * @var array
     */
    protected $_ignores;

    /*
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento_Core_Model_Session
     */
    protected $_session;

    /**
     * @var Magento_Sales_Model_QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var Magento_Customer_Model_CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param Magento_Core_Model_Context               $context
     * @param Magento_Core_Model_Registry              $registry
     * @param Magento_Core_Model_Store_Config          $coreStoreConfig
     * @param Magento_Core_Model_Event_Manager         $eventManager
     * @param Magento_Customer_Model_CustomerFactory   $customerFactory
     * @param Magento_Sales_Model_QuoteFactory         $quoteFactory
     * @param Magento_Core_Model_Session               $session
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Core_Helper_Http                 $coreHttp
     * @param Magento_Core_Model_Config                $coreConfig
     * @param array                                    $data
     * @param array                                    $ignoredUserAgents
     * @param array                                    $ignores
     * @param Magento_Core_Model_Resource_Abstract     $resource
     * @param Magento_Data_Collection_Db               $resourceCollection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Magento_Core_Model_Context $context,
        Magento_Core_Model_Registry $registry,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Customer_Model_CustomerFactory $customerFactory,
        Magento_Sales_Model_QuoteFactory $quoteFactory,
        Magento_Core_Model_Session $session,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Core_Helper_Http $coreHttp,
        Magento_Core_Model_Config $coreConfig,
        array $data = array(),
        array $ignoredUserAgents = array(),
        array $ignores = array(),
        Magento_Core_Model_Resource_Abstract $resource = null,
        Magento_Data_Collection_Db $resourceCollection = null
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_eventManager = $eventManager;
        $this->_customerFactory = $customerFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_session = $session;
        $this->_storeManager = $storeManager;
        $this->_coreHttp = $coreHttp;
        $this->_coreConfig = $coreConfig;
        $this->_ignoredUserAgents = $ignoredUserAgents;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_ignores = $ignores;
    }

    /**
     * Object initialization
     */
    protected function _construct()
    {
        $this->_init('Magento_Log_Model_Resource_Visitor');
        $userAgent = $this->_coreHttp->getHttpUserAgent();
        if ($this->_ignoredUserAgents) {
            if (in_array($userAgent, $this->_ignoredUserAgents)) {
                $this->_skipRequestLogging = true;
            }
        }
    }

    /**
     * Retrieve session object
     *
     * @return Magento_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Initialize visitor information from server data
     *
     * @return Magento_Log_Model_Visitor
     */
    public function initServerData()
    {
        $this->addData(array(
            'server_addr'           => $this->_coreHttp->getServerAddr(true),
            'remote_addr'           => $this->_coreHttp->getRemoteAddr(true),
            'http_secure'           => $this->_storeManager->getStore()->isCurrentlySecure(),
            'http_host'             => $this->_coreHttp->getHttpHost(true),
            'http_user_agent'       => $this->_coreHttp->getHttpUserAgent(true),
            'http_accept_language'  => $this->_coreHttp->getHttpAcceptLanguage(true),
            'http_accept_charset'   => $this->_coreHttp->getHttpAcceptCharset(true),
            'request_uri'           => $this->_coreHttp->getRequestUri(true),
            'session_id'            => $this->_getSession()->getSessionId(),
            'http_referer'          => $this->_coreHttp->getHttpReferer(true),
        ));

        return $this;
    }

    /**
     * Return Online Minutes Interval
     *
     * @return int Minutes Interval
     */
    public function getOnlineMinutesInterval()
    {
        $configValue = $this->_coreStoreConfig->getConfig('customer/online_customers/online_minutes_interval');
        return intval($configValue) > 0
            ? intval($configValue)
            : self::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }

    /**
     * Retrieve url from model data
     *
     * @return string
     */
    public function getUrl()
    {
        $url = 'http' . ($this->getHttpSecure() ? 's' : '') . '://';
        $url .= $this->getHttpHost().$this->getRequestUri();
        return $url;
    }

    public function getFirstVisitAt()
    {
        if (!$this->hasData('first_visit_at')) {
            $this->setData('first_visit_at', now());
        }
        return $this->getData('first_visit_at');
    }

    public function getLastVisitAt()
    {
        if (!$this->hasData('last_visit_at')) {
            $this->setData('last_visit_at', now());
        }
        return $this->getData('last_visit_at');
    }

    /**
     * Initialization visitor information by request
     *
     * Used in event "controller_action_predispatch"
     *
     * @param   Magento_Event_Observer $observer
     * @return  Magento_Log_Model_Visitor
     */
    public function initByRequest($observer)
    {
        if ($this->_skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }

        $this->setData($this->_getSession()->getVisitorData());
        $this->initServerData();

        if (!$this->getId()) {
            $this->setFirstVisitAt(now());
            $this->setIsNewVisitor(true);
            $this->save();
            $this->_eventManager->dispatch('visitor_init', array('visitor' => $this));
        }
        return $this;
    }

    /**
     * Saving visitor information by request
     *
     * Used in event "controller_action_postdispatch"
     *
     * @param   Magento_Event_Observer $observer
     * @return  Magento_Log_Model_Visitor
     */
    public function saveByRequest($observer)
    {
        if ($this->_skipRequestLogging || $this->isModuleIgnored($observer)) {
            return $this;
        }

        try {
            $this->setLastVisitAt(now());
            $this->save();
            $this->_getSession()->setVisitorData($this->getData());
        } catch (Exception $e) {
            $this->_logger->logException($e);
        }
        return $this;
    }

    /**
     * Bind customer data when customer login
     *
     * Used in event "customer_login"
     *
     * @param   Magento_Event_Observer $observer
     * @return  Magento_Log_Model_Visitor
     */
    public function bindCustomerLogin($observer)
    {
        if (!$this->getCustomerId() && $customer = $observer->getEvent()->getCustomer()) {
            $this->setDoCustomerLogin(true);
            $this->setCustomerId($customer->getId());
        }
        return $this;
    }

    /**
     * Bind customer data when customer logout
     *
     * Used in event "customer_logout"
     *
     * @param   Magento_Event_Observer $observer
     * @return  Magento_Log_Model_Visitor
     */
    public function bindCustomerLogout($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($this->getCustomerId() && $customer) {
            $this->setDoCustomerLogout(true);
        }
        return $this;
    }

    /**
     * @param Magento_Event_Observer $observer
     * @return $this
     */
    public function bindQuoteCreate($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote) {
            if ($quote->getIsCheckoutCart()) {
                $this->setQuoteId($quote->getId());
                $this->setDoQuoteCreate(true);
            }
        }
        return $this;
    }

    /**
     * @param Magento_Event_Observer $observer
     * @return $this
     */
    public function bindQuoteDestroy($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote) {
            $this->setDoQuoteDestroy(true);
        }
        return $this;
    }

    /**
     * Methods for research (depends from customer online admin section)
     */
    public function addIpData($data)
    {
        $ipData = array();
        $data->setIpData($ipData);
        return $this;
    }

    /**
     * @param object $data
     * @return $this
     */
    public function addCustomerData($data)
    {
        $customerId = $data->getCustomerId();
        if (intval($customerId) <= 0) {
            return $this;
        }
        $customerData = $this->_customerFactory->create()->load($customerId);
        $newCustomerData = array();
        foreach ($customerData->getData() as $propName => $propValue) {
            $newCustomerData['customer_' . $propName] = $propValue;
        }

        $data->addData($newCustomerData);
        return $this;
    }

    /**
     * @param object $data
     * @return $this
     */
    public function addQuoteData($data)
    {
        $quoteId = $data->getQuoteId();
        if (intval($quoteId) <= 0) {
            return $this;
        }
        $data->setQuoteData($this->_quoteFactory->create()->load($quoteId));
        return $this;
    }

    /**
     * @param Magento_Event_Observer $observer
     * @return bool
     */
    public function isModuleIgnored($observer)
    {
        if (is_array($this->_ignores) && $observer) {
            $curModule = $observer->getEvent()->getControllerAction()->getRequest()->getRouteName();
            if (isset($this->_ignores[$curModule])) {
                return true;
            }
        }
        return false;
    }
}
