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
 * @package     Enterprise_Reward
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Reward admin rate controller
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Adminhtml_Reward_RateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check if module functionality enabled
     *
     * @return Enterprise_Reward_Adminhtml_Reward_RateController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('enterprise_reward')->isEnabled() && $this->getRequest()->getActionName() != 'noroute') {
            $this->_forward('noroute');
        }
        return $this;
    }

    /**
     * Initialize layout, breadcrumbs
     *
     * @return Enterprise_Reward_Adminhtml_Reward_RateController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('customer/reward_rates')
            ->_addBreadcrumb(Mage::helper('enterprise_reward')->__('Customers'),
                Mage::helper('enterprise_reward')->__('Customers'))
            ->_addBreadcrumb(Mage::helper('enterprise_reward')->__('Manage Reward Exchange Rates'),
                Mage::helper('enterprise_reward')->__('Manage Reward Exchange Rates'));
        return $this;
    }

    /**
     * Initialize rate object
     *
     * @return Enterprise_Reward_Model_Reward_Rate
     */
    protected function _initRate()
    {
        $rateId = $this->getRequest()->getParam('rate_id', 0);
        $rate = Mage::getModel('enterprise_reward/reward_rate');
        if ($rateId) {
            $rate->load($rateId);
        }
        Mage::register('current_reward_rate', $rate);
        return $rate;
    }

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * New Action.
     * Forward to Edit Action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Action
     */
    public function editAction()
    {
        $this->_initRate();
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Save Action
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('rate');

        if ($data) {
            $rate = $this->_initRate();

            if ($this->getRequest()->getParam('rate_id') && ! $rate->getId()) {
                $this->_getSession()->addError($this->__('This Reward Exchange Rate does not exists.'));
                return $this->_redirect('*/*/');
            }

            $rate->addData($data);

            try {
                $rate->save();
                $this->_getSession()->addSuccess(Mage::helper('enterprise_reward')->__('Rate saved successfully.'));
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('Can not save Rate.'));
                return $this->_redirect('*/*/edit', array('rate_id' => $rate->getId(), '_current' => true));
            }
        }

        return $this->_redirect('*/*/');
    }

    /**
     * Delete Action
     */
    public function deleteAction()
    {
        $rate = $this->_initRate();
        if ($rate->getId()) {
            try {
                $rate->delete();
                $this->_getSession()->addSuccess(Mage::helper('enterprise_reward')->__('Rate deleted successfully.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/*', array('_current' => true));
                return;
            }
        }

        return $this->_redirect('*/*/');
    }

    /**
     * Validate Action
     *
     */
    public function validateAction()
    {
        $response = new Varien_Object(array('error' => false));
        $post     = $this->getRequest()->getParam('rate');
        $message  = null;

        if (!isset($post['customer_group_id'])
            || !isset($post['website_id'])
            || !isset($post['direction'])
            || !isset($post['value'])
            || !isset($post['equal_value'])) {
            $message = $this->__('Invalid form data');
        } elseif ($post['direction'] == Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY
                  && ((int) $post['value'] <= 0 || (float) $post['equal_value'] <= 0)) {
              if ((int) $post['value'] <= 0) {
                  $message = $this->__('Please enter positive integer number to left Rate field');
              } else {
                  $message = $this->__('Please enter positive number to right Rate field');
              }
        } elseif ($post['direction'] == Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS
                  && ((float) $post['value'] <= 0 || (int) $post['equal_value'] <= 0)) {
              if ((int) $post['equal_value'] <= 0) {
                  $message = $this->__('Please enter positive integer number to right Rate field');
              } else {
                  $message = $this->__('Please enter positive number to left Rate field');
              }
        } else {
            $rate       = $this->_initRate();
            $initRateId = $rate->getId();
            $rateId     = $rate->fetch($post['customer_group_id'], $post['website_id'], $post['direction'])
                               ->getId();

            if ($rateId && ($initRateId != $rateId)) {
                $message = $this->__('Rate with same Website, Custormer Group and Direction or covering Rate already exists.');
            }
        }

        if ($message) {
            $this->_getSession()->addError($message);
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Acl check for admin
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('enterprise_reward/rates');
    }
}
