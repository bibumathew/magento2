<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_TargetRule
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_TargetRule_Block_Adminhtml_Targetrule extends Magento_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize invitation manage page
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_targetrule';
        $this->_blockGroup = 'Magento_TargetRule';
        $this->_headerText = Mage::helper('Magento_TargetRule_Helper_Data')->__('Related Products Rule');
        $this->_addButtonLabel = Mage::helper('Magento_TargetRule_Helper_Data')->__('Add Rule');
        parent::_construct();
    }

}
