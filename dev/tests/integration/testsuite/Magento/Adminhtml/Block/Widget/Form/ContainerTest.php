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
 * @magentoAppArea adminhtml
 */
class Magento_Adminhtml_Block_Widget_Form_ContainerTest extends PHPUnit_Framework_TestCase
{
    public function testGetFormHtml()
    {
        $objectManager = Magento_TestFramework_Helper_Bootstrap::getObjectManager();
        /** @var $layout \Magento\Core\Model\Layout */
        $layout = Mage::getSingleton('Magento\Core\Model\Layout');
        // Create block with blocking _prepateLayout(), which is used by block to instantly add 'form' child
        /** @var $block \Magento\Adminhtml\Block\Widget\Form\Container */
        $block = $this->getMock('Magento\Adminhtml\Block\Widget\Form\Container', array('_prepareLayout'),
            array(
                $objectManager->create('Magento_Core_Helper_Data'),
                $objectManager->create('Magento_Backend_Block_Template_Context'),
            )
        );

        $layout->addBlock($block, 'block');
        $form = $layout->addBlock('Magento\Core\Block\Text', 'form', 'block');

        $expectedHtml = '<b>html</b>';
        $this->assertNotEquals($expectedHtml, $block->getFormHtml());
        $form->setText($expectedHtml);
        $this->assertEquals($expectedHtml, $block->getFormHtml());
    }
}
