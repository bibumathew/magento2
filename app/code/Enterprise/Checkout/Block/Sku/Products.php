<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Checkout
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * SKU failed products Block
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 */
class Enterprise_Checkout_Block_Sku_Products extends Magento_Checkout_Block_Cart
{
    /**
     * Checkout data
     *
     * @var Enterprise_Checkout_Helper_Data
     */
    protected $_checkoutData = null;

    /**
     * Core url
     *
     * @var Magento_Core_Helper_Url
     */
    protected $_coreUrl = null;

    /**
     * Catalog image
     *
     * @var Magento_Catalog_Helper_Image
     */
    protected $_catalogImage = null;

    /**
     * @param Magento_Catalog_Helper_Image $catalogImage
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Catalog_Helper_Data $catalogData
     * @param Magento_Checkout_Helper_Cart $checkoutCart
     * @param Magento_Core_Helper_Url $coreUrl
     * @param Enterprise_Checkout_Helper_Data $checkoutData
     * @param Magento_Core_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Magento_Catalog_Helper_Image $catalogImage,
        Magento_Core_Helper_Data $coreData,
        Magento_Catalog_Helper_Data $catalogData,
        Magento_Checkout_Helper_Cart $checkoutCart,
        Magento_Core_Helper_Url $coreUrl,
        Enterprise_Checkout_Helper_Data $checkoutData,
        Magento_Core_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_catalogImage = $catalogImage;
        $this->_coreUrl = $coreUrl;
        $this->_checkoutData = $checkoutData;
        parent::__construct($checkoutCart, $catalogData, $coreData, $context, $data);
    }

    /**
     * Return list of product items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_getHelper()->getFailedItems();
    }

    /**
     * Retrieve helper instance
     *
     * @return Enterprise_Checkout_Helper_Data
     */
    protected function _getHelper()
    {
        return $this->_checkoutData;
    }

    /**
     * Retrieve link for deleting all failed items
     *
     * @return string
     */
    public function getDeleteAllItemsUrl()
    {
        return $this->getUrl('checkout/cart/removeAllFailed');
    }

    /**
     * Retrieve failed items form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('checkout/cart/addFailedItems');
    }

    /**
     * Prepare cart items URLs
     */
    public function prepareItemUrls()
    {
        $products = array();
        /* @var $item Magento_Sales_Model_Quote_Item */
        foreach ($this->getItems() as $item) {
            if ($item->getProductType() == 'undefined') {
                continue;
            }
            $product    = $item->getProduct();
            $option     = $item->getOptionByCode('product_type');
            if ($option) {
                $product = $option->getProduct();
            }

            if ($item->getStoreId() != Mage::app()->getStore()->getId()
                && !$item->getRedirectUrl()
                && !$product->isVisibleInSiteVisibility())
            {
                $products[$product->getId()] = $item->getStoreId();
            }
        }

        if ($products) {
            $products = Mage::getResourceSingleton('Magento_Catalog_Model_Resource_Url')
                ->getRewriteByProductStore($products);
            foreach ($this->getItems() as $item) {
                if ($item->getProductType() == 'undefined') {
                    continue;
                }
                $product    = $item->getProduct();
                $option     = $item->getOptionByCode('product_type');
                if ($option) {
                    $product = $option->getProduct();
                }

                if (isset($products[$product->getId()])) {
                    $object = new Magento_Object($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($object);
                }
            }
        }
    }

    /**
     * Get item row html
     *
     * @param Magento_Sales_Model_Quote_Item $item
     * @return string
     */
    public function getItemHtml(Magento_Sales_Model_Quote_Item $item)
    {
        /** @var $renderer Magento_Checkout_Block_Cart_Item_Renderer */
        $renderer = $this->getItemRenderer($item->getProductType())->setQtyMode(false);
        if ($item->getProductType() == 'undefined') {
            $renderer->overrideProductThumbnail($this->_catalogImage->init($item, 'thumbnail'));
            $renderer->setProductName('');
        }
        $renderer->setDeleteUrl(
            $this->getUrl('checkout/cart/removeFailed', array(
                'sku' => $this->_coreUrl->urlEncode($item->getSku())
            ))
        );
        $renderer->setIgnoreProductUrl(!$this->showItemLink($item));

        // Don't display subtotal column
        $item->setNoSubtotal(true);
        return parent::getItemHtml($item);
    }

    /**
     * Check whether item link should be rendered
     *
     * @param Magento_Sales_Model_Quote_Item $item
     * @return bool
     */
    public function showItemLink(Magento_Sales_Model_Quote_Item $item)
    {
        $product = $item->getProduct();
        if ($product->isComposite()) {
            $productsByGroups = $product->getTypeInstance()->getProductsToPurchaseByReqGroups($product);
            foreach ($productsByGroups as $productsInGroup) {
                foreach ($productsInGroup as $childProduct) {
                    if (($childProduct->hasStockItem() && $childProduct->getStockItem()->getIsInStock())
                        && !$childProduct->isDisabled()
                    ) {
                        return true;
                    }
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Added failed items existence validation before block html generation
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::getSingleton('Enterprise_Checkout_Model_Cart')->getFailedItems()) {
            $html = parent::_toHtml();
        } else {
            $html = '';
        }
        return $html;
    }
}
