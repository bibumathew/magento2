<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright  {copyright}
 * @license    {license_link}
 */


/**
 * OAuth consumer edit form block
 *
 * @category   Magento
 * @package    Magento_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Oauth_Block_Adminhtml_Oauth_Consumer_Edit_Form extends Magento_Adminhtml_Block_Widget_Form
{
    /**
     * Consumer model
     *
     * @var Magento_Oauth_Model_Consumer
     */
    protected $_model;

    /**
     * Get consumer model
     *
     * @return Magento_Oauth_Model_Consumer
     */
    public function getModel()
    {
        if (null === $this->_model) {
            $this->_model = Mage::registry('current_consumer');
        }
        return $this->_model;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Magento_Oauth_Block_Adminhtml_Oauth_Consumer_Edit_Form
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();
        $form = new Magento_Data_Form(array(
            'id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('Magento_Oauth_Helper_Data')->__('Consumer Information'), 'class' => 'fieldset-wide'
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id', 'value' => $model->getId()));
        }
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Name'),
            'title'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Name'),
            'required'  => true,
            'value'     => $model->getName(),
        ));

        $fieldset->addField('key', 'text', array(
            'name'      => 'key',
            'label'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Key'),
            'title'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Key'),
            'disabled'  => true,
            'required'  => true,
            'value'     => $model->getKey(),
        ));

        $fieldset->addField('secret', 'text', array(
            'name'      => 'secret',
            'label'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Secret'),
            'title'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Secret'),
            'disabled'  => true,
            'required'  => true,
            'value'     => $model->getSecret(),
        ));

        $fieldset->addField('callback_url', 'text', array(
            'name'      => 'callback_url',
            'label'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Callback URL'),
            'title'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Callback URL'),
            'required'  => false,
            'value'     => $model->getCallbackUrl(),
            'class'     => 'validate-url',
        ));

        $fieldset->addField('rejected_callback_url', 'text', array(
            'name'      => 'rejected_callback_url',
            'label'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Rejected Callback URL'),
            'title'     => Mage::helper('Magento_Oauth_Helper_Data')->__('Rejected Callback URL'),
            'required'  => false,
            'value'     => $model->getRejectedCallbackUrl(),
            'class'     => 'validate-url',
        ));

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
