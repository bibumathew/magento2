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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog composite product configuration controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Customer_Wishlist_Product_Composite_WishlistController extends Mage_Adminhtml_Controller_Action
{
     /**
     * Wishlist we're working with
     *
     * @var Mage_Wishlist_Model_Wishlist
     */
    protected $_wishlist = null;

    /**
     * Wishlist item we're working with
     *
     * @var Mage_Wishlist_Model_Wishlist
     */
    protected $_wishlistItem = null;

    /*
     * Loads wishlist and wishlist item
     *
     * @return Mage_Adminhtml_Customer_Wishlist_Product_Composite_WishlistController
     */
    protected function _initData()
    {
        $wishlistItemId = (int) $this->getRequest()->getParam('id');
        if (!$wishlistItemId) {
            Mage::throwException($this->__('No wishlist item id defined.'));
        }

        /* @var $wishlistItem Mage_Wishlist_Model_Item */
        $wishlistItem = Mage::getModel('wishlist/item')
            ->load($wishlistItemId);

        if (!$wishlistItem->getWishlistId()) {
            Mage::throwException($this->__('Wishlist item is not loaded.'));
        }

        $optionCollection = Mage::getModel('wishlist/item_option')->getCollection()
            ->addItemFilter(array($wishlistItemId));
        $wishlistItem->setOptions($optionCollection->getOptionsByItem($wishlistItem));

        $this->_wishlist = Mage::getModel('wishlist/wishlist')
            ->load($wishlistItem->getWishlistId());

        $this->_wishlistItem = $wishlistItem;

        return $this;
    }

    /*
     * Ajax handler to response configuration fieldset of composite product in customer's wishlist
     *
     * @return Mage_Adminhtml_Customer_Wishlist_Product_Composite_WishlistController
     */
    public function configureAction()
    {
        $configureResult = new Varien_Object();
        try {
            $this->_initData();

            $configureResult->setProductId($this->_wishlistItem->getProductId());
            $configureResult->setBuyRequest($this->_wishlistItem->getBuyRequest());
            $configureResult->setCurrentStoreId($this->_wishlistItem->getStoreId());
            $configureResult->setCurrentCustomerId($this->_wishlist->getCustomerId());

            $configureResult->setOk(true);
        } catch (Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        /* @var $helper Mage_Adminhtml_Helper_Catalog_Product_Composite */
        $helper = Mage::helper('adminhtml/catalog_product_composite');
        $helper->renderConfigureResult($this, $configureResult);

        return $this;
    }

    /*
     * IFrame handler for submitted configuration for wishlist item
     *
     * @return Mage_Adminhtml_Customer_Wishlist_Product_Composite_WishlistController
     */
    public function updateAction()
    {
        // Update wishlist item
        $updateResult = new Varien_Object();
        try {
            $this->_initData();

            $buyRequest = new Varien_Object($this->getRequest()->getParams());
            $this->_wishlist->updateItem($this->_wishlistItem->getId(), $buyRequest);

            $updateResult->setOk(true);
        } catch (Exception $e) {
            $updateResult->setError(true);
            $updateResult->setMessage($e->getMessage());
        }

        /* @var $helper Mage_Adminhtml_Helper_Catalog_Product_Composite */
        $helper = Mage::helper('adminhtml/catalog_product_composite');
        $helper->renderUpdateResult($this, $updateResult);

        return $this;
    }
}
