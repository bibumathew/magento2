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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "if (editForm.submit()) { return false }",
                    'class'     => 'save'
                    ))
                );
        return parent::_prepareLayout();
    }

    /**
     * Prepare form before rendering HTML
     * Setting Form Fieldsets and fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('app_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('xmlconnect')->__('Device Information')));

        $fieldset->addField('type', 'select', array(
                'name'      => 'type',
                'label'     => Mage::helper('xmlconnect')->__('Device Type'),
                'title'     => Mage::helper('xmlconnect')->__('Device Type'),
                'values'    => Mage::helper('xmlconnect')->getDeviceTypeOptions(),
                'required'  => true
        ));

        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('xmlconnect')->__('Settings');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('xmlconnect')->__('Settings');
    }

    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return (bool) Mage::getSingleton('adminhtml/session')->getNewApplication();
    }

    /**
     * Check if tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}