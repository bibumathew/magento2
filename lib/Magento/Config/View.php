<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Framework
 * @subpackage  Config
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * View configuration files handler
 */
class Magento_Config_View extends Magento_Config_XmlAbstract
{
    /**
     * Path to view.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/etc/view.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $dom
     * @return array
     */
    protected function _extractData(DOMDocument $dom)
    {
        $result = array();
        /** @var $varsNode DOMElement */
        foreach ($dom->childNodes->item(0)/*root*/->childNodes as $varsNode) {
            $moduleName = $varsNode->getAttribute('module');
            /** @var $varNode DOMElement */
            foreach ($varsNode->getElementsByTagName('var') as $varNode) {
                $varName = $varNode->getAttribute('name');
                $varValue = $varNode->nodeValue;
                $result[$moduleName][$varName] = $varValue;
            }
        }
        return $result;
    }

    /**
     * Get a list of variables in scope of specified module
     *
     * Returns array(<var_name> => <var_value>)
     *
     * @param string $module
     * @return array
     */
    public function getVars($module)
    {
        return isset($this->_data[$module]) ? $this->_data[$module] : array();
    }

    /**
     * Get value of a configuration option variable
     *
     * @param string $module
     * @param string $var
     * @return string|false
     */
    public function getVarValue($module, $var)
    {
        return isset($this->_data[$module][$var]) ? $this->_data[$module][$var] : false;
    }

    /**
     * Return copy of DOM
     *
     * @return Magento_Config_Dom
     */
    public function getDomConfigCopy()
    {
        return clone $this->_getDomConfigModel();
    }

    /**
     * Getter for initial view.xml contents
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?><view></view>';
    }

    /**
     * Variables are identified by module and name
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array('/view/vars' => 'module', '/view/vars/var' => 'name');
    }
}
