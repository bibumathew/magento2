<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Invitation reports controller
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */

class Enterprise_Invitation_Adminhtml_Report_InvitationController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init action breadcrumbs
     *
     * @return Enterprise_Invitation_Adminhtml_Report_InvitationController
     */
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('Mage_Reports_Helper_Data')->__('Reports'), Mage::helper('Mage_Reports_Helper_Data')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('Enterprise_Invitation_Helper_Data')->__('Invitations'), Mage::helper('Enterprise_Invitation_Helper_Data')->__('Invitations'));
        return $this;
    }

    /**
     * General report action
     */
    public function indexAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Invitations'))
             ->_title($this->__('General'));

        $this->_initAction()
            ->_setActiveMenu('Enterprise_Invitation::report_enterprise_invitation_general')
            ->_addBreadcrumb(Mage::helper('Enterprise_Invitation_Helper_Data')->__('General Report'),
            Mage::helper('Enterprise_Invitation_Helper_Data')->__('General Report'))
            ->renderLayout();
    }

    /**
     * Export invitation general report grid to CSV format
     */
    public function exportCsvAction()
    {
        $this->loadLayout();
        $fileName   = 'invitation_general.csv';
        $exportBlock = $this->getLayout()->getChildBlock('report.general.customer','grid.export');

        $this->_prepareDownloadResponse($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export invitation general report grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $this->loadLayout();
        $fileName   = 'invitation_general.xml';
        /** @var Mage_Backend_Block_Widget_Grid_ExportInterface $exportBlock */

        $exportBlock = $this->getLayout()->getChildBlock('report.general.customer','grid.export');

        $this->_prepareDownloadResponse($fileName, $exportBlock->getExcelFile($fileName));
    }

    /**
     * Report by customers action
     */
    public function customerAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Invitations'))
             ->_title($this->__('Customers'));

        $this->_initAction()
            ->_setActiveMenu('Enterprise_Invitation::report_enterprise_invitation_customer')
            ->_addBreadcrumb(Mage::helper('Enterprise_Invitation_Helper_Data')->__('Invitation Report by Customers'),
            Mage::helper('Enterprise_Invitation_Helper_Data')->__('Invitation Report by Customers'))
            ->renderLayout();
    }

    /**
     * Export invitation customer report grid to CSV format
     */
    public function exportCustomerCsvAction()
    {
        $this->loadLayout();
        $fileName = 'invitation_customer.csv';
        /** @var Mage_Backend_Block_Widget_Grid_ExportInterface $exportBlock */
        $exportBlock = $this->getLayout()->getChildBlock('report.invitation.customer','grid.export');
        $this->_prepareDownloadResponse($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export invitation customer report grid to Excel XML format
     */
    public function exportCustomerExcelAction()
    {
        $this->loadLayout();
        /** @var Mage_Backend_Block_Widget_Grid_ExportInterface $exportBlock */
        $exportBlock = $this->getLayout()->getChildBlock('report.invitation.customer','grid.export');
        $fileName = 'invitation_customer.xml';
        $this->_prepareDownloadResponse($fileName, $exportBlock->getExcelFile($fileName));
    }

    /**
     * Report by order action
     */
    public function orderAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Invitations'))
             ->_title($this->__('Order Conversion Rate'));

        $this->_initAction()->_setActiveMenu('Enterprise_Invitation::report_enterprise_invitation_order')
            ->_addBreadcrumb(Mage::helper('Enterprise_Invitation_Helper_Data')->__('Invitation Report by Customers'),
            Mage::helper('Enterprise_Invitation_Helper_Data')->__('Invitation Report by Order Conversion Rate'))
            ->renderLayout();
    }

    /**
     * Export invitation order report grid to CSV format
     */
    public function exportOrderCsvAction()
    {
        $this->loadLayout();
        $fileName = 'invitation_order.csv';
        $exportBlock = $this->getLayout()->getChildBlock('adminhthtml.report.invitation.order.grid', 'grid.export');
        $this->_prepareDownloadResponse($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export invitation order report grid to Excel XML format
     */
    public function exportOrderExcelAction()
    {
        $this->loadLayout();
        $fileName = 'invitation_order.xml';
        $exportBlock = $this->getLayout()->getChildBlock('adminhthtml.report.invitation.order.grid', 'grid.export');
        $this->_prepareDownloadResponse($fileName, $exportBlock->getExcelFile($fileName));
    }

    /**
     * Acl admin user check
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Enterprise_Invitation_Model_Config')->isEnabled() &&
               Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Enterprise_Invitation::report_enterprise_invitation');
    }
}
