<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Persistent
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Persistent Shopping Cart Data Helper
 */
class Magento_Persistent_Helper_Data extends Magento_Core_Helper_Data
{
    const XML_PATH_ENABLED = 'persistent/options/enabled';
    const XML_PATH_LIFE_TIME = 'persistent/options/lifetime';
    const XML_PATH_LOGOUT_CLEAR = 'persistent/options/logout_clear';
    const XML_PATH_REMEMBER_ME_ENABLED = 'persistent/options/remember_enabled';
    const XML_PATH_REMEMBER_ME_DEFAULT = 'persistent/options/remember_default';
    const XML_PATH_PERSIST_SHOPPING_CART = 'persistent/options/shopping_cart';

    /**
     * Name of config file
     *
     * @var string
     */
    protected $_configFileName = 'persistent.xml';

    /**
     * Persistent session
     *
     * @var Magento_Persistent_Helper_Session
     */
    protected $_persistentSession;

    /**
     * Checkout data
     *
     * @var Magento_Checkout_Helper_Data
     */
    protected $_checkoutData;

    /**
     * Core url
     *
     * @var Magento_Core_Helper_Url
     */
    protected $_coreUrl;

    /**
     * @param Magento_Core_Helper_Context $context
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Core_Helper_Http $coreHttp
     * @param Magento_Core_Model_Config $config
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_StoreManager $storeManager
     * @param Magento_Core_Model_Locale $locale
     * @param Magento_Core_Model_Date $dateModel
     * @param Magento_Core_Model_App_State $appState
     * @param Magento_Core_Model_Encryption $encryptor
     * @param Magento_Core_Helper_Url $coreUrl
     * @param Magento_Checkout_Helper_Data $checkoutData
     * @param Magento_Persistent_Helper_Session $persistentSession
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        Magento_Core_Helper_Context $context,
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Core_Helper_Http $coreHttp,
        Magento_Core_Model_Config $config,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_StoreManager $storeManager,
        Magento_Core_Model_Locale $locale,
        Magento_Core_Model_Date $dateModel,
        Magento_Core_Model_App_State $appState,
        Magento_Core_Model_Encryption $encryptor,
        Magento_Core_Helper_Url $coreUrl,
        Magento_Checkout_Helper_Data $checkoutData,
        Magento_Persistent_Helper_Session $persistentSession,
        $dbCompatibleMode = true
    ) {
        $this->_coreUrl = $coreUrl;
        $this->_checkoutData = $checkoutData;
        $this->_persistentSession = $persistentSession;
        parent::__construct(
            $eventManager,
            $coreHttp,
            $context,
            $config,
            $coreStoreConfig,
            $storeManager,
            $locale,
            $dateModel,
            $appState,
            $encryptor,
            $dbCompatibleMode
        );
    }

    /**
     * Checks whether Persistence Functionality is enabled
     *
     * @param int|string|Magento_Core_Model_Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_ENABLED, $store);
    }

    /**
     * Checks whether "Remember Me" enabled
     *
     * @param int|string|Magento_Core_Model_Store $store
     * @return bool
     */
    public function isRememberMeEnabled($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_REMEMBER_ME_ENABLED, $store);
    }

    /**
     * Is "Remember Me" checked by default
     *
     * @param int|string|Magento_Core_Model_Store $store
     * @return bool
     */
    public function isRememberMeCheckedDefault($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_REMEMBER_ME_DEFAULT, $store);
    }

    /**
     * Is shopping cart persist
     *
     * @param int|string|Magento_Core_Model_Store $store
     * @return bool
     */
    public function isShoppingCartPersist($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_PERSIST_SHOPPING_CART, $store);
    }

    /**
     * Get Persistence Lifetime
     *
     * @param int|string|Magento_Core_Model_Store $store
     * @return int
     */
    public function getLifeTime($store = null)
    {
        $lifeTime = intval($this->_coreStoreConfig->getConfig(self::XML_PATH_LIFE_TIME, $store));
        return ($lifeTime < 0) ? 0 : $lifeTime;
    }

    /**
     * Check if set `Clear on Logout` in config settings
     *
     * @return bool
     */
    public function getClearOnLogout()
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_LOGOUT_CLEAR);
    }

    /**
     * Retrieve url for unset long-term cookie
     *
     * @return string
     */
    public function getUnsetCookieUrl()
    {
        return $this->_getUrl('persistent/index/unsetCookie');
    }

    /**
     * Retrieve name of persistent customer
     *
     * @return string
     */
    public function getPersistentName()
    {
        return __('(Not %1?)', $this->escapeHtml($this->_persistentSession->getCustomer()->getName()));
    }

    /**
     * Retrieve path for config file
     *
     * @return string
     */
    public function getPersistentConfigFilePath()
    {
        return $this->_coreConfig->getModuleDir('etc', $this->_getModuleName()) . DS . $this->_configFileName;
    }

    /**
     * Check whether specified action should be processed
     *
     * @param Magento_Event_Observer $observer
     * @return bool
     */
    public function canProcess($observer)
    {
        $action = $observer->getEvent()->getAction();
        $controllerAction = $observer->getEvent()->getControllerAction();

        if ($action instanceof Magento_Core_Controller_Varien_Action) {
            return !$action->getFlag('', Magento_Core_Controller_Varien_Action::FLAG_NO_START_SESSION);
        }
        if ($controllerAction instanceof Magento_Core_Controller_Varien_Action) {
            return !$controllerAction->getFlag('', Magento_Core_Controller_Varien_Action::FLAG_NO_START_SESSION);
        }
        return true;
    }

    /**
     * Get create account url depends on checkout
     *
     * @param  $url string
     * @return string
     */
    public function getCreateAccountUrl($url)
    {
        if ($this->_checkoutData->isContextCheckout()) {
            $url = $this->_coreUrl->addRequestParam($url, array('context' => 'checkout'));
        }
        return $url;
    }

}
