<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml super product links grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author	   Ivan Chepurnyi <mitch@varien.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct() 
	{
		parent::__construct();
		$this->setDefaultFilter(array('in_products'=>1));
        $this->setUseAjax(true);
		$this->setId('super_product_links');
	}
	
	protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
            	$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            else {
                if($productIds) {
                	$this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            	}
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    protected function _prepareCollection()
    {
        $product =  Mage::registry('product');
       	$collection = Mage::getResourceModel('catalog/product_collection')
       		->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('price')
            ->addFieldToFilter('attribute_set_id',$product->getAttributeSetId())
            ->addFieldToFilter('type_id',1);
        
        $oldStoreId = $collection->getEntity()->getStoreId();  
        $collection->getEntity()->setStore(0);
        
       	foreach ($product->getSuperAttributesIds() as $attributeId) {
       		$collection->addAttributeToSelect($attributeId);
       	}
		
       	
       	
        $this->setCollection($collection);
        
        parent::_prepareCollection();
        
        $collection->getEntity()->setStore($oldStoreId);  
        return $this;
    }
	
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', null);
                        
        if (!is_array($products)) {
            $products =  array_keys(Mage::registry('product')->getSuperLinks());
        }
        
        return $products;
    }
    
    protected function _prepareColumns()
    {
    	$product = Mage::registry('product');
    	$attributes = $product->getSuperAttributes(true);
    	
    	
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'entity_id',
            'renderer'	=> 'adminhtml/catalog_product_edit_tab_super_config_grid_renderer_checkbox',
            'attributes' => $attributes
        ));
        
        $this->addColumn('id', array(
            'header'    => __('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => __('Name'),
            'index'     => 'name'
        ));
        
        $types = Mage::getResourceModel('catalog/product_type_collection')
            ->load()
            ->toOptionHash();

        $this->addColumn('type',
            array(
                'header'=> __('Type'),
                'width' => '100px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => $types,
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getConfig()->getId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> __('Attrib. Set Name'),
                'width' => '130px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));
        
        $this->addColumn('sku', array(
            'header'    => __('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('price', array(
            'header'    => __('Price'),
            'type'      => 'currency',
            'currency_code' => (string) Mage::getStoreConfig('general/currency/base'),
            'index'     => 'price'
        ));
                        
        
        foreach ($attributes as $attribute) {
		    $this->addColumn($attribute->getAttributeCode(), array(
		        'header'    => __($attribute->getFrontend()->getLabel()),
		        'index'     => $attribute->getAttributeCode(),
		        'type'		=> $attribute->getSourceModel() ? 'options' : 'number',
		        'options'   => $attribute->getSourceModel() ? $this->getOptions($attribute) : ''
		    ));
        }
         
        
        return parent::_prepareColumns();
    }
    
    public function getOptions($attribute) {
    	$result = array();
    	foreach ($attribute->getSource()->getAllOptions() as $option) {
    		if($option['value']!='') {
     			$result[$option['value']] = $option['label'];
    		}    		
    	}
    	
    	return $result;
    }
    
    public function getGridUrl()
    {
        return Mage::getUrl('*/*/superConfig', array('_current'=>true));
    }
}// Class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid END