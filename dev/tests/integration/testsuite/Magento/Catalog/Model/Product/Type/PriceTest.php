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
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class Magento_Catalog_Model_Product_Type_PriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Catalog_Model_Product_Type_Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product_Type_Price');
    }

    public function testGetPrice()
    {
        $this->assertEquals('test', $this->_model->getPrice(new Magento_Object(array('price' => 'test'))));
    }

    public function testGetFinalPrice()
    {
        /** @var $product Magento_Catalog_Model_Product */
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->load(1); // fixture

        // regular & tier prices
        $this->assertEquals(10.0, $this->_model->getFinalPrice(1, $product));
        $this->assertEquals(8.0, $this->_model->getFinalPrice(2, $product));
        $this->assertEquals(5.0, $this->_model->getFinalPrice(5, $product));

        // with options
        $product->addCustomOption('option_ids', implode(',', array_keys($product->getOptions())));

        foreach ($product->getOptions() as $id => $option) {
            $product->addCustomOption("option_{$id}", $option->getValue());
        }
        $this->assertEquals(13.0, $this->_model->getFinalPrice(1, $product));
    }

    /**
     * Warning: this is a copy-paste from testGetFinalPrice(), but the method has different interface
     */
    public function testGetChildFinalPrice()
    {
        /** @var $product Magento_Catalog_Model_Product */
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->load(1); // fixture

        // regular & tier prices
        $this->assertEquals(10.0, $this->_model->getChildFinalPrice('', '', $product, 1));
        $this->assertEquals(8.0, $this->_model->getChildFinalPrice('', '', $product, 2));
        $this->assertEquals(5.0, $this->_model->getChildFinalPrice('', '', $product, 5));

        // with options
        $product->addCustomOption('option_ids', implode(',', array_keys($product->getOptions())));
        foreach ($product->getOptions() as $id => $option) {
            $product->addCustomOption("option_{$id}", $option->getValue());
        }
        $this->assertEquals(13.0, $this->_model->getChildFinalPrice('', '', $product, 1));
    }

    public function testGetTierPrice()
    {
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->load(1); // fixture
        $this->assertEquals(8.0, $this->_model->getTierPrice(2, $product));
        $this->assertEquals(5.0, $this->_model->getTierPrice(5, $product));
    }

    public function testGetTierPriceCount()
    {
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->load(1); // fixture
        $this->assertEquals(2, $this->_model->getTierPriceCount($product));
    }

    public function testGetFormatedTierPrice()
    {
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->load(1); // fixture
        $this->assertEquals('<span class="price">$8.00</span>', $this->_model->getFormatedTierPrice(2, $product));
    }

    public function testGetFormatedPrice()
    {
        $product = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->create('Magento_Catalog_Model_Product');
        $product->load(1); // fixture
        $this->assertEquals('<span class="price">$10.00</span>', $this->_model->getFormatedPrice($product));
    }

    public function testCalculatePrice()
    {
        $this->assertEquals(10, Magento_Catalog_Model_Product_Type_Price::calculatePrice(
            10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01'
        ));
        $this->assertEquals(8, Magento_Catalog_Model_Product_Type_Price::calculatePrice(
            10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01'
        ));
    }

    public function testCalculateSpecialPrice()
    {
        $this->assertEquals(10, Magento_Catalog_Model_Product_Type_Price::calculateSpecialPrice(
            10, 8, '1970-12-12 23:59:59', '1971-01-01 01:01:01'
        ));
        $this->assertEquals(8, Magento_Catalog_Model_Product_Type_Price::calculateSpecialPrice(
            10, 8, '1970-12-12 23:59:59', '2034-01-01 01:01:01'
        ));
    }

    public function testIsTierPriceFixed()
    {
        $this->assertTrue($this->_model->isTierPriceFixed());
    }
}
