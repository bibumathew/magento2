<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml sales order create sidebar viewed block
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

class Viewed extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_viewed');
        $this->setDataId('viewed');
    }

    /**
     * Retrieve display block availability
     *
     * @return bool
     */
    public function canDisplay()
    {
        return false;
    }

    /**
     * Retrieve availability removing items in block
     *
     * @return bool
     */
    public function canRemoveItems()
    {
        return false;
    }

    public function getHeaderText()
    {
        return __('Recently Viewed');
    }

}
