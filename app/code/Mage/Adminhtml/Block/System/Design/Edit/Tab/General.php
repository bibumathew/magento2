<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Adminhtml_Block_System_Design_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Initialise form fields
     *
     * @return Mage_Adminhtml_Block_System_Design_Edit_Tab_General
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('general', array(
            'legend' => Mage::helper('Mage_Core_Helper_Data')->__('General Settings'))
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'select', array(
                'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Store'),
                'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Store'),
                'values'   => Mage::getSingleton('Mage_Core_Model_System_Store')->getStoreValuesForForm(),
                'name'     => 'store_id',
                'required' => true,
            ));
            $renderer = $this->getLayout()
                ->createBlock('Mage_Backend_Block_Store_Switcher_Form_Renderer_Fieldset_Element');
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id',
                'value'     => Mage::app()->getStore(true)->getId(),
            ));
        }

        $fieldset->addField('design', 'select', array(
            'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Custom Design'),
            'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Custom Design'),
            'values'   => Mage::getModel('Mage_Core_Model_Theme')->getLabelsCollection(
                Mage::helper('Mage_Core_Helper_Data')->__('-- Please Select --')
            ),
            'name'     => 'design',
            'required' => true,
        ));

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField('date_from', 'date', array(
            'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Date From'),
            'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Date From'),
            'name'     => 'date_from',
            'image'    => $this->getViewFileUrl('images/grid-cal.gif'),
            'date_format' => $dateFormat,
            //'required' => true,
        ));
        $fieldset->addField('date_to', 'date', array(
            'label'    => Mage::helper('Mage_Core_Helper_Data')->__('Date To'),
            'title'    => Mage::helper('Mage_Core_Helper_Data')->__('Date To'),
            'name'     => 'date_to',
            'image'    => $this->getViewFileUrl('images/grid-cal.gif'),
            'date_format' => $dateFormat,
            //'required' => true,
        ));

        $formData = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getDesignData(true);
        if (!$formData) {
            $formData = Mage::registry('design')->getData();
        } else {
            $formData = $formData['design'];
        }

        $form->addValues($formData);
        $form->setFieldNameSuffix('design');
        $this->setForm($form);
    }

}