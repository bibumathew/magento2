<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_AdvancedCheckout
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * SKU failed products Block
 *
 * @category   Magento
 * @package    Magento_AdvancedCheckout
 */
namespace Magento\AdvancedCheckout\Block\Sku;

class Products extends \Magento\Checkout\Block\Cart
{
    /**
     * @var \Magento\AdvancedCheckout\Helper\Data
     */
    protected $_checkoutData;

    /**
     * @var \Magento\Core\Helper\Url
     */
    protected $_coreUrl;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\AdvancedCheckout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Catalog\Model\Resource\Url
     */
    protected $_catalogUrlResource;

    /**
     * @param \Magento\AdvancedCheckout\Model\Cart $cart
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrlResource
     * @param \Magento\Core\Helper\Url $coreUrl
     * @param \Magento\AdvancedCheckout\Helper\Data $checkoutData
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrlBuilder
     * @param \Magento\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\AdvancedCheckout\Model\Cart $cart,
        \Magento\Catalog\Model\Resource\Url $catalogUrlResource,
        \Magento\Core\Helper\Url $coreUrl,
        \Magento\AdvancedCheckout\Helper\Data $checkoutData,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Url $catalogUrlBuilder,
        \Magento\UrlInterface $urlBuilder,
        array $data = array()
    ) {
        $this->_cart = $cart;
        $this->_catalogUrlResource = $catalogUrlResource;
        $this->_coreUrl = $coreUrl;
        $this->_checkoutData = $checkoutData;
        $this->_storeManager = $storeManager;
        parent::__construct($catalogData, $coreData, $context, $customerSession, $checkoutSession, $storeManager,
            $catalogUrlBuilder, $urlBuilder, $data);
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
     * @return \Magento\AdvancedCheckout\Helper\Data
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
        /* @var $item \Magento\Sales\Model\Quote\Item */
        foreach ($this->getItems() as $item) {
            if ($item->getProductType() == 'undefined') {
                continue;
            }
            $product    = $item->getProduct();
            $option     = $item->getOptionByCode('product_type');
            if ($option) {
                $product = $option->getProduct();
            }

            if ($item->getStoreId() != $this->_storeManager->getStore()->getId()
                && !$item->getRedirectUrl()
                && !$product->isVisibleInSiteVisibility())
            {
                $products[$product->getId()] = $item->getStoreId();
            }
        }

        if ($products) {
            $products = $this->_catalogUrlResource->getRewriteByProductStore($products);
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
                    $object = new \Magento\Object($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($object);
                }
            }
        }
    }

    /**
     * Get item row html
     *
     * @param \Magento\Sales\Model\Quote\Item $item
     * @return string
     */
    public function getItemHtml(\Magento\Sales\Model\Quote\Item $item)
    {
        /** @var $renderer \Magento\Checkout\Block\Cart\Item\Renderer */
        $renderer = $this->getItemRenderer($item->getProductType())->setQtyMode(false);
        if ($item->getProductType() == 'undefined') {
            $renderer->overrideProductThumbnail($this->helper('Magento\Catalog\Helper\Image')->init($item, 'thumbnail'));
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
     * @param \Magento\Sales\Model\Quote\Item $item
     * @return bool
     */
    public function showItemLink(\Magento\Sales\Model\Quote\Item $item)
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
        if ($this->_cart->getFailedItems()) {
            $html = parent::_toHtml();
        } else {
            $html = '';
        }
        return $html;
    }
}
