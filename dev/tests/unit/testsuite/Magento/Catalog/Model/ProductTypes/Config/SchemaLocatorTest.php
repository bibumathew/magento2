<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Catalog\Model\ProductTypes\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    protected function setUp()
    {
        $this->_moduleReaderMock = $this->getMock(
            'Magento\Module\Dir\Reader', array(), array(), '', false
        );
        $this->_moduleReaderMock->expects($this->once())
            ->method('getModuleDir')->with('etc', 'Magento_Catalog')->will($this->returnValue('schema_dir'));
        $this->_model = new \Magento\Catalog\Model\ProductTypes\Config\SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals(
            'schema_dir/product_types_merged.xsd',
            $this->_model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/product_types.xsd',
            $this->_model->getPerFileSchema()
        );
    }
}
