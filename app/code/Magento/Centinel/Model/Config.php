<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Centinel
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Config centinel model
 */
class Magento_Centinel_Model_Config
{
    /**
     * Store id or store model
     *
     * @var int|Magento_Core_Model_Store
     */
    protected $_store = false;

    /**
     * Path of centinel config
     *
     * @var string
     */
    protected $_serviceConfigPath = 'payment_services/centinel';

    /**
     * Core data
     *
     * @var Magento_Core_Helper_Data
     */
    protected $_coreData = null;

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
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_Config $coreConfig
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Config $coreConfig
    ) {
        $this->_coreData = $coreData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_coreConfig = $coreConfig;
    }

    /**
     * Set store to congif model
     *
     * @param int|Magento_Core_Model_Store $store
     * @return Magento_Centinel_Model_Config
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Return store
     *
     * @return int|Magento_Core_Model_Store
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * Return centinel processorId
     *
     * @return string
     */
    public function getProcessorId()
    {
        return $this->_getServiceConfigValue('processor_id');
    }

    /**
     * Return centinel merchantId
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->_getServiceConfigValue('merchant_id');
    }

    /**
     * Return centinel transactionPwd
     *
     * @return string
     */
    public function getTransactionPwd()
    {
        return $this->_coreData->decrypt($this->_getServiceConfigValue('password'));
    }

    /**
     * Return flag - is centinel mode test
     *
     * @return bool
     */
    public function getIsTestMode()
    {
        return (bool)(int)$this->_getServiceConfigValue('test_mode');
    }

    /**
     * Return value of node of centinel config section
     *
     * @param string $key
     * @return string
     */
    private function _getServiceConfigValue($key)
    {
        return $this->_coreStoreConfig->getConfig($this->_serviceConfigPath . '/' . $key, $this->getStore());
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->_getServiceConfigValue('debug');
    }
}
