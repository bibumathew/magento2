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
 * @method \Magento\Log\Model\Resource\Visitor _getResource()
 * @method \Magento\Log\Model\Resource\Visitor getResource()
 * @method string getSessionId()
 * @method \Magento\Log\Model\Visitor setSessionId(string $value)
 * @method \Magento\Log\Model\Visitor setFirstVisitAt(string $value)
 * @method \Magento\Log\Model\Visitor setLastVisitAt(string $value)
 * @method int getLastUrlId()
 * @method \Magento\Log\Model\Visitor setLastUrlId(int $value)
 * @method int getStoreId()
 * @method \Magento\Log\Model\Visitor setStoreId(int $value)
 *
 * @category    Magento
 * @package     Magento_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model;

class Visitor extends \Magento\Core\Model\AbstractModel
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
     * @var \Magento\Core\Helper\Http
     */
    protected $_coreHttp = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Core\Model\Event\Manager
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
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Core_Helper_Http $coreHttp
     * @param Magento_Core_Model_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_Config $coreConfig
     * @param Magento_Core_Model_Resource_Abstract $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Event\Manager $eventManager,
        \Magento\Core\Helper\Http $coreHttp,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Config $coreConfig,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_coreHttp = $coreHttp;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_coreConfig = $coreConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Onject initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Visitor');
        $userAgent = $this->_coreHttp->getHttpUserAgent();
        $ignoreAgents = $this->_coreConfig->getNode('global/ignore_user_agents');
        if ($ignoreAgents) {
            $ignoreAgents = $ignoreAgents->asArray();
            if (in_array($userAgent, $ignoreAgents)) {
                $this->_skipRequestLogging = true;
            }
        }
    }

    /**
     * Retrieve session object
     *
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    protected function _getSession()
    {
        return \Mage::getSingleton('Magento\Core\Model\Session');
    }

    /**
     * Initialize visitor information from server data
     *
     * @return \Magento\Log\Model\Visitor
     */
    public function initServerData()
    {
        $this->addData(array(
            'server_addr'           => $this->_coreHttp->getServerAddr(true),
            'remote_addr'           => $this->_coreHttp->getRemoteAddr(true),
            'http_secure'           => \Mage::app()->getStore()->isCurrentlySecure(),
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
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
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
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
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
        } catch (\Exception $e) {
            \Mage::logException($e);
        }
        return $this;
    }

    /**
     * Bind customer data when customer login
     *
     * Used in event "customer_login"
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
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
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Log\Model\Visitor
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
     * @param \Magento\Event\Observer $observer
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
     * @param \Magento\Event\Observer $observer
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
        $customerData = \Mage::getModel('Magento\Customer\Model\Customer')->load($customerId);
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
        $data->setQuoteData(\Mage::getModel('Magento\Sales\Model\Quote')->load($quoteId));
        return $this;
    }

    /**
     * @param \Magento\Event\Observer $observer
     * @return bool
     */
    public function isModuleIgnored($observer)
    {
        $ignores = $this->_coreConfig->getNode('global/ignoredModules/entities')->asArray();

        if (is_array($ignores) && $observer) {
            $curModule = $observer->getEvent()->getControllerAction()->getRequest()->getRouteName();
            if (isset($ignores[$curModule])) {
                return true;
            }
        }
        return false;
    }
}
