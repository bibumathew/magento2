<?php
/**
 * Product text attribute model
 *
 * @package    Ecom
 * @subpackage Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Catalog_Model_Mysql4_Product_Attribute_Text extends Mage_Catalog_Model_Mysql4_Product_Attribute_Abstract
{
    public function __construct() 
    {
        $this->_attributeValueTable = Mage::registry('resources')->getTableName('catalog_resource', 'product_attribute_text');
    }
}