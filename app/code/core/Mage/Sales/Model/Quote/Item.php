<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quote item model
 *
 * @category   Mage
 * @package    Mage_Sales
 */
class Mage_Sales_Model_Quote_Item extends Mage_Sales_Model_Quote_Item_Abstract
{
    protected $_eventPrefix = 'sales_quote_item';
    protected $_eventObject = 'item';

    /**
     * Quote model object
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote       = null;
    protected $_options = array();
    protected $_optionsByCode = array();

    function _construct()
    {
        $this->_init('sales/quote_item');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->setIsVirtual($this->getProduct()->getIsVirtual());
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        return $this;
    }

    /**
     * Declare quote model object
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    protected function _prepareQty($qty)
    {
        $qty = Mage::app()->getLocale()->getNumber($qty);
        $qty = ($qty > 0) ? $qty : 1;
        return $qty;
    }

    /**
     * Adding quantity to quote item
     *
     * @param   float $qty
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function addQty($qty)
    {
        $oldQty = $this->getQty();
        $qty = $this->_prepareQty($qty);
        $this->setQty($oldQty+$qty);
        return $this;
    }

    /**
     * Declare quote item quantity
     *
     * @param   float $qty
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function setQty($qty)
    {
        $qty    = $this->_prepareQty($qty);
        $oldQty = $this->_getData('qty');
        $this->setData('qty', $qty);

        Mage::dispatchEvent('sales_quote_item_qty_set_after', array('item'=>$this));

        if ($this->getQuote() && $this->getQuote()->getIgnoreOldQty()) {
            return $this;
        }
        if ($this->getUseOldQty()) {
            $this->setData('qty', $oldQty);
        }
        return $this;
    }

    /**
     * Retrieve option product with Qty
     *
     * Return array
     * 'qty'        => the qty
     * 'product'    => the product model
     *
     * @return array
     */
    public function getQtyOptions()
    {
        $productIds = array();
        $return     = array();
        foreach ($this->getOptions() as $option) {
            /* @var $option Mage_Sales_Model_Quote_Item_Option */
            if ($option->getProduct()->getId() != $this->getProduct()->getId()
                && !isset($productIds[$option->getProduct()->getId()])) {
                $productIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
            }
        }

        foreach ($productIds as $productId) {
            if ($option = $this->getOptionByCode('product_qty_' . $productId)) {
                $return[$productId] = $option;
            }
        }

        return $return;
    }

    /**
     * Setup product for quote item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function setProduct($product)
    {
        $this->setData('product', $product)
            ->setProductId($product->getId())
            ->setProductType($product->getTypeId())
            //->setSku($product->getSku())
            ->setSku($this->getProduct()->getSku())
            ->setName($product->getName())
            //->setWeight($product->getWeight())
            ->setWeight($this->getProduct()->getWeight())
            ->setTaxClassId($product->getTaxClassId())
            ->setCost($product->getCost())
            ->setIsQtyDecimal($product->getIsQtyDecimal());

//        if ($options = $product->getCustomOptions()) {
//            foreach ($options as $option) {
//                $this->addOption($option);
//            }
//        }
        return $this;
    }

    /**
     * Retrieve product model object associated with item
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (($product === null) && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getQuote()->getStoreId())
                ->load($this->getProductId());
            $this->setProduct($product);
        }
        $product->setCustomOptions($this->_optionsByCode);
        return $product;
    }

    /**
     * Check product representation in item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if ($itemProduct->getId() != $product->getId()) {
            return false;
        }

        $itemOptions    = $this->getOptions();
        $productOptions = $product->getCustomOptions();
        if (count($itemOptions) != count($productOptions)) {
            return false;
        }

        foreach ($itemOptions as $option) {
            $code = $option->getCode();
            if ( !isset($productOptions[$code])
                || ($productOptions[$code]->getValue() === null)
                || $productOptions[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compare item
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  bool
     */
    public function compare($item)
    {
        if ($this->getProductId() != $item->getProductId()) {
            return false;
        }
        foreach ($this->getOptions() as $option) {
            if ($itemOption = $item->getOptionByCode($option->getCode())) {
                if ($itemOption->getValue() != $option->getValue()) {
                    return false;
                }
            }
            else {
                return false;
            }
        }
        return true;
    }

    /**
     * Get item product type
     *
     * @return string
     */
    public function getProductType()
    {
        if ($option = $this->getOptionByCode('product_type')) {
            return $option->getValue();
        }
        if ($product = $this->getProduct()) {
            return $product->getTypeId();
        }
        return $this->_getData('product_type');
    }

    public function toArray(array $arrAttributes=array())
    {
        $data = parent::toArray($arrAttributes);

        if ($product = $this->getProduct()) {
            $data['product'] = $product->toArray();
        }
        return $data;
    }

    /**
     * Initialize quote item options
     *
     * @param   array $options
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function setOptions($options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
        return $this;
    }

    /**
     * Get all item options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Add option to item
     *
     * @param   Mage_Sales_Model_Quote_Item_Option $option
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function addOption($option)
    {
        if (is_array($option)) {
            $option = Mage::getModel('sales/quote_item_option')->setData($option)
                ->setItem($this);
        }
        elseif (($option instanceof Varien_Object) && !($option instanceof Mage_Sales_Model_Quote_Item_Option)) {
            $option = Mage::getModel('sales/quote_item_option')->setData($option->getData())
               ->setProduct($option->getProduct())
               ->setItem($this);
        }
        elseif($option instanceof Mage_Sales_Model_Quote_Item_Option) {
            $option->setItem($this);
        }
        else {
            Mage::throwException(Mage::helper('sales')->__('Invalid item option format'));
        }

        if ($exOption = $this->getOptionByCode($option->getCode())) {
            $exOption->addData($option->getData());
        }
        else {
            $this->_addOptionCode($option);
            $this->_options[] = $option;
        }
        return $this;
    }

    /**
     * Register option code
     *
     * @param   Mage_Sales_Model_Quote_Item_Option $option
     * @return  Mage_Sales_Model_Quote_Item
     */
    protected function _addOptionCode($option)
    {
        if (!isset($this->_optionsByCode[$option->getCode()])) {
            $this->_optionsByCode[$option->getCode()] = $option;
        }
        else {
            Mage::throwException(Mage::helper('sales')->__('Item option with code %s already exist', $option->getCode()));
        }
        return $this;
    }

    /**
     * Get item option by code
     *
     * @param   string $code
     * @return  Mage_Sales_Model_Quote_Item_Option || null
     */
    public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code])) {
            return $this->_optionsByCode[$code];
        }
        return null;
    }

    /**
     * Save item options
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    protected function _afterSave()
    {
        foreach ($this->_options as $option) {
            if ($option->isDeleted()) {
                $option->delete();
            }
            else {
                $option->save();
            }
        }
        return parent::_afterSave();
    }

    /**
     * Clone quote item
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function __clone()
    {
        parent::__clone();
        $options = $this->getOptions();
        $this->_quote           = null;
        $this->_options         = array();
        $this->_optionsByCode   = array();
        foreach ($options as $option) {
            $this->addOption(clone $option);
        }
        return $this;
    }
}