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

/**
 * Test class for \Magento\Catalog\Block\Product\View\Options.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class Magento_Catalog_Block_Product_View_OptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View\Options
     */
    protected $_block;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    protected function setUp()
    {
        $this->_product = Mage::getModel('Magento\Catalog\Model\Product');
        $this->_product->load(1);
        /** @var $objectManager Magento_TestFramework_ObjectManager */
        $objectManager = Magento_TestFramework_Helper_Bootstrap::getObjectManager();
        $objectManager->get('Magento\Core\Model\Registry')->unregister('current_product');
        $objectManager->get('Magento\Core\Model\Registry')->register('current_product', $this->_product);
        $this->_block = Mage::app()->getLayout()->createBlock('Magento\Catalog\Block\Product\View\Options');
    }

    public function testSetGetProduct()
    {
        $this->assertSame($this->_product, $this->_block->getProduct());

        $product = Mage::getModel('Magento\Catalog\Model\Product');
        $this->_block->setProduct($product);
        $this->assertSame($product, $this->_block->getProduct());
    }

    public function testGetGroupOfOption()
    {
        $this->assertEquals('default', $this->_block->getGroupOfOption('test'));
    }

    public function testGetOptions()
    {
        $options = $this->_block->getOptions();
        $this->assertNotEmpty($options);
        foreach ($options as $option) {
            $this->assertInstanceOf('Magento\Catalog\Model\Product\Option', $option);
        }
    }

    public function testHasOptions()
    {
        $this->assertTrue($this->_block->hasOptions());
    }

    public function testGetJsonConfig()
    {
        $config = json_decode($this->_block->getJsonConfig());
        $this->assertNotNull($config);
        $this->assertNotEmpty($config);
    }
}
