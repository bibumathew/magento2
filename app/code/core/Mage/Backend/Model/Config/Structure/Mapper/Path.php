<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * System Configuration Path Mapper
 */
class Mage_Backend_Model_Config_Structure_Mapper_Path extends Mage_Backend_Model_Config_Structure_MapperAbstract
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data)
    {
        if ($this->_hasValue('config/system/sections', $data)) {
            foreach ($data['config']['system']['sections'] as &$sectionConfig) {
                if ($this->_hasValue('children', $sectionConfig)) {
                    foreach ($sectionConfig['children'] as &$groupConfig) {
                        $groupConfig = $this->_processConfig($groupConfig, $sectionConfig);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Process configuration
     *
     * @param array $elementConfig
     * @param array $parentConfig
     * @return array
     */
    protected function _processConfig(array $elementConfig, array $parentConfig)
    {
        $parentPath = $this->_hasValue('path', $parentConfig) ? $parentConfig['path'] . '/' : '';
        $parentPath .= $parentConfig['id'];
        $elementConfig['path'] = $parentPath;

        if ($this->_hasValue('children', $elementConfig)) {
            foreach ($elementConfig['children'] as &$subConfig) {
                $subConfig = $this->_processConfig($subConfig, $elementConfig);
            }
        }

        return $elementConfig;
    }
}