<?php
/**
 * Modules configuration. Contains primary configuration and configuration from modules /etc/*.xml files
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Config_Modules extends Mage_Core_Model_Config_Base
{
    /**
     * @param Mage_Core_Model_Config_Loader_Modules $loader
     */
    public function __construct(Mage_Core_Model_Config_Loader_Modules $loader)
    {
        parent::__construct('<config><modules></modules></config>');
        $loader->load($this);
    }
}
