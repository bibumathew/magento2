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
 * Test class for \Magento\Catalog\Model\Product\Attribute\Backend\Price.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Price
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product\Attribute\Backend\Price');
        $this->_model->setAttribute(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Eav\Model\Config')
                ->getAttribute('catalog_product', 'price')
        );
    }

    public function testSetScopeDefault()
    {
        /* validate result of setAttribute */
        $this->assertEquals(
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL,
            $this->_model->getAttribute()->getIsGlobal()
        );
        $this->_model->setScope($this->_model->getAttribute());
        $this->assertEquals(
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL,
            $this->_model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testSetScope()
    {
        $this->_model->setScope($this->_model->getAttribute());
        $this->assertEquals(
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_WEBSITE,
            $this->_model->getAttribute()->getIsGlobal()
        );
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoConfigFixture current_store currency/options/base GBP
     */
    public function testAfterSave()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        $product->load(1);
        $product->setOrigData();
        $product->setPrice(9.99);
        $product->setStoreId(0);

        $this->_model->setScope($this->_model->getAttribute());
        $this->_model->afterSave($product);

        $this->assertEquals(
            '9.99',
            $product->getResource()->getAttributeRawValue(
                $product->getId(),
                $this->_model->getAttribute()->getId(),
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getId()
            )
        );
    }
}
