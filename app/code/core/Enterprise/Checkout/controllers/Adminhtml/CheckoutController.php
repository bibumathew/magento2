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
 * @package     Enterprise_Checkout
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Admin Checkout index controller
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Checkout_Adminhtml_CheckoutController extends Enterprise_Enterprise_Controller_Adminhtml_Action
{
    /**
     * Flag that indicates whether page must be reloaded with correct params or not
     *
     * @var bool
     */
    protected $_redirectFlag = false;


    /**
     * Return Checkout model as singleton
     *
     * @return Enterprise_Checkout_Model_Cart
     */
    public function getCartModel()
    {
        return Mage::getSingleton('enterprise_checkout/cart');
    }
    
    /**
     * Init store based on quote and customer sharing options
     * Store customer, store and quote to registry
     *
     * @throws Mage_Core_Exception
     * @return Enterprise_Checkout_Adminhtml_CheckoutController
     */
    protected function _initAction()
    {
        $customerId = $this->getRequest()->getParam('customer');
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if (!$customer->getId()) {
            Mage::throwException(Mage::helper('enterprise_checkout')->__('Customer not found'));
        }

        $cart = $this->getCartModel();
        $cart->setCustomer($customer);

        $storeId = $this->getRequest()->getParam('store');

        if ($storeId === null || Mage::app()->getStore($storeId)->isAdmin()) {

            if ($storeId = $cart->getPreferredStoreId()) {
                // Redirect to preferred store view
                if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'url' => $this->getUrl('*/*/index', array('store' => $storeId, 'customer' => $customerId))
                    )));
                } else {
                    $this->_redirect('*/*/index', array('store' => $storeId, 'customer' => $customerId));
                }
                $this->_redirectFlag = true;
                return $this;
            } else {
                Mage::throwException(Mage::helper('enterprise_checkout')->__('Store not found'));
            }
        }

        // Assign store to quote when it will be saved
        if(!$cart->getQuote()->getId()) {
            $cart->getQuote()->setStore(Mage::app()->getStore($storeId));
        }

        Mage::register('checkout_current_quote', $cart->getQuote());
        Mage::register('checkout_current_customer', $customer);
        Mage::register('checkout_current_store', Mage::app()->getStore($storeId));

        return $this;
    }

    /**
     * Empty page for final errors occured
     */
    public function errorAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Manage shopping cart layout
     */
    public function indexAction()
    {
        try {
            $this->_initAction();
            if ($this->_redirectFlag) {
                return;
            }
            $this->loadLayout();
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(
                Mage::helper('enterprise_checkout')->__('An error was occured. Please, see error log for details.')
            );
        }
        $this->_redirect('*/*/error');
    }


    /**
     * Quote items grid ajax callback
     */
    public function cartAction()
    {
        try {
            $this->_initAction();
            if ($this->_redirectFlag) {
                return;
            }
            $this->loadLayout();
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_processCoreException($e);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Add products to quote, ajax
     */
    public function addToCartAction()
    {
        try {
            $this->_checkIsAllowedModification();
            $this->_initAction();
            if ($this->_redirectFlag) {
                return;
            }

            // Add new products
            $products = $this->getRequest()->getPost('add_product');
            $cart = $this->getCartModel();
            if (is_array($products)) {
                foreach ($products as $productId) {
                    $cart->addProduct($productId);
                }
            }

            // Reorder products
            $reordered = $this->getRequest()->getPost('add_order_item', array());
            foreach ($reordered as $orderItemId) {
                $orderItem = Mage::getModel('sales/order_item')->load($orderItemId);
                $cart->reorderItem($orderItem);
            }

        } catch (Mage_Core_Exception $e) {
            $this->_processCoreException($e);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Mass update quote items, ajax
     */
    public function updateItemsAction()
    {
        try {
            $this->_checkIsAllowedModification();
            $this->_initAction();
            if ($this->_redirectFlag) {
                return;
            }
            if ($items = $this->getRequest()->getPost('item', array())) {
                $this->getCartModel()->updateQuoteItems($items);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_processCoreException($e);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Apply/cancel coupon code in quote, ajax
     */
    public function applyCouponAction()
    {
        try {
            $this->_checkIsAllowedModification();
            $this->_initAction();
            if ($this->_redirectFlag) {
                return;
            }
            $code = $this->getRequest()->getPost('code', '');
            $quote = Mage::registry('checkout_current_quote');
            $quote->setCouponCode($code)
                ->collectTotals()
                ->save();

            $this->loadLayout();
            if (!$quote->getCouponCode()) {
                $this->getLayout()
                    ->getBlock('form_coupon')
                    ->setInvalidCouponCode($code);
            }
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_processCoreException($e);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Common action for accordion grids, ajax
     */
    public function accordionAction()
    {
        try {
            $this->_initAction();
            if ($this->_redirectFlag) {
                return;
            }
            $this->loadLayout();
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_processCoreException($e);
        } catch (Exception $e) {
            $this->_processException($e);
        }
    }

    /**
     * Redirect to order creation page based on current quote
     */
    public function createOrderAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            Mage::throwException(Mage::helper('enterprise_checkout')->__('Access denied.'));
        }
        try {
            $this->_initAction();
            if ($this->_redirectFlag) {
                return;
            }
            $activeQuote = $this->getCartModel()->getQuote();
            $quote = $this->getCartModel()->copyQuote($activeQuote);
            if ($quote->getId()) {
                $session = Mage::getSingleton('adminhtml/sales_order_create')->getSession();
                $session->setQuoteId($quote->getId())
                   ->setStoreId($quote->getStoreId())
                   ->setCustomerId($quote->getCustomerId());
                
            }
            $this->_redirect('*/sales_order_create', array(
                'customer_id' => Mage::registry('checkout_current_customer')->getId(),
                'store_id' => Mage::registry('checkout_current_store')->getId(),
            ));
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(
                Mage::helper('enterprise_checkout')->__('An error was occured. Please, see error log for details.')
            );
        }
        $this->_redirect('*/*/error');
    }

    /**
     * Catalog products accordion grid callback
     */
    public function productsAction()
    {
        $this->accordionAction();
    }

    /**
     * Wishlist accordion grid callback
     */
    public function viewWishlistAction()
    {
        $this->accordionAction();
    }

    /**
     * Compared products accordion grid callback
     */
    public function viewComparedAction()
    {
        $this->accordionAction();
    }

    /**
     * Recently compared products accordion grid callback
     */
    public function viewRecentlyComparedAction()
    {
        $this->accordionAction();
    }

    /**
     * Recently viewed products accordion grid callback
     */
    public function viewRecentlyViewedAction()
    {
        $this->accordionAction();
    }

    /**
     * Last ordered items accordion grid callback
     */
    public function viewOrderedAction()
    {
        $this->accordionAction();
    }

    /**
     * Process Core exception
     *
     * @param Exception $e
     * @return return
     */
    protected function _processCoreException(Exception $e)
    {
        $result = array('error' => $e->getMessage());
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Process Generic exception
     *
     * @param Exception $e
     * @return return
     */
    protected function _processException(Exception $e)
    {
        Mage::logException($e);
        $result = array(
            'error' => Mage::helper('enterprise_checkout')->__('An error was occured. Please, see error log for details.')
        );
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    /**
     * Acl check for quote modifications
     *
     * @return boolean
     */
    protected function _checkIsAllowedModification()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_checkout/update')) {
            Mage::throwException(Mage::helper('enterprise_checkout')->__('Access denied.'));
        }
    }
    
    /**
     * Acl check for admin
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_checkout/view')
            || Mage::getSingleton('admin/session')->isAllowed('sales/enterprise_checkout/update');
    }
}
