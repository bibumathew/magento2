<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog price rules
 *
 * @category    Mage
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Promo_Catalog extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        $this->_addButton('apply_rules', array(
            'label'     => Mage::helper('Mage_CatalogRule_Helper_Data')->__('Apply Rules'),
            'onclick'   => "location.href='".$this->getUrl('*/*/applyRules')."'",
            'class'     => '',
        ));

        $this->_controller = 'promo_catalog';
        $this->_headerText = Mage::helper('Mage_CatalogRule_Helper_Data')->__('Catalog Price Rules');
        $this->_addButtonLabel = Mage::helper('Mage_CatalogRule_Helper_Data')->__('Add New Rule');
        parent::_construct();

    }
}