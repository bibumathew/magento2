<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Admin rating left menu
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Rating_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('rating_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('Mage_Rating_Helper_Data')->__('Rating Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('Mage_Rating_Helper_Data')->__('Rating Information'),
            'title'     => Mage::helper('Mage_Rating_Helper_Data')->__('Rating Information'),
            'content'   => $this->getLayout()->createBlock('Mage_Adminhtml_Block_Rating_Edit_Tab_Form')->toHtml(),
        ))
        ;
/*
        $this->addTab('answers_section', array(
                'label'     => Mage::helper('Mage_Rating_Helper_Data')->__('Rating Options'),
                'title'     => Mage::helper('Mage_Rating_Helper_Data')->__('Rating Options'),
                'content'   => $this->getLayout()->createBlock('Mage_Adminhtml_Block_Rating_Edit_Tab_Options')
                    ->append($this->getLayout()->createBlock('Mage_Adminhtml_Block_Rating_Edit_Tab_Options'))
                    ->toHtml(),
           ));*/
        return parent::_beforeToHtml();
    }
}
