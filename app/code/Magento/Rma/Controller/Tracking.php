<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Rma_Controller_Tracking extends Magento_Core_Controller_Front_Action
{
    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Core_Controller_Varien_Action_Context $context
     * @param Magento_Core_Model_Registry $coreRegistry
     */
    public function __construct(
        Magento_Core_Controller_Varien_Action_Context $context,
        Magento_Core_Model_Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Popup action
     * Shows tracking info if it's present, otherwise redirects to 404
     *
     * @return null
     */
    public function popupAction()
    {
        $shippingInfoModel = Mage::getModel('Magento_Rma_Model_Shipping_Info')
            ->loadByHash($this->getRequest()->getParam('hash'));

        $this->_coreRegistry->register('rma_current_shipping', $shippingInfoModel);
        if (count($shippingInfoModel->getTrackingInfo()) == 0) {
            $this->norouteAction();
            return;
        }
        $this->loadLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(__('Tracking Information'));
        }
        $this->renderLayout();
    }

    /**
     * Popup package action
     * Shows package info if it's present, otherwise redirects to 404
     *
     * @return null
     */
    public function packageAction()
    {
        $shippingInfoModel = Mage::getModel('Magento_Rma_Model_Shipping_Info')
            ->loadPackage($this->getRequest()->getParam('hash'));

        $this->_coreRegistry->register('rma_package_shipping', $shippingInfoModel);
        if (!$shippingInfoModel->getPackages()) {
            $this->norouteAction();
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check order view availability
     *
     * @param   Magento_Rma_Model_Rma $rma
     * @return  bool
     */
    protected function _canViewRma($rma)
    {
        if (!Mage::getSingleton('Magento_Customer_Model_Session')->isLoggedIn()) {
            $currentOrder = $this->_coreRegistry->registry('current_order');
            if ($rma->getOrderId() && ($rma->getOrderId() === $currentOrder->getId())) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * Try to load valid rma by entity_id and register it
     *
     * @param int $entityId
     * @return bool
     */
    protected function _loadValidRma($entityId = null)
    {
        if (!Mage::getSingleton('Magento_Customer_Model_Session')->isLoggedIn()
            && !$this->_objectManager->get('Magento_Sales_Helper_Guest')->loadValidOrder()
        ) {
            return;
        }

        if (null === $entityId) {
            $entityId = (int) $this->getRequest()->getParam('entity_id');
        }

        if (!$entityId) {
            $this->_forward('noRoute');
            return false;
        }

        $rma = Mage::getModel('Magento_Rma_Model_Rma')->load($entityId);

        if ($this->_canViewRma($rma)) {
            $this->_coreRegistry->register('current_rma', $rma);
            return true;
        } else {
            $this->_redirect('*/*/returns');
        }
        return false;
    }

    /**
     * Print label for one specific shipment
     */
    public function printLabelAction()
    {
        try {
            $data = $this->_objectManager->get('Magento_Rma_Helper_Data')
                ->decodeTrackingHash($this->getRequest()->getParam('hash'));

            $rmaIncrementId = '';
            if ($data['key'] == 'rma_id') {
                $this->_loadValidRma($data['id']);
                if ($this->_coreRegistry->registry('current_rma')) {
                    $rmaIncrementId = $this->_coreRegistry->registry('current_rma')->getIncrementId();
                }
            }
            $model = Mage::getModel('Magento_Rma_Model_Shipping_Info')
                ->loadPackage($this->getRequest()->getParam('hash'));

            $shipping = Mage::getModel('Magento_Rma_Model_Shipping');
            $labelContent = $model->getShippingLabel();
            if ($labelContent) {
                $pdfContent = null;
                if (stripos($labelContent, '%PDF-') !== false) {
                    $pdfContent = $labelContent;
                } else {
                    $pdf = new Zend_Pdf();
                    $page = $shipping->createPdfPageFromImageString($labelContent);
                    if (!$page) {
                        $this->_getSession()->addError(__("We don't recognize or support the file extension in shipment %1.", $shipping->getIncrementId()));
                    }
                    $pdf->pages[] = $page;
                    $pdfContent = $pdf->render();
                }

                return $this->_prepareDownloadResponse(
                    'ShippingLabel(' . $rmaIncrementId . ').pdf',
                    $pdfContent,
                    'application/pdf'
                );
            }
        } catch (Magento_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()
                ->addError(__('Something went wrong creating a shipping label.'));
        }
        $this->norouteAction();
        return;
    }

    /**
     * Create pdf document with information about packages
     *
     */
    public function packagePrintAction()
    {
        $data = $this->_objectManager->get('Magento_Rma_Helper_Data')->decodeTrackingHash($this->getRequest()->getParam('hash'));

        if ($data['key'] == 'rma_id') {
            $this->_loadValidRma($data['id']);
        }
        $model = Mage::getModel('Magento_Rma_Model_Shipping_Info')
            ->loadPackage($this->getRequest()->getParam('hash'));

        if ($model) {
            $pdf = Mage::getModel('Magento_Sales_Model_Order_Pdf_Shipment_Packaging')
                    ->setPackageShippingBlock(
                        Mage::getBlockSingleton('Magento_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Shippingmethod')
                    )
                    ->getPdf($model)
            ;

            $this->_prepareDownloadResponse(
                'packingslip'.Mage::getSingleton('Magento_Core_Model_Date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                'application/pdf'
            );
        }
    }
}
