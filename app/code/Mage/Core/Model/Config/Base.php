<?php
/**
 * Abstract configuration class
 * Used to retrieve core configuration values
 *
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Config_Base extends Magento_Simplexml_Config implements Mage_Core_Model_ConfigInterface
{
    /**
     * List of instances
     *
     * @var array
     */
    public static $instances = array();

    /**
     * @param string|Magento_Simplexml_Element $sourceData $sourceData
     */
    public function __construct($sourceData = null)
    {
        $this->_elementClass = 'Mage_Core_Model_Config_Element';
        parent::__construct($sourceData);
        self::$instances[] = $this;
    }

    /**
     * Reinitialize config object
     */
    public function reinit()
    {

    }

    /**
     * Cleanup objects because of simplexml memory leak
     */
    public static function destroy()
    {
        if (is_array(self::$instances)) {
            foreach (self::$instances  as $instance) {
                $instance->_xml = null;
            }
        }
        self::$instances = array();
    }
}
