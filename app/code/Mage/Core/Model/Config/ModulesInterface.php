<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
interface Mage_Core_Model_Config_ModulesInterface extends Mage_Core_Model_ConfigInterface
{
    /**
     * Get module config node
     *
     * @param string $moduleName
     * @return Varien_Simplexml_Element
     */
    public function getModuleConfig($moduleName = '');
}