<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Cron
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Cron_Model_Config_SchemaLocatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var Magento_Cron_Model_Config_SchemaLocator
     */
    protected $_locator;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->_moduleReaderMock = $this->getMockBuilder('Magento_Core_Model_Config_Modules_Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_moduleReaderMock->expects($this->once())
            ->method('getModuleDir')->with('etc', 'Magento_Cron')->will($this->returnValue('schema_dir'));
        $this->_locator = new Magento_Cron_Model_Config_SchemaLocator($this->_moduleReaderMock);
    }

    /**
     * Testing that schema has file
     */
    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/crontab.xsd', $this->_locator->getSchema());
    }
}
