<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\VersionsCms\Model\Hierarchy\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\VersionsCms\Model\Hierarchy\Config\SchemaLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_modulesReaderMock;

    protected function setUp()
    {
        $this->_modulesReaderMock = $this->getMock(
            'Magento\Module\Dir\Reader', array(), array(), '', false
        );

        $this->_modulesReaderMock->expects($this->once())
            ->method('getModuleDir')
            ->with('etc', 'Magento_VersionsCms')
            ->will($this->returnValue('some_path'));

        $this->_model = new \Magento\VersionsCms\Model\Hierarchy\Config\SchemaLocator($this->_modulesReaderMock);
    }

    /**
     * @covers \Magento\VersionsCms\Model\Hierarchy\Config\SchemaLocator::getSchema
     */
    public function testGetSchema()
    {
        $expectedSchemaPath = 'some_path' . DIRECTORY_SEPARATOR . 'menu_hierarchy_merged.xsd';
        $this->assertEquals($expectedSchemaPath, $this->_model->getSchema());
    }

    /**
     * @covers \Magento\VersionsCms\Model\Hierarchy\Config\SchemaLocator::getPerFileSchema
     */
    public function testGetPerFileSchema()
    {
        $expectedSchemaPath = 'some_path' . DIRECTORY_SEPARATOR . 'menu_hierarchy.xsd';
        $this->assertEquals($expectedSchemaPath, $this->_model->getPerFileSchema());
    }
}
