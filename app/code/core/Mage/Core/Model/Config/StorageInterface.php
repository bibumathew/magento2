<?php
/**
 * Application config storage interface
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
interface Mage_Core_Model_Config_StorageInterface
{
    /**
     * Get loaded configuration
     *
     * @return Mage_Core_Model_ConfigInterface
     */
    public function getConfiguration();

    /**
     * Remove configuration cache
     */
    public function removeCache();
}