<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Gift wrapping checkout process options block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Checkout_Options extends Mage_Core_Block_Template
{
    protected $_designCollection;

    /**
     * Gift wrapping collection
     *
     * @return Enterprise_GiftWrapping_Model_Resource_Mysql4_Wrapping_Collection
     */
    public function getDesignCollection()
    {
        if (is_null($this->_designCollection)) {
            $store = Mage::app()->getStore();
            $this->_designCollection = Mage::getModel('enterprise_giftwrapping/wrapping')->getCollection()
                ->addStoreAttributesToResult($store->getId())
                ->applyStatusFilter()
                ->applyWebsiteFilter($store->getWebsiteId());
        }
        return $this->_designCollection;
    }

    /**
     * Select element for choosing gift wrapping design
     *
     * @return array
     */
    public function getDesignSelectHtml()
    {
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setData(array(
                'id'    => 'giftwrapping_{{id}}',
                'class' => 'select'
            ))
            ->setName('giftwrapping[{{id}}][design]')
            ->setOptions($this->getDesignCollection()->toOptionArray());
        return $select->getHtml();
    }

    /**
     * Get quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Calculate including tax price
     *
     * @param Varien_Object $item
     * @param mixed $basePrice
     * @param bool $includeTax
     * @return string
     */
    public function calculatePrice($item, $basePrice, $includeTax = false)
    {
        $shippingAddress = $this->_getQuote()->getShippingAddress();
        $billingAddress  = $this->_getQuote()->getBillingAddress();

        $taxClass = Mage::helper('enterprise_giftwrapping')->getWrappingTaxClass();
        $item->setTaxClassId($taxClass);

        $price = Mage::helper('enterprise_giftwrapping')->getPrice($item, $basePrice, $includeTax, $shippingAddress,
            $billingAddress
        );
        return Mage::helper('core')->currency($price, true, false);
    }

    /**
     * Return gift wrapping designs info
     *
     * @return Varien_Object
     */
    public function getDesignsInfo()
    {
        $data = array();
        foreach ($this->getDesignCollection()->getItems() as $item) {
            if ($this->getDisplayWrappingBothPrices()) {
                $temp['price_incl_tax'] = $this->calculatePrice($item, $item->getBasePrice(), true);
                $temp['price_excl_tax'] = $this->calculatePrice($item, $item->getBasePrice());
            } else {
                $temp['price'] = $this->calculatePrice($item, $item->getBasePrice(),
                    $this->getDisplayWrappingIncludeTaxPrice()
                );
            }
            $temp['path'] = $item->getImageUrl();
            $data[$item->getId()] = $temp;
        }
       return new Varien_Object($data);
    }

    /**
     * Prepare and return quote items info
     *
     * @return Varien_Object
     */
    public function getItemsInfo()
    {
        $data = array();
        if ($this->_getQuote()->getIsMultiShipping()) {
            foreach ($this->_getQuote()->getAllShippingAddresses() as $address) {
                $this->_processItems($address->getAllItems(), $data);
            }
        } else {
            $this->_processItems($this->_getQuote()->getAllItems(), $data);
        }
        return new Varien_Object($data);
    }

    /**
     * Process items
     *
     * @param array $items
     * @param array $data
     * @return array
     */
    protected function _processItems($items, &$data)
    {
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $allowed = $item->getProduct()->getGiftWrappingAvailable();
            if (Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForProduct($allowed)) {
                $temp = array();
                if ($price = $item->getProduct()->getGiftWrappingPrice()) {
                    if ($this->getDisplayWrappingBothPrices()) {
                        $temp['price_incl_tax'] = $this->calculatePrice(new Varien_Object(), $price, true);
                        $temp['price_excl_tax'] = $this->calculatePrice(new Varien_Object(), $price);
                    } else {
                        $temp['price'] = $this->calculatePrice(new Varien_Object(), $price,
                            $this->getDisplayWrappingIncludeTaxPrice()
                        );
                    }
                }
                $data[$item->getId()] = $temp;
            }
        }
        return $data;
    }

    /**
     * Prepare and return printed card info
     *
     * @return Varien_Object
     */
    public function getCardInfo()
    {
        $data = array();
        if ($this->getAllowPrintedCard()) {
            $price = Mage::helper('enterprise_giftwrapping')->getPrintedCardPrice();
            if ($price) {
                 if ($this->getDisplayCardBothPrices()) {
                     $data['price_incl_tax'] = $this->calculatePrice(new Varien_Object(), $price, true);
                     $data['price_excl_tax'] = $this->calculatePrice(new Varien_Object(), $price);
                 } else {
                    $data['price'] = $this->calculatePrice(new Varien_Object(), $price,
                        $this->getDisplayCardIncludeTaxPrice()
                    );
                }
            }
        }
        return new Varien_Object($data);
    }

    /**
     * Check display both prices for gift wrapping
     *
     * @return bool
     */
    public function getDisplayWrappingBothPrices()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartWrappingBothPrices();
    }

    /**
     * Check display both prices for printed card
     *
     * @return bool
     */
    public function getDisplayCardBothPrices()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartCardBothPrices();
    }

    /**
     * Check display prices including tax for gift wrapping
     *
     * @return bool
     */
    public function getDisplayWrappingIncludeTaxPrice()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartWrappingIncludeTaxPrice();
    }

    /**
     * Check display price including tax for printed card
     *
     * @return bool
     */
    public function getDisplayCardIncludeTaxPrice()
    {
        return Mage::helper('enterprise_giftwrapping')->displayCartCardIncludeTaxPrice();
    }

    /**
     * Check allow printed card
     *
     * @return bool
     */
    public function getAllowPrintedCard()
    {
        return Mage::helper('enterprise_giftwrapping')->allowPrintedCard();
    }

    /**
     * Check allow gift receipt
     *
     * @return bool
     */
    public function getAllowGiftReceipt()
    {
        return Mage::helper('enterprise_giftwrapping')->allowGiftReceipt();
    }

    /**
     * Check allow gift wrapping on order level
     *
     * @return bool
     */
    public function getAllowForOrder()
    {
        return Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForOrder();
    }

    /**
     * Check allow gift wrapping on order items
     *
     * @return bool
     */
    public function getAllowForItems()
    {
        return Mage::helper('enterprise_giftwrapping')->isGiftWrappingAvailableForItems();
    }

    /**
     * Check allow gift wrapping for order
     *
     * @return bool
     */
    public function canDisplayGiftWrapping()
    {
        return $this->getAllowForOrder()
            || $this->getAllowForItems()
            || $this->getAllowPrintedCard()
            || $this->getAllowGiftReceipt();
    }
}
