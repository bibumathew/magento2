<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Catalog;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    public function testNewProductsWidget()
    {
        $type = 'Magento\Catalog\Block\Product\Widget\NewWidget';

        /** @var $model \Magento\Widget\Model\Widget\Instance */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Widget\Model\Widget\Instance');
        $config = $model->setType($type)->getWidgetConfigAsArray();
        $templates = $config['parameters']['template']['values'];
        $this->assertArrayHasKey('default', $templates);
        $this->assertArrayHasKey('list', $templates);
        $this->assertArrayHasKey('list_default', $templates);
        $this->assertArrayHasKey('list_names', $templates);
        $this->assertArrayHasKey('list_images', $templates);

        $blocks = $config['supported_containers'];

        $containers = array();
        foreach ($blocks as $block) {
            $containers[] = $block['container_name'];
        }

        $this->assertContains('left', $containers);
        $this->assertContains('content', $containers);
        $this->assertContains('right', $containers);

        // Verify that the correct id (code) is found for this widget instance type.
        $code = $model->setType($type)->getWidgetReference('type', $type, 'code');
        $this->assertEquals('new_products', $code);
    }
}
