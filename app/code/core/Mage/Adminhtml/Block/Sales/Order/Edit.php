<?php
/**
 * Adminhtml sales order edit
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Michael Bessolov <michael@varien.com>
 */

class Mage_Adminhtml_Block_Sales_Order_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'sales_order';

        parent::__construct();

        $this->_updateButton('save', 'label', __('Save Order'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        if (Mage::registry('sales_order')->getId()) { // TOCHECK
            return __('Edit Order #') . Mage::registry('sales_order')->getRealOrderId();
        }
        else {
            return __('New Order');
        }
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/sales_order/view', array('order_id' => Mage::registry('sales_order')->getId()));
    }

}
