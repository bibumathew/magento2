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
 * @package     Mage_PaypalUk
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Paypal shortcut link
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_PaypalUk_Block_Link_Shortcut extends Mage_Core_Block_Template
{
    public function getCheckoutUrl()
    {
        return $this->getUrl('paypaluk/express/shortcut', array('_secure'=>true));
    }

    public function getImageUrl()
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        if (strpos('en_GB', $locale)===false) {
            $locale = 'en_US';
        }

        return 'https://www.paypal.com/'.$locale.'/i/btn/btn_xpressCheckout.gif';
    }

    /**
     * Render block html output
     * @return string
     */
    public function _toHtml()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $paypalUkModel = Mage::getModel('paypaluk/express');
        if ($paypalUkModel->isAvailable($quote) && $paypalUkModel->isVisibleOnCartPage()
            && $quote->validateMinimumAmount()) {
            return parent::_toHtml();
        }
        return '';
    }
}
