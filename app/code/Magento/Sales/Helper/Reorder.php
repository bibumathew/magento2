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
 * Sales module base helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Sales_Helper_Reorder extends Magento_Core_Helper_Data
{
    const XML_PATH_SALES_REORDER_ALLOW = 'sales/reorder/allow';

    public function isAllow()
    {
        return $this->isAllowed();
    }

    /**
     * Check if reorder is allowed for given store
     *
     * @param Magento_Core_Model_Store|int|null $store
     * @return bool
     */
    public function isAllowed($store = null)
    {
        if ($this->_coreStoreConfig->getConfig(self::XML_PATH_SALES_REORDER_ALLOW, $store)) {
            return true;
        }
        return false;
    }

    public function canReorder(Magento_Sales_Model_Order $order)
    {
        if (!$this->isAllowed($order->getStore())) {
            return false;
        }
        if (Mage::getSingleton('Magento_Customer_Model_Session')->isLoggedIn()) {
            return $order->canReorder();
        } else {
            return true;
        }
    }
}
