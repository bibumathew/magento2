<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Banner Setup Resource Model
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Banner_Model_Resource_Setup extends Magento_Sales_Model_Resource_Setup
{
    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Model_Config_Resource $resourcesConfig
     * @param Magento_Core_Model_Config_Modules $modulesConfig
     * @param Magento_Core_Model_ModuleListInterface $moduleList
     * @param Magento_Core_Model_Resource $resource
     * @param Magento_Core_Model_Config_Modules_Reader $modulesReader
     * @param Magento_Core_Model_CacheInterface $cache
     * @param $resourceName
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Model_Config_Resource $resourcesConfig,
        Magento_Core_Model_Config_Modules $modulesConfig,
        Magento_Core_Model_ModuleListInterface $moduleList,
        Magento_Core_Model_Resource $resource,
        Magento_Core_Model_Config_Modules_Reader $modulesReader,
        Magento_Core_Model_CacheInterface $cache,
        $resourceName
    ) {
        parent::__construct($coreData, $resourcesConfig, $modulesConfig, $moduleList, $resource, $modulesReader, $cache,
            $resourceName);
    }
}
