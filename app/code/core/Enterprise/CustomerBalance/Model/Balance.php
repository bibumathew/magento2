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
 * @category   Enterprise
 * @package    Enterprise_CustomerBalance
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Enterprise_CustomerBalance_Model_Balance extends Mage_Core_Model_Abstract
{
	protected $_customer;

    protected function _construct()
    {
        $this->_init('enterprise_customerbalance/balance');
    }

    public function updateBalance()
    {
        if( abs($this->getDelta()) > 0 ) {
            $this->loadByCustomerWebsite($this->getCustomerId(), $this->getWebsiteId());
            try {
	            if( !$this->getId() ) {
	                $this->setBalance($this->getDelta())
	                     ->save();
	                $this->getHistoryModel()->addCreateEvent($this);
	            } else {
	                $newBalance = $this->getBalance() + $this->getDelta();
	                $this->setBalance($newBalance)
	                     ->save();
	                $this->getHistoryModel()->addUpdateEvent($this);
	            	
	            }
	            $this->_sendNotice();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    public function loadByCustomerWebsite($customerId, $websiteId)
    {
        $this->getResource()->loadByCustomerWebsite($this, $customerId, $websiteId);
        return $this;
    }

    public function getTotal($customerId)
    {
        if( (bool) Mage::getStoreConfig('customer/account_share/scope') ) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            return $this->getResource()->getTotal($customerId, $customer->getWebsiteId());
        }
        return $this->getResource()->getTotal($customerId);
    }

    public function getHistoryModel()
    {
        return Mage::getModel('enterprise_customerbalance/balance_history');
    }
    
    protected function _sendNotice()
    {
    	if( !$this->getEmailNotify() ) {
    		return $this;
    	}

    	Mage::getModel('core/email_template')
            ->setDesignConfig(array('store'=>$this->getEmailStoreId()))
            ->sendTransactional(
                Mage::getStoreConfig('customer/enterprise_customerbalance_email/template'),
                Mage::getStoreConfig('customer/enterprise_customerbalance_email/identity'),
                $this->_getCustomer()->getEmail(),
                $this->_getCustomer()->getName(),
                array('balance' => $this->getBalance(),
                      'name' => $this->_getCustomer()->getName())
            );
    	return $this;
    }
    
    protected function _getCustomer()
    {
    	if( $this->_customer ) {
    		return $this->_customer;
    	}
    	
    	if( !$this->getCustomerId() ) {
    		return false;
    	}
    	
    	$this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
    	return $this->_customer;
    }
}