<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CurrencySymbol
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Custom currency symbol model
 *
 * @category    Magento
 * @package     Magento_CurrencySymbol
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_CurrencySymbol_Model_System_Currencysymbol
{
    /**
     * Custom currency symbol properties
     *
     * @var array
     */
    protected $_symbolsData = array();

    /**
     * Store id
     *
     * @var string | null
     */
    protected $_storeId;

    /**
     * Website id
     *
     * @var string | null
     */
    protected $_websiteId;
    /**
     * Cache types which should be invalidated
     *
     * @var array
     */
    protected $_cacheTypes = array(
        Magento_Core_Model_Cache_Type_Config::TYPE_IDENTIFIER,
        Magento_Core_Model_Cache_Type_Block::TYPE_IDENTIFIER,
        Magento_Core_Model_Cache_Type_Layout::TYPE_IDENTIFIER,
    );

    /**
     * Config path to custom currency symbol value
     */
    const XML_PATH_CUSTOM_CURRENCY_SYMBOL = 'currency/options/customsymbol';
    const XML_PATH_ALLOWED_CURRENCIES     = 'currency/options/allow';

    /*
     * Separator used in config in allowed currencies list
     */
    const ALLOWED_CURRENCIES_CONFIG_SEPARATOR = ',';

    /**
     * Config currency section
     */
    const CONFIG_SECTION = 'currency';

    /**
     * Core event manager proxy
     *
     * @var Magento_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * @var Magento_Core_Model_Cache_TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var Magento_Backend_Model_Config_Factory
     */
    protected $_configFactory;

    /**
     * @var Magento_Core_Model_System_Store
     */
    protected $_systemStore;

    /**
     * @var Magento_Core_Model_StoreManager
     */
    protected $_storeManager;

    /**
     * @var Magento_Core_Model_LocaleInterface
     */
    protected $_locale;

    /**
     * @var Magento_Core_Model_Config
     */
    protected $_coreConfig;

    /**
     * Core store config
     *
     * @var Magento_Core_Model_Store_Config
     */
    protected $_coreStoreConfig;

    /**
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_Config $coreConfig
     * @param Magento_Backend_Model_Config_Factory $configFactory
     * @param Magento_Core_Model_Cache_TypeListInterface $cacheTypeList
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Core_Model_LocaleInterface $locale
     * @param Magento_Core_Model_System_Store $systemStore
     * @param Magento_Core_Model_Event_Manager $eventManager
     */
    public function __construct(
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Config $coreConfig,
        Magento_Backend_Model_Config_Factory $configFactory,
        Magento_Core_Model_Cache_TypeListInterface $cacheTypeList,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Core_Model_LocaleInterface $locale,
        Magento_Core_Model_System_Store $systemStore,
        Magento_Core_Model_Event_Manager $eventManager
    ) {
        $this->_coreConfig = $coreConfig;
        $this->_configFactory = $configFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        $this->_systemStore  = $systemStore;
        $this->_eventManager = $eventManager;
        $this->_coreStoreConfig = $coreStoreConfig;
    }

    /**
     * Sets store Id
     *
     * @param  $storeId
     * @return Magento_CurrencySymbol_Model_System_Currencysymbol
     */
    public function setStoreId($storeId=null)
    {
        $this->_storeId = $storeId;
        $this->_symbolsData = array();

        return $this;
    }

    /**
     * Sets website Id
     *
     * @param  $websiteId
     * @return Magento_CurrencySymbol_Model_System_Currencysymbol
     */
    public function setWebsiteId($websiteId=null)
    {
        $this->_websiteId = $websiteId;
        $this->_symbolsData = array();

        return $this;
    }

    /**
     * Returns currency symbol properties array based on config values
     *
     * @return array
     */
    public function getCurrencySymbolsData()
    {
        if ($this->_symbolsData) {
            return $this->_symbolsData;
        }

        $this->_symbolsData = array();

        $allowedCurrencies = explode(
            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
            $this->_coreStoreConfig->getConfig(self::XML_PATH_ALLOWED_CURRENCIES, null)
        );

        /* @var $storeModel Magento_Core_Model_System_Store */
        $storeModel = $this->_systemStore;
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $websiteSymbols  = $website->getConfig(self::XML_PATH_ALLOWED_CURRENCIES);
                        $allowedCurrencies = array_merge($allowedCurrencies, explode(
                            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
                            $websiteSymbols
                        ));
                    }
                    $storeSymbols = $this->_coreStoreConfig->getConfig(self::XML_PATH_ALLOWED_CURRENCIES, $store);
                    $allowedCurrencies = array_merge($allowedCurrencies, explode(
                        self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
                        $storeSymbols
                    ));
                }
            }
        }
        ksort($allowedCurrencies);

        $currentSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);

        foreach ($allowedCurrencies as $code) {
            if (!$symbol = $this->_locale->getTranslation($code, 'currencysymbol')) {
                $symbol = $code;
            }
            $name = $this->_locale->getTranslation($code, 'nametocurrency');
            if (!$name) {
                $name = $code;
            }
            $this->_symbolsData[$code] = array(
                'parentSymbol'  => $symbol,
                'displayName' => $name
            );

            if (isset($currentSymbols[$code]) && !empty($currentSymbols[$code])) {
                $this->_symbolsData[$code]['displaySymbol'] = $currentSymbols[$code];
            } else {
                $this->_symbolsData[$code]['displaySymbol'] = $this->_symbolsData[$code]['parentSymbol'];
            }
            if ($this->_symbolsData[$code]['parentSymbol'] == $this->_symbolsData[$code]['displaySymbol']) {
                $this->_symbolsData[$code]['inherited'] = true;
            } else {
                $this->_symbolsData[$code]['inherited'] = false;
            }
        }

        return $this->_symbolsData;
    }

    /**
     * Saves currency symbol to config
     *
     * @param  $symbols array
     * @return Magento_CurrencySymbol_Model_System_Currencysymbol
     */
    public function setCurrencySymbolsData($symbols=array())
    {
        foreach ($this->getCurrencySymbolsData() as $code => $values) {
            if (isset($symbols[$code])) {
                if ($symbols[$code] == $values['parentSymbol'] || empty($symbols[$code]))
                unset($symbols[$code]);
            }
        }
        if ($symbols) {
            $value['options']['fields']['customsymbol']['value'] = serialize($symbols);
        } else {
            $value['options']['fields']['customsymbol']['inherit'] = 1;
        }

        $this->_configFactory->create()
            ->setSection(self::CONFIG_SECTION)
            ->setWebsite(null)
            ->setStore(null)
            ->setGroups($value)
            ->save();

        $this->_eventManager->dispatch('admin_system_config_changed_section_currency_before_reinit',
            array('website' => $this->_websiteId, 'store' => $this->_storeId)
        );

        // reinit configuration
        $this->_coreConfig->reinit();
        $this->_storeManager->reinitStores();

        $this->clearCache();

        $this->_eventManager->dispatch('admin_system_config_changed_section_currency',
            array('website' => $this->_websiteId, 'store' => $this->_storeId)
        );

        return $this;
    }

    /**
     * Returns custom currency symbol by currency code
     *
     * @param  $code
     * @return bool|string
     */
    public function getCurrencySymbol($code)
    {
        $customSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);
        if (array_key_exists($code, $customSymbols)) {
            return $customSymbols[$code];
        }

        return false;
    }

    /**
     * Clear translate cache
     *
     * @return Magento_CurrencySymbol_Model_System_Currencysymbol
     */
    public function clearCache()
    {
        // clear cache for frontend
        foreach ($this->_cacheTypes as $cacheType) {
            $this->_cacheTypeList->invalidate($cacheType);
        }
        return $this;
    }

    /**
     * Unserialize data from Store Config.
     *
     * @param string $configPath
     * @param int $storeId
     * @return array
     */
    protected function _unserializeStoreConfig($configPath, $storeId = null)
    {
        $result = array();
        $configData = (string)$this->_coreStoreConfig->getConfig($configPath, $storeId);
        if ($configData) {
            $result = unserialize($configData);
        }

        return is_array($result) ? $result : array();
    }
}
