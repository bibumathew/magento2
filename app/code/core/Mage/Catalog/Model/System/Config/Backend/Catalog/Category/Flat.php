<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Flat category on/off backend
 */
class Mage_Catalog_Model_System_Config_Backend_Catalog_Category_Flat extends Mage_Core_Model_Config_Data
{
    /**
     * After enable flat category required reindex
     *
     * @return Mage_Catalog_Model_System_Config_Backend_Catalog_Category_Flat
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged() && $this->getValue()) {
            Mage::getSingleton('Mage_Index_Model_Indexer')->getProcessByCode('catalog_category_flat')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }
}
