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
 * @package    Enterprise_GiftCardAccount
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Enterprise_GiftCardAccount_Block_Manage_Giftcardaccount_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'manage_giftcardaccount';
        $this->_blockGroup = 'enterprise_giftcardaccount';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('enterprise_giftcardaccount')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('enterprise_giftcardaccount')->__('Delete'));

    }

    public function getGiftcardaccountId()
    {
        return Mage::registry('current_giftcardaccount')->getId();
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_giftcardaccount')->getId()) {
            return Mage::helper('enterprise_giftcardaccount')->__('Edit Gift Card Account: %s', $this->htmlEscape(Mage::registry('current_giftcardaccount')->getCode()));
        }
        else {
            return Mage::helper('enterprise_giftcardaccount')->__('New Gift Card Account');
        }
    }

}