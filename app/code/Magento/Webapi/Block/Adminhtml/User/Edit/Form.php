<?php
/**
 * Web API user edit form.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 *
 * @method Magento_Webapi_Block_Adminhtml_User_Edit setApiUser() setApiUser(Magento_Webapi_Model_Acl_User $user)
 * @method Magento_Webapi_Model_Acl_User getApiUser() getApiUser()
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Magento_Webapi_Block_Adminhtml_User_Edit_Form extends Magento_Backend_Block_Widget_Form_Generic
{
    /**
     * Prepare Form.
     *
     * @return Magento_Webapi_Block_Adminhtml_User_Edit_Form
     */
    protected function _prepareForm()
    {
        /** @var Magento_Data_Form $form */
        $form = $this->_formFactory->create();
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
