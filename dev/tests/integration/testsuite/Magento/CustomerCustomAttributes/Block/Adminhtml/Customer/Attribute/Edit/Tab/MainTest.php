<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CustomerCustomAttributes
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for \Magento\CustomerCustomAttributes\Block\Adminhtml\Customer\Attribute\Edit\Tab\Main
 *
 * @magentoAppArea adminhtml
 */
class Magento_CustomerCustomAttributes_Block_Adminhtml_Customer_Attribute_Edit_Tab_MainTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento\Core\Model\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_ADMINHTML)
            ->setDefaultDesignTheme();
        $entityType = Mage::getSingleton('Magento\Eav\Model\Config')->getEntityType('customer');
        $model = Mage::getModel('Magento\Customer\Model\Attribute');
        $model->setEntityTypeId($entityType->getId());
        /** @var $objectManager Magento_TestFramework_ObjectManager */
        $objectManager = Magento_TestFramework_Helper_Bootstrap::getObjectManager();
        $objectManager->get('Magento_Core_Model_Registry')->register('entity_attribute', $model);

        $block = Mage::app()->getLayout()->createBlock(
            'Magento\CustomerCustomAttributes\Block\Adminhtml\Customer\Attribute\Edit\Tab\Main'
        );
        $prepareFormMethod = new ReflectionMethod(
            'Magento\CustomerCustomAttributes\Block\Adminhtml\Customer\Attribute\Edit\Tab\Main', '_prepareForm');
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (array('date_range_min', 'date_range_max') as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
