<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Gift wrapping order items view block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Order_View_Items
    extends Enterprise_GiftWrapping_Block_Adminhtml_Order_View_Abstract
{
    /**
     * Prepare and return order items info
     *
     * @return Varien_Object
     */
    public function getItemsInfo()
    {
        $data = array();
        foreach ($this->getOrder()->getAllItems() as $item) {
            if ($this->getDisplayWrappingBothPrices()) {
                 $temp['price_excl_tax'] = $this->_preparePrices($item->getGwBasePrice(), $item->getGwPrice());
                 $temp['price_incl_tax'] = $this->_preparePrices(
                    $item->getGwBasePrice() + $item->getGwBaseTaxAmount(),
                    $item->getGwPrice() + $item->getGwTaxAmount()
                 );
            } else if ($this->getDisplayWrappingPriceInclTax()) {
                $temp['price'] = $this->_preparePrices(
                    $item->getGwBasePrice() + $item->getGwBaseTaxAmount(),
                    $item->getGwPrice() + $item->getGwTaxAmount()
                );
            } else {
                $temp['price'] = $this->_preparePrices($item->getGwBasePrice(),$item->getGwPrice());
            }
            $temp['design'] = $item->getGwId();
            $data[$item->getId()] = $temp;
        }
        return new Varien_Object($data);
    }

    /**
     * Check ability to display gift wrapping for order items
     *
     * @return bool
     */
    public function canDisplayGiftWrappingForItems()
    {
        foreach ($this->getOrder()->getAllItems() as $item) {
            if ($item->getGwId()) {
                return true;
            }
        }
        return false;
    }
}