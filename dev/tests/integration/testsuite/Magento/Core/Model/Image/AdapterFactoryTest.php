<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for \Magento\Core\Model\Image\AdapterFactory
 * @magentoAppArea adminhtml
 */
namespace Magento\Core\Model\Image;

class AdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Image\AdapterFactory');
        $this->_config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Store\Config');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testCreate()
    {
        $result = $this->_model->create();
        $this->assertInstanceOf('Magento\Image\Adapter\AbstractAdapter', $result);
        $this->assertNotEmpty(
            $this->_config->getConfig(\Magento\Core\Model\Image\AdapterFactory::XML_PATH_IMAGE_ADAPTER)
        );
    }
}
