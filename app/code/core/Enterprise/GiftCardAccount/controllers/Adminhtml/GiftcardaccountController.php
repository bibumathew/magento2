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
 * @package     Enterprise_GiftCardAccount
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

class Enterprise_GiftCardAccount_Adminhtml_GiftcardaccountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Default action
     */
    public function indexAction()
    {
        $usage = Mage::getModel('enterprise_giftcardaccount/pool')->getPoolUsageInfo();

        $function = 'addNotice';
        if ($usage->getPercent() == 100) {
            $function = 'addError';
        }

        Mage::getSingleton('adminhtml/session')->$function(
            Mage::helper('enterprise_giftcardaccount')->__(
                'Code Pool used: <b>%.2f%%</b> (free <b>%d</b> of <b>%d</b> total). Generate new code pool <a href="%s">here</a>.',
                $usage->getPercent(),
                $usage->getFree(),
                $usage->getTotal(),
                Mage::getSingleton('adminhtml/url')->getUrl('*/*/generate'))
        );

        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcardaccount');
        $this->renderLayout();
    }


    /**
     * Create new Gift Card Account
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit GiftCardAccount
     */
    public function editAction()
    {

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $this->_initGca();
        $model = Mage::registry('current_giftcardaccount');

        if (!$model->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('enterprise_giftcardaccount')->__('This Gift Card Account no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->loadLayout()
            ->_addBreadcrumb($id ? Mage::helper('enterprise_giftcardaccount')->__('Edit Gift Card Account') : Mage::helper('enterprise_giftcardaccount')->__('New Gift Card Account'),
                             $id ? Mage::helper('enterprise_giftcardaccount')->__('Edit Gift Card Account') : Mage::helper('enterprise_giftcardaccount')->__('New Gift Card Account'))
            ->_addContent($this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit')->setData('form_action_url', $this->getUrl('*/*/save')))
            ->_addLeft($this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tabs'))
            ->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            $data = $this->_filterPostData($data);
            // init model and set data
            $model = Mage::getModel('enterprise_giftcardaccount/giftcardaccount');
            if (!empty($data)) {
                $model->addData($data);
            }

            // try to save it
            try {
                // save the data
                $model->save();
                $sending = null;
                if ($model->getAction()) {
                    try {
                        $model->sendEmail();
                        $sending = $model->getEmailSent();
                    } catch (Exception $e) {
                        $sending = false;
                    }
                }

                if (!is_null($sending)) {
                    if ($sending) {
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_giftcardaccount')->__('Gift Card Account was successfully saved & sent'));
                    } else {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('enterprise_giftcardaccount')->__('Gift Card Account was successfully saved, but email was not sent'));
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_giftcardaccount')->__('Gift Card Account was successfully saved'));
                }

                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init model and delete
                $model = Mage::getModel('enterprise_giftcardaccount/giftcardaccount');
                $model->load($id);
                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_giftcardaccount')->__('Gift Card Account was successfully deleted'));
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('enterprise_giftcardaccount')->__('Unable to find a Gift Card Account to delete'));
        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Render GCA grid
     */
    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_grid', 'giftcardaccount.grid')
                ->toHtml()
        );
    }

    /**
     * Generate code pool
     */
    public function generateAction()
    {
        try {
            Mage::getModel('enterprise_giftcardaccount/pool')->generatePool();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('enterprise_giftcardaccount')->__('New code pool was generated successfully.'));
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addException($e, Mage::helper('enterprise_giftcardaccount')->__('Unable to generate new code pool.'));
        }
        $this->_redirectReferer('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/giftcardaccount');
    }

    /**
     * Render GCA history grid
     */
    public function gridHistoryAction()
    {
        $this->_initGca();
        $id = (int)$this->getRequest()->getParam('id');
        if ($id && !Mage::registry('current_giftcardaccount')->getId()) {
            return;
        }

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_edit_tab_history')->toHtml()
        );
    }

    /**
     * Load GCA from request
     *
     * @param string $idFieldName
     */
    protected function _initGca($idFieldName = 'id')
    {
        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = Mage::getModel('enterprise_giftcardaccount/giftcardaccount');
        if ($id) {
            $model->load($id);
        }
        Mage::register('current_giftcardaccount', $model);
    }

    /**
     * Export GCA grid to MSXML
     */
    public function exportMsxmlAction()
    {
        $this->_prepareDownloadResponse('giftcardaccounts.xml',
            $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_grid')
                ->getExcel($this->__('Gift Card Accounts'))
        );
    }

    /**
     * Export GCA grid to CSV
     */
    public function exportCsvAction()
    {
        $this->_prepareDownloadResponse('giftcardaccounts.csv',
            $this->getLayout()->createBlock('enterprise_giftcardaccount/adminhtml_giftcardaccount_grid')->getCsv()
        );
    }

    /**
     * Delete gift card accounts specified using grid massaction
     */
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('giftcardaccount');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select gift card account(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('enterprise_giftcardaccount/giftcardaccount')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($ids))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
                'date_format' => Mage::app()->getLocale()->getDateFormat()
            ));

        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
                'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
            ));

        if (isset($data['date_expires']) && $data['date_expires']) {
            $data['date_expires'] = $filterInput->filter($data['date_expires']);
            $data['date_expires'] = $filterInternal->filter($data['date_expires']);
        }

        return $data;
    }
}
