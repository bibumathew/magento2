<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog product tier price backend attribute model
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Model_Resource_Product_Attribute_Backend_Tierprice
    extends Magento_Catalog_Model_Resource_Product_Attribute_Backend_Groupprice_Abstract
{
    /**
     * Initialize connection and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity_tier_price', 'value_id');
    }

    /**
     * Add qty column
     *
     * @param array $columns
     * @return array
     */
    protected function _loadPriceDataColumns($columns)
    {
        $columns = parent::_loadPriceDataColumns($columns);
        $columns['price_qty'] = 'qty';
        return $columns;
    }

    /**
     * Order by qty
     *
     * @param Magento_DB_Select $select
     * @return Magento_DB_Select
     */
    protected function _loadPriceDataSelect($select)
    {
        $select->order('qty');
        return $select;
    }
}
