<?php
/**
 * Catalog navigation
 *
 * @package    Ecom
 * @subpackage Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Catalog_Block_Navigation extends Mage_Core_Block_Template
{
    function loadCategories($parent)
    {
        $categoryTree = Mage::getModel('catalog','category_tree')->getLevel($parent);
        $data  = array();
        foreach ($categoryTree as $item) {
            $data[] = array(
                'title' => $item->getData('attribute_value'),
                'id'    => $item->getId(),
            );
        }
        $this->assign('categories', $data);
    }
    
    public function loadProductManufacturers()
    {
        $manufacturers = Mage::getModel('catalog','product_attribute')
            ->loadByCode('manufacturer')
            ->getOptions()
                ->getHtmlOptions();
        $this->assign('manufacturers', $manufacturers);
    }
    
    public function loadProductTypes()
    {
        $types = Mage::getModel('catalog','product_attribute')
            ->loadByCode('type')
            ->getOptions()
                ->getHtmlOptions();
        $this->assign('types', $types);
    }    
}// Class Mage_Core_Block_List END