<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_CatalogRule
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog Rule Product Aggregated Price per date Model
 *
 * @method Mage_CatalogRule_Model_Resource_Rule_Product_Price _getResource()
 * @method Mage_CatalogRule_Model_Resource_Rule_Product_Price getResource()
 * @method string getRuleDate()
 * @method Mage_CatalogRule_Model_Rule_Product_Price setRuleDate(string $value)
 * @method int getCustomerGroupId()
 * @method Mage_CatalogRule_Model_Rule_Product_Price setCustomerGroupId(int $value)
 * @method int getProductId()
 * @method Mage_CatalogRule_Model_Rule_Product_Price setProductId(int $value)
 * @method float getRulePrice()
 * @method Mage_CatalogRule_Model_Rule_Product_Price setRulePrice(float $value)
 * @method int getWebsiteId()
 * @method Mage_CatalogRule_Model_Rule_Product_Price setWebsiteId(int $value)
 * @method string getLatestStartDate()
 * @method Mage_CatalogRule_Model_Rule_Product_Price setLatestStartDate(string $value)
 * @method string getEarliestEndDate()
 * @method Mage_CatalogRule_Model_Rule_Product_Price setEarliestEndDate(string $value)
 *
 * @category    Mage
 * @package     Mage_CatalogRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogRule_Model_Rule_Product_Price extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_CatalogRule_Model_Resource_Rule_Product_Price');
    }

    /**
     * Apply price rule price to price index table
     *
     * @param Magento_DB_Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param string $websiteId
     * @param array $updateFields       the array fields for compare with rule price and update
     * @param string $websiteDate
     * @return Mage_CatalogRule_Model_Rule_Product_Price
     */
    public function applyPriceRuleToIndexTable(Magento_DB_Select $select, $indexTable, $entityId, $customerGroupId,
        $websiteId, $updateFields, $websiteDate)
    {

        $this->_getResource()->applyPriceRuleToIndexTable($select, $indexTable, $entityId, $customerGroupId, $websiteId,
            $updateFields, $websiteDate);

        return $this;
    }
}
