<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GiftCardAccount
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_GiftCardAccount_Block_Adminhtml_Giftcardaccount_Edit_Form
    extends Magento_Backend_Block_Widget_Form_Generic
{
    protected function _prepareForm()
    {
        /** @var Magento_Data_Form $form */
        $form = $this->_formFactory->create(array(
            'attributes' => array(
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
            ))
        );

        $giftcardaccount = $this->_coreRegistry->registry('current_giftcardaccount');

        if ($giftcardaccount->getId()) {
            $form->addField('giftcardaccount_id', 'hidden', array(
                'name' => 'giftcardaccount_id',
            ));
            $form->setValues($giftcardaccount->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
