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
 * Adminhtml customers group page content block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Group extends Mage_Adminhtml_Block_Template
{

    /**
     * Modify header & button labels
     *
     */
    protected function _construct()
    {
        $this->_controller = 'customer_group';
        $this->_headerText = Mage::helper('Mage_Customer_Helper_Data')->__('Customer Groups');
        $this->_addButtonLabel = Mage::helper('Mage_Customer_Helper_Data')->__('Add New Customer Group');
        parent::_construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass() {
        return 'icon-head head-customer-groups';
    }
}
