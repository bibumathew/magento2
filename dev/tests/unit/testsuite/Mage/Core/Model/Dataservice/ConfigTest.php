<?php
/**
 * Test class for Mage_Core_Model_Dataservice_Config
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Dataservice_ConfigTest extends PHPUnit_Framework_TestCase
{
    const NAMEPART = 'NAMEPART';

    /** @var Mage_Core_Model_Dataservice_Config */
    protected $_dataserviceConfig;

    /** @var  Mage_Core_Model_Dataservice_Config_Reader */
    private $_reader;

    public function setup()
    {
        $this->_reader = $this->getMockBuilder('Mage_Core_Model_Dataservice_Config_Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $configXml = file_get_contents(__DIR__ . '/_files/service_calls.xml');
        $config = new Varien_Simplexml_Config($configXml);
        $this->_reader->expects($this->any())
            ->method('getServiceCallConfig')
            ->will($this->returnValue($config));

        $this->_dataserviceConfig = new Mage_Core_Model_Dataservice_Config($this->_reader);
    }

    public function testGetClassByAlias()
    {
        // result should match the config.xml file
        $result = $this->_dataserviceConfig->getClassByAlias('alias');
        $this->assertNotNull($result);
        $this->assertEquals('some_class_name', $result['class']);
        $this->assertEquals('some_method_name', $result['retrieveMethod']);
        $this->assertEquals('foo', $result['methodArguments']['some_arg_name']);
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Service call with name
     */
    public function testGetClassByAliasNotFound()
    {
        $this->_dataserviceConfig->getClassByAlias('none');
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage
     */
    public function testGetClassByAliasInvalidCall()
    {
        $this->_dataserviceConfig->getClassByAlias('missing_service');
    }

}
