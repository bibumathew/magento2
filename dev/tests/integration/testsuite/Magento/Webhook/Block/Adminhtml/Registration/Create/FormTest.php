<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Webhook
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Webhook\Block\Adminhtml\Registration\Create;

/**
 * \Magento\Webhook\Block\Adminhtml\Registration\Create\Form
 *
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareForm()
    {
        /** @var $layout \Magento\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\Layout');

        /** @var \Magento\Core\Model\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Registry');
        $subscriptionData = array(
            'subscription_id' => '333',
        );
        $registry
            ->register(
                'current_subscription',
                $subscriptionData
            );

        /** @var \Magento\Webhook\Block\Adminhtml\Registration\Create\Form $block */
        $block = $layout->createBlock('Magento\Webhook\Block\Adminhtml\Registration\Create\Form',
            '', array('registry' => $registry)
        );
        $block->toHtml();

        $form = $block->getForm();

        $this->assertInstanceOf('Magento\Data\Form', $form);
        $this->assertEquals('post', $form->getData('method'));
        $this->assertEquals($block->getUrl('adminhtml/*/register', array('id' => 333)), $form->getData('action'));
        $this->assertEquals('api_user', $form->getId());


        $expectedFieldset = array(
            'company' => array(
                'name' => 'company',
                'type' => 'text',
                'required' => false
            ),
            'email' => array(
                'name' => 'email',
                'type' => 'text',
                'required' => true
            ),
            'apikey' => array(
                'name' => 'apikey',
                'type' => 'text',
                'required' => true
            ),
            'apisecret' => array(
                'name' => 'apisecret',
                'type' => 'text',
                'required' => true
            )
        );

        foreach ($expectedFieldset as $fieldId => $field) {
            $element = $form->getElement($fieldId);
            $this->assertInstanceOf('Magento\Data\Form\Element\AbstractElement', $element);
            $this->assertEquals($field['name'], $element->getName(), 'Wrong \'' . $fieldId . '\' field name');
            $this->assertEquals($field['type'], $element->getType(), 'Wrong \'' . $fieldId . ' field type');
            $this->assertEquals($field['required'], $element->getData('required'),
                'Wrong \'' . $fieldId . '\' requirement state'
            );
        }
    }
}
