<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_PageCache
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Page cache data helper
 *
 * @category    Magento
 * @package     Magento_PageCache
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_PageCache_Helper_Data extends Magento_Core_Helper_Abstract
{
    /**
     * Paths to external cache config options
     */
    const XML_PATH_EXTERNAL_CACHE_ENABLED  = 'system/external_page_cache/enabled';
    const XML_PATH_EXTERNAL_CACHE_LIFETIME = 'system/external_page_cache/cookie_lifetime';

    /**
     * Cookie name for disabling external caching
     */
    const NO_CACHE_COOKIE = 'external_no_cache';

    /**
     * Cookie name for locking the NO_CACHE_COOKIE for modification
     */
    const NO_CACHE_LOCK_COOKIE = 'external_no_cache_cookie_locked';

    /**
     * @var bool
     */
    protected $_isNoCacheCookieLocked = false;

    /**
     * Core store config
     *
     * @var Magento_Core_Model_Store_Config
     */
    protected $_coreStoreConfig;

    /**
     * @var Magento_Core_Model_Cookie
     */
    protected $_cookie;

    /**
     * @var Magento_PageCache_Model_CacheControlFactory
     */
    protected $_ccFactory;

    /**
     * Initialize 'no cache' cookie locking
     *
     * @param Magento_PageCache_Model_CacheControlFactory $ccFactory
     * @param Magento_Core_Model_Cookie $cookie
     * @param Magento_Core_Helper_Context $context
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     */
    function __construct(
        Magento_PageCache_Model_CacheControlFactory $ccFactory,
        Magento_Core_Model_Cookie $cookie,
        Magento_Core_Helper_Context $context,
        Magento_Core_Model_Store_Config $coreStoreConfig
    ) {
        parent::__construct($context);
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_isNoCacheCookieLocked = (bool)$cookie->get(self::NO_CACHE_LOCK_COOKIE);
        $this->_cookie = $cookie;
        $this->_ccFactory = $ccFactory;
    }

    /**
     * Retrieve the cookie model instance
     *
     * @return Magento_Core_Model_Cookie
     */
    protected function _getCookie()
    {
        return $this->_cookie;
    }

    /**
     * Check whether external cache is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_EXTERNAL_CACHE_ENABLED);
    }

    /**
     * Initialize proper external cache control model
     *
     * @throws Magento_Core_Exception
     * @return Magento_PageCache_Model_Control_Interface
     */
    public function getCacheControlInstance()
    {
        return $this->_ccFactory->getCacheControlInstance();
    }

    /**
     * Disable caching on external storage side by setting special cookie, if the cookie has not been locked
     *
     * @param int|null $lifetime
     * @return Magento_PageCache_Helper_Data
     */
    public function setNoCacheCookie($lifetime = null)
    {
        if ($this->_isNoCacheCookieLocked) {
            return $this;
        }
        $lifetime = $lifetime !== null ? $lifetime : $this->_coreStoreConfig->getConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
        if ($this->_cookie->get(self::NO_CACHE_COOKIE)) {
            $this->_cookie->renew(self::NO_CACHE_COOKIE, $lifetime);
        } else {
            $this->_cookie->set(self::NO_CACHE_COOKIE, '1', $lifetime);
        }
        return $this;
    }

    /**
     * Remove the 'no cache' cookie, if it has not been locked
     *
     * @return Magento_PageCache_Helper_Data
     */
    public function removeNoCacheCookie()
    {
        if (!$this->_isNoCacheCookieLocked) {
            $this->_cookie->delete(self::NO_CACHE_COOKIE);
        }
        return $this;
    }

    /**
     * Disable modification of the 'no cache' cookie
     *
     * @return Magento_PageCache_Helper_Data
     */
    public function lockNoCacheCookie()
    {
        $this->_cookie->set(self::NO_CACHE_LOCK_COOKIE, '1', 0);
        $this->_isNoCacheCookieLocked = true;
        return $this;
    }

    /**
     * Enable modification of the 'no cache' cookie
     *
     * @return Magento_PageCache_Helper_Data
     */
    public function unlockNoCacheCookie()
    {
        $this->_cookie->delete(self::NO_CACHE_LOCK_COOKIE);
        $this->_isNoCacheCookieLocked = false;
        return $this;
    }

    /**
     * Returns a lifetime of cookie for external cache
     *
     * @return string Time in seconds
     */
    public function getNoCacheCookieLifetime()
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
    }
}
