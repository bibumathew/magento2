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
 * @package     Mage_DirectPayment
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Direct payment iformation block
 *
 */
class Mage_DirectPayment_Block_Info extends Mage_Payment_Block_Info
{    
    const MULTISHIPPING_CONTROLLER_CODE = 'multishipping';
    const PAYMENT_METHOD_CODE = 'directpayment';
    
    protected $_formBlock;        
    
    /**
     * (non-PHPdoc)
     * @see app/code/core/Mage/Core/Block/Mage_Core_Block_Template#_toHtml()
     */
    protected function _toHtml()
    {
        if ($this->getForm()->getMethodCode() != self::PAYMENT_METHOD_CODE) {
            return;
        }
        $html = parent::_toHtml();       
        if ($this->_getRequestController() == self::MULTISHIPPING_CONTROLLER_CODE) {
            $this->getForm()->setTemplate('directpayment/form.phtml');            
            $html .= $this->getForm()->_toHtml();
        }        
        
        return $html;
    }
    
    
    /**
     * Set payment info
     * 
     * @return Mage_DirectPayment_Block_Info
     */
    public function setMethodInfo()
    {        
        $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();        
        $this->setInfo($payment);
        
        return $this;
    }
    
    /**
     * Get form instance
     * 
     * @return Mage_DirectPayment_Block_Form
     */
    public function getForm()
    {
        if (!$this->_formBlock) {
            $this->_formBlock = Mage::getSingleton('core/layout')
                                ->createBlock($this->getMethod()->getFormBlockType());
            $this->_formBlock->setMethod($this->getMethod());
        }
        
        return $this->_formBlock;
    }        
    
    /**
     * Get controller name
     * 
     * @return string
     */
    protected function _getRequestController()
    {
        return Mage::app()->getFrontController()
                            ->getRequest()
                            ->getControllerName();
    }
}