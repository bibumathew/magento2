<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer account form block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Customer_Edit_Tab_Account extends Magento_Adminhtml_Block_Widget_Form
{

    /*
     * Disable Auto Group Change Attribute Name
     */
    const DISABLE_ATTRIBUTE_NAME = 'disable_auto_group_change';

    /**
     * Customer data
     *
     * @var Magento_Customer_Helper_Data
     */
    protected $_customerData = null;

    /**
     * @param Magento_Customer_Helper_Data $customerData
     * @param  $context
     * @param  $data
     */
    public function __construct(
        Magento_Customer_Helper_Data $customerData,
        Magento_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_customerData = $customerData;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     *
     * @return Magento_Adminhtml_Block_Customer_Edit_Tab_Account
     */
    public function initForm()
    {
        $form = new Magento_Data_Form();
        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('account');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => __('Account Information')
        ));

        $customer = Mage::registry('current_customer');
        /** @var $customerForm Magento_Customer_Model_Form */
        $customerForm = $this->_initCustomerForm($customer);
        $attributes = $this->_initCustomerAttributes($customerForm);
        $this->_setFieldset($attributes, $fieldset, array(self::DISABLE_ATTRIBUTE_NAME));

        $form->getElement('group_id')->setRenderer($this->getLayout()
            ->createBlock('Magento_Adminhtml_Block_Customer_Edit_Renderer_Attribute_Group')
            ->setDisableAutoGroupChangeAttribute($customerForm->getAttribute(self::DISABLE_ATTRIBUTE_NAME))
            ->setDisableAutoGroupChangeAttributeValue($customer->getData(self::DISABLE_ATTRIBUTE_NAME))
        );

        $this->_setCustomerWebsiteId($customer);
        $customerStoreId = $this->_getCustomerStoreId($customer);

        $prefixElement = $form->getElement('prefix');
        if ($prefixElement) {
            $prefixOptions = $this->_customerData->getNamePrefixOptions($customerStoreId);
            if (!empty($prefixOptions)) {
                $fieldset->removeField($prefixElement->getId());
                $prefixField = $fieldset->addField($prefixElement->getId(),
                    'select',
                    $prefixElement->getData(),
                    $form->getElement('group_id')->getId()
                );
                $prefixField->setValues($prefixOptions);
                if ($customer->getId()) {
                    $prefixField->addElementValues($customer->getPrefix());
                }
            }
        }

        $suffixElement = $form->getElement('suffix');
        if ($suffixElement) {
            $suffixOptions = $this->_customerData->getNameSuffixOptions($customerStoreId);
            if (!empty($suffixOptions)) {
                $fieldset->removeField($suffixElement->getId());
                $suffixField = $fieldset->addField($suffixElement->getId(),
                    'select',
                    $suffixElement->getData(),
                    $form->getElement('lastname')->getId()
                );
                $suffixField->setValues($suffixOptions);
                if ($customer->getId()) {
                    $suffixField->addElementValues($customer->getSuffix());
                }
            }
        }

        if ($customer->getId()) {
            $this->_addEditCustomerFormFields($form, $fieldset, $customer);
        } else {
            $this->_addNewCustomerFormFields($form, $fieldset);
            $customer->setData('sendemail', '1');
        }

        $this->_disableSendEmailStoreForEmptyWebsite($form);
        $this->_handleReadOnlyCustomer($form, $customer);

        $form->setValues($customer->getData());
        $this->setForm($form);
        return $this;
    }

    /**
     * Return predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'      => 'Magento_Adminhtml_Block_Customer_Form_Element_File',
            'image'     => 'Magento_Adminhtml_Block_Customer_Form_Element_Image',
            'boolean'   => 'Magento_Adminhtml_Block_Customer_Form_Element_Boolean',
        );
    }

    /**
     * Initialize attribute set
     *
     * @param Magento_Customer_Model_Form $customerFor
     * @return Magento_Eav_Model_Entity_Attribute[]
     */
    protected function _initCustomerAttributes(Magento_Customer_Model_Form $customerForm)
    {
        $attributes = $customerForm->getAttributes();
        foreach ($attributes as $attribute) {
            /* @var $attribute Magento_Eav_Model_Entity_Attribute */
            $attributeLabel = __($attribute->getFrontend()->getLabel());
            $attribute->setFrontendLabel($attributeLabel);
            $attribute->unsIsVisible();
        }
        return $attributes;
    }

    /**
     * Initialize customer form
     *
     * @param Magento_Customer_Model_Customer $customer
     * @return Magento_Customer_Model_Form $customerForm
     */
    protected function _initCustomerForm(Magento_Customer_Model_Customer $customer)
    {
        /** @var $customerForm Magento_Customer_Model_Form */
        $customerForm = Mage::getModel('Magento_Customer_Model_Form');
        $customerForm->setEntity($customer)
            ->setFormCode('adminhtml_customer')
            ->initDefaultValues();

        return $customerForm;
    }

    /**
     * Handle Read-Only customer
     *
     * @param Magento_Data_Form $form
     * @param Magento_Customer_Model_Customer $customer
     */
    protected function _handleReadOnlyCustomer($form, $customer)
    {
        if (!$customer->isReadonly()) {
            return;
        }
        foreach ($customer->getAttributes() as $attribute) {
            $element = $form->getElement($attribute->getAttributeCode());
            if ($element) {
                $element->setReadonly(true, true);
            }
        }
    }

    /**
     * Make sendemail or sendmail_store_id disabled if website_id has an empty value
     *
     * @param Magento_Data_Form $form
     */
    protected function _disableSendEmailStoreForEmptyWebsite(Magento_Data_Form $form)
    {
        $isSingleMode = Mage::app()->isSingleStoreMode();
        $sendEmailId = $isSingleMode ? 'sendemail' : 'sendemail_store_id';
        $sendEmail = $form->getElement($sendEmailId);

        $prefix = $form->getHtmlIdPrefix();
        if ($sendEmail) {
            $_disableStoreField = '';
            if (!$isSingleMode) {
                $_disableStoreField = "$('{$prefix}sendemail_store_id').disabled=(''==this.value || '0'==this.value);";
            }
            $sendEmail->setAfterElementHtml(
                '<script type="text/javascript">'
                . "
                document.observe('dom:loaded', function(){
                    $('{$prefix}website_id').disableSendemail = function() {
                        $('{$prefix}sendemail').disabled = ('' == this.value || '0' == this.value);".
                        $_disableStoreField
                    ."\n}.bind($('{$prefix}website_id'));
                    Event.observe('{$prefix}website_id', 'change', $('{$prefix}website_id').disableSendemail);
                    $('{$prefix}website_id').disableSendemail();
                });
                "
                . '</script>'
            );
        }
    }

    /**
     * Create New Customer form fields
     *
     * @param Magento_Data_Form $form
     * @param Magento_Data_Form_Element_Fieldset $fieldset
     */
    protected function _addNewCustomerFormFields($form, $fieldset)
    {
        $fieldset->removeField('created_in');

        // Prepare send welcome email checkbox
        $fieldset->addField('sendemail', 'checkbox', array(
            'label' => __('Send Welcome Email'),
            'name'  => 'sendemail',
            'id'    => 'sendemail',
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $form->getElement('website_id')->addClass('validate-website-has-store');

            $websites = array();
            foreach (Mage::app()->getWebsites(true) as $website) {
                $websites[$website->getId()] = !is_null($website->getDefaultStore());
            }
            $prefix = $form->getHtmlIdPrefix();

            $note = __('Please select a website which contains store view');
            $form->getElement('website_id')->setAfterElementHtml(
                '<script type="text/javascript">'
                . "
                var {$prefix}_websites = " . $this->_coreData->jsonEncode($websites) .";
                jQuery.validator.addMethod('validate-website-has-store', function(v, elem){
                        return {$prefix}_websites[elem.value] == true;
                    },
                    '" . $note . "'
                );
                Element.observe('{$prefix}website_id', 'change', function(){
                    jQuery.validator.validateElement('#{$prefix}website_id');
                }.bind($('{$prefix}website_id')));
                "
                . '</script>'
            );
            $renderer = $this->getLayout()
                ->createBlock('Magento_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element');
            $form->getElement('website_id')->setRenderer($renderer);

            $fieldset->addField('sendemail_store_id', 'select', array(
                'label' => __('Send From'),
                'name' => 'sendemail_store_id',
                'values' => Mage::getSingleton('Magento_Core_Model_System_Store')->getStoreValuesForForm()
            ));
        } else {
            $fieldset->removeField('website_id');
            $fieldset->addField('website_id', 'hidden', array(
                'name' => 'website_id'
            ));
        }
    }

    /**
     * Edit/View Existing Customer form fields
     *
     * @param Magento_Data_Form $form
     * @param Magento_Data_Form_Element_Fieldset $fieldset
     * @param Magento_Customer_Model_Customer $customer
     */
    protected function _addEditCustomerFormFields($form, $fieldset, $customer)
    {
        $form->getElement('created_in')->setDisabled('disabled');
        if (!Mage::app()->isSingleStoreMode()) {
            $form->getElement('website_id')->setDisabled('disabled');
            $renderer = $this->getLayout()
                ->createBlock('Magento_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element');
            $form->getElement('website_id')->setRenderer($renderer);
        } else {
            $fieldset->removeField('website_id');
        }

        if ($customer->isReadonly()) {
            return;
        }

        // Prepare customer confirmation control (only for existing customers)
        $confirmationKey = $customer->getConfirmation();
        if ($confirmationKey || $customer->isConfirmationRequired()) {
            $confirmationAttr = $customer->getAttribute('confirmation');
            if (!$confirmationKey) {
                $confirmationKey = $customer->getRandomConfirmationKey();
            }

            $element = $fieldset->addField('confirmation', 'select', array(
                'name'  => 'confirmation',
                'label' => __($confirmationAttr->getFrontendLabel()),
            ));
            $element->setEntityAttribute($confirmationAttr);
            $element->setValues(array(
                '' => 'Confirmed',
                $confirmationKey => 'Not confirmed'
            ));

            // Prepare send welcome email checkbox if customer is not confirmed
            // no need to add it, if website ID is empty
            if ($customer->getConfirmation() && $customer->getWebsiteId()) {
                $fieldset->addField('sendemail', 'checkbox', array(
                    'name'  => 'sendemail',
                    'label' => __('Send Welcome Email after Confirmation')
                ));
                $customer->setData('sendemail', '1');
            }
        }
    }

    /**
     * Add Password management fieldset
     *
     * @param Magento_Data_Form $form
     * @param string $fieldLabel
     * @param boolean $isNew whether we set initial password or change existing one
     */
    protected function _addPasswordManagementFieldset($form, $fieldLabel, $isNew)
    {
        // Add password management fieldset
        $newFieldset = $form->addFieldset(
            'password_fieldset',
            array('legend' => __('Password Management'))
        );
        if ($isNew) {
            // New customer password for existing customer
            $elementId = 'new_password';
            $elementClass = 'validate-new-password';
        } else {
            // Password field for newly generated customer
            $elementId = 'password';
            $elementClass = 'input-text required-entry validate-password';
        }
        $field = $newFieldset->addField($elementId, 'text',
            array(
                'label' => __($fieldLabel),
                'name'  => $elementId,
                'class' => $elementClass,
                'required' => !$isNew,
            )
        );
        $field->setRenderer(
            $this->getLayout()->createBlock('Magento_Adminhtml_Block_Customer_Edit_Renderer_Newpass')
        );
    }

    /**
     * Get Customer Store Id
     *
     * @param Magento_Customer_Model_Customer $customer
     * @return int|null
     */
    protected function _getCustomerStoreId(Magento_Customer_Model_Customer $customer)
    {
        $customerStoreId = null;
        if ($customer->getId()) {
            $customerStoreId = Mage::app()->getWebsite($customer->getWebsiteId())
                ->getDefaultStore()
                ->getId();
        }
        return $customerStoreId;
    }

    /**
     * Set Customer Website Id in Single Store Mode
     *
     * @param Magento_Customer_Model_Customer $customer
     */
    protected function _setCustomerWebsiteId(Magento_Customer_Model_Customer $customer)
    {
        if (Mage::app()->isSingleStoreMode()) {
            $customer->setWebsiteId(Mage::app()->getStore(true)->getWebsiteId());
        }
    }
}
