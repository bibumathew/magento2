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
 * Adminhtml order creditmemo totals block
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Totals
{
    protected $_creditmemo;

    public function getCreditmemo()
    {
        if ($this->_creditmemo === null) {
            if ($this->hasData('creditmemo')) {
                $this->_creditmemo = $this->_getData('creditmemo');
            } elseif (Mage::registry('current_creditmemo')) {
                $this->_creditmemo = Mage::registry('current_creditmemo');
            } elseif ($this->getParentBlock() && $this->getParentBlock()->getCreditmemo()) {
                $this->_creditmemo = $this->getParentBlock()->getCreditmemo();
            }
        }
        return $this->_creditmemo;
    }

    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Initialize creditmemo totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->addTotal(new Varien_Object(array(
            'code'      => 'adjustment_positive',
            'value'     => $this->getSource()->getAdjustmentPositive(),
            'base_value'=> $this->getSource()->getBaseAdjustmentPositive(),
            'label'     => $this->helper('Mage_Sales_Helper_Data')->__('Adjustment Refund')
        )));
        $this->addTotal(new Varien_Object(array(
            'code'      => 'adjustment_negative',
            'value'     => $this->getSource()->getAdjustmentNegative(),
            'base_value'=> $this->getSource()->getBaseAdjustmentNegative(),
            'label'     => $this->helper('Mage_Sales_Helper_Data')->__('Adjustment Fee')
        )));
        return $this;
    }
}