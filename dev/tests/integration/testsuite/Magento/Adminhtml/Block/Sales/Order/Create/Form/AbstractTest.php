<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Magento_Adminhtml_Block_Sales_Order_Create_Form_Abstract
 */
class Magento_Adminhtml_Block_Sales_Order_Create_Form_AbstractTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testAddAttributesToForm()
    {
        $objectManager = Magento_Test_Helper_Bootstrap::getObjectManager();
        Mage::getDesign()->setArea(Magento_Core_Model_App_Area::AREA_ADMINHTML)->setDefaultDesignTheme();
        $arguments = array(
            $objectManager->get('Magento_Backend_Block_Template_Context'),
            $objectManager->get('Magento_Data_Form_Factory')
        );
        /** @var $block Magento_Adminhtml_Block_Sales_Order_Create_Form_Abstract */
        $block = $this->getMockForAbstractClass('Magento_Adminhtml_Block_Sales_Order_Create_Form_Abstract', $arguments);
        $block->setLayout($objectManager->create('Magento_Core_Model_Layout'));

        $method = new ReflectionMethod(
            'Magento_Adminhtml_Block_Sales_Order_Create_Form_Abstract', '_addAttributesToForm');
        $method->setAccessible(true);

        /** @var $formFactory Magento_Data_Form_Factory */
        $formFactory = $objectManager->get('Magento_Data_Form_Factory');
        $form = $formFactory->create();
        $fieldset = $form->addFieldset('test_fieldset', array());
        $arguments = array(
            'data' => array(
                'attribute_code' => 'date',
                'backend_type' => 'datetime',
                'frontend_input' => 'date',
                'frontend_label' => 'Date',
            )
        );
        $dateAttribute = $objectManager->create('Magento_Customer_Model_Attribute', $arguments);
        $attributes = array('date' => $dateAttribute);
        $method->invoke($block, $attributes, $fieldset);

        $element = $form->getElement('date');
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
