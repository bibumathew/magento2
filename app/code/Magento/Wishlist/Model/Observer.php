<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Wishlist
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Shopping cart operation observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model;

class Observer extends \Magento\Core\Model\AbstractModel
{
    /**
     * Wishlist data
     *
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistData = null;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_wishlistData = $wishlistData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get customer wishlist model instance
     *
     * @param   int $customerId
     * @return  \Magento\Wishlist\Model\Wishlist || false
     */
    protected function _getWishlist($customerId)
    {
        if (!$customerId) {
            return false;
        }
        return \Mage::getModel('Magento\Wishlist\Model\Wishlist')->loadByCustomer($customerId, true);
    }

    /**
     * Check move quote item to wishlist request
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\Wishlist\Model\Observer
     */
    public function processCartUpdateBefore($observer)
    {
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo();
        $productIds = array();

        $wishlist = $this->_getWishlist($cart->getQuote()->getCustomerId());
        if (!$wishlist) {
            return $this;
        }

        /**
         * Collect product ids marked for move to wishlist
         */
        foreach ($data as $itemId => $itemInfo) {
            if (!empty($itemInfo['wishlist'])) {
                if ($item = $cart->getQuote()->getItemById($itemId)) {
                    $productId  = $item->getProductId();
                    $buyRequest = $item->getBuyRequest();

                    if (isset($itemInfo['qty']) && is_numeric($itemInfo['qty'])) {
                        $buyRequest->setQty($itemInfo['qty']);
                    }
                    $wishlist->addNewItem($productId, $buyRequest);

                    $productIds[] = $productId;
                    $cart->getQuote()->removeItem($itemId);
                }
            }
        }

        if (!empty($productIds)) {
            $wishlist->save();
            $this->_wishlistData->calculate();
        }
        return $this;
    }

    public function processAddToCart($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $sharedWishlist = \Mage::getSingleton('Magento\Checkout\Model\Session')->getSharedWishlist();
        $messages = \Mage::getSingleton('Magento\Checkout\Model\Session')->getWishlistPendingMessages();
        $urls = \Mage::getSingleton('Magento\Checkout\Model\Session')->getWishlistPendingUrls();
        $wishlistIds = \Mage::getSingleton('Magento\Checkout\Model\Session')->getWishlistIds();
        $singleWishlistId = \Mage::getSingleton('Magento\Checkout\Model\Session')->getSingleWishlistId();

        if ($singleWishlistId) {
            $wishlistIds = array($singleWishlistId);
        }

        if (count($wishlistIds) && $request->getParam('wishlist_next')) {
            $wishlistId = array_shift($wishlistIds);

            if (\Mage::getSingleton('Magento\Customer\Model\Session')->isLoggedIn()) {
                $wishlist = \Mage::getModel('Magento\Wishlist\Model\Wishlist')
                        ->loadByCustomer(\Mage::getSingleton('Magento\Customer\Model\Session')->getCustomer(), true);
            } else if ($sharedWishlist) {
                $wishlist = \Mage::getModel('Magento\Wishlist\Model\Wishlist')->loadByCode($sharedWishlist);
            } else {
                return;
            }


            $wishlist->getItemCollection()->load();

            foreach ($wishlist->getItemCollection() as $wishlistItem) {
                if ($wishlistItem->getId() == $wishlistId) {
                    $wishlistItem->delete();
                }
            }
            \Mage::getSingleton('Magento\Checkout\Model\Session')->setWishlistIds($wishlistIds);
            \Mage::getSingleton('Magento\Checkout\Model\Session')->setSingleWishlistId(null);
        }

        if ($request->getParam('wishlist_next') && count($urls)) {
            $url = array_shift($urls);
            $message = array_shift($messages);

            \Mage::getSingleton('Magento\Checkout\Model\Session')->setWishlistPendingUrls($urls);
            \Mage::getSingleton('Magento\Checkout\Model\Session')->setWishlistPendingMessages($messages);

            \Mage::getSingleton('Magento\Checkout\Model\Session')->addError($message);

            $observer->getEvent()->getResponse()->setRedirect($url);
            \Mage::getSingleton('Magento\Checkout\Model\Session')->setNoCartRedirect(true);
        }
    }

    /**
     * Customer login processing
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Wishlist\Model\Observer
     */
    public function customerLogin(\Magento\Event\Observer $observer)
    {
        $this->_wishlistData->calculate();

        return $this;
    }

    /**
     * Customer logout processing
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Wishlist\Model\Observer
     */
    public function customerLogout(\Magento\Event\Observer $observer)
    {
        \Mage::getSingleton('Magento\Customer\Model\Session')->setWishlistItemCount(0);

        return $this;
    }
}
