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
 * @author      Michael Bessolov <michael@varien.com>
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Adminhtml_Sales_InvoiceController extends Mage_Adminhtml_Controller_Action
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
     * @return Mage_Adminhtml_Sales_InvoiceController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Invoices'),$this->__('Invoices'));
        return $this;
    }

    /**
     * Invoices grid
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_invoice'))
            ->renderLayout();
    }

    /**
     * Invoice information page
     */
    public function viewAction()
    {
        if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                Mage::register('current_invoice', $invoice);
                $this->_initAction()
                    ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_invoice_view'))
                    ->renderLayout();
            }
        } else {
            $this->_forward('noRoute');
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/invoice');
    }

    public function pdfinvoicesAction(){
        $invoicesIds = $this->getRequest()->getPost();

        $invoicesIds = $invoicesIds['invoice_ids'];


        $invoices = Mage::getResourceModel('sales/order_invoice_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('in' => $invoicesIds))
            ->load();
        if (!isset($pdf)){
            $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
        } else {
            $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
            $pdf->pages = array_merge ($pdf->pages, $pages->pages);
        }

        header('Content-Disposition: attachment; filename="invoice.pdf"');
        header('Content-Type: application/pdf');
        echo $pdf->render();
    }
}
