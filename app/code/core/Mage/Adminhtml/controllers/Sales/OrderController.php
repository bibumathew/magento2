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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Michael Bessolov <michael@varien.com>
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Orders'), $this->__('Orders'));
        return $this;
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    /**
     * Orders grid
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order'))
            ->renderLayout();
    }

    /**
     * Order grid
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_grid')->toHtml()
        );
    }

    /**
     * View order detale
     */
    public function viewAction()
    {
        if ($order = $this->_initOrder()) {
            $this->_initAction()
                ->_addBreadcrumb($this->__('View Order'), $this->__('View Order'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_view'))
                ->_addLeft($this->getLayout()->createBlock('adminhtml/sales_order_view_tabs'))
                ->renderLayout();
        }
    }

    /**
     * Cancel order
     */
    public function cancelAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->cancel()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('Order was successfully cancelled.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Order was not cancelled.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Hold order
     */
    public function holdAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->hold()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('Order was successfully holded.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Order was not holded.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Unhold order
     */
    public function unholdAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->unhold()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('Order was successfully unholded.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Order was not unholded.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Add order comment action
     */
    public function addCommentAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $order->addStatusToHistory($data['status'], $data['comment'], $notify);
                $comment = trim(strip_tags($data['comment']));

                if ($notify && $comment) {
                    Mage::getDesign()->setStore($order->getStoreId());
                    Mage::getDesign()->setArea('frontend');
                    $order->sendOrderUpdateEmail($comment);
                }
                $order->save();
                Mage::getDesign()->setArea('adminhtml');
                $response = $this->getLayout()->createBlock('adminhtml/sales_order_view_history')->toHtml();
            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Can nod add order history.')
                );
            }
            if (is_array($response)) {
                $response = Zend_Json::encode($response);
            }
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Generate invoices grid for ajax request
     */
    public function invoicesAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_invoices')->toHtml()
        );
    }

    /**
     * Generate shipments grid for ajax request
     */
    public function shipmentsAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_shipments')->toHtml()
        );
    }

    /**
     * Generate creditmemos grid for ajax request
     */
    public function creditmemosAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tab_creditmemos')->toHtml()
        );
    }

    /**
     * Cancel selected orders
     */
    public function massCancelAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $cancelAnyOrder = false;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canCancel()) {
                $order->cancel()
                    ->save();
                $cancelAnyOrder = true;
            }
        }
        if ($cancelAnyOrder) {
            $this->_getSession()->addSuccess($this->__('Orders was canceled'));
        }
        else {
            // selected orders is not available for cancel
        }
        $this->_redirect('*/*/');
    }

    /**
     * Hold selected orders
     */
    public function massHoldAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $holdAnyOrder = false;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canHold()) {
                $order->hold()
                    ->save();
                $holdAnyOrder = true;
            }
        }
        if ($holdAnyOrder) {
            $this->_getSession()->addSuccess($this->__('Orders was holded'));
        }
        else {
            // selected orders is not available for hold
        }
        $this->_redirect('*/*/');
    }

    /**
     * Unhold selected orders
     */
    public function massUnholdAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $unholdAnyOrder = false;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->canUnhold()) {
                $order->unhold()
                    ->save();
                $unholdAnyOrder = true;
            }
        }
        if ($unholdAnyOrder) {
            $this->_getSession()->addSuccess($this->__('Orders was unholded'));
        }
        else {
            // selected orders is not available for hold
        }
        $this->_redirect('*/*/');
    }

    /**
     * Change status for selected orders
     */
    public function massStatusAction()
    {

    }

    /**
     * Print documents for selected orders
     */
    public function massPrintAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $document = $this->getRequest()->getPost('document');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }

    public function pdfinvoicesAction(){
        //$this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_invoice')->toPdf();
       // );
    }
    
    public function pdfshipmentsAction(){
        //$this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/sales_shipment')->toPdf();
       // );
    }

}

