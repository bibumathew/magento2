<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Report collection abstract model
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Report_Collection_Abstract
    extends Mage_Reports_Model_Resource_Report_Collection_Abstract
{
    /**
     * Order status
     *
     * @var string
     */
    protected $_orderStatus        = null;

    /**
     * Set status filter
     *
     * @param string $orderStatus
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    public function addOrderStatusFilter($orderStatus)
    {
        $this->_orderStatus = $orderStatus;
        return $this;
    }

    /**
     * Apply order status filter
     *
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyOrderStatusFilter()
    {
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
        $this->getSelect()->where('order_status IN(?)', $orderStatus);
        return $this;
    }

    /**
     * Order status filter is custom for this collection
     *
     * @return Mage_Sales_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyCustomFilter()
    {
        return $this->_applyOrderStatusFilter();
    }
}