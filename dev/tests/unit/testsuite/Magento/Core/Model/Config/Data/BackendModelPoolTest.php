<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Core_Model_Config_Data_BackendModelPoolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Core_Model_Config_Data_BackendModelPoolTest
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_model = new Magento_Core_Model_Config_Data_BackendModelPool($this->_objectManager);
    }

    /**
     * @covers Magento_Core_Model_Config_Data_BackendModelPool::get
     */
    public function testGetModelWithCorrectInterface()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('TestBackendModel')
            ->will($this->returnValue(new TestBackendModel()));

        $this->assertInstanceOf('TestBackendModel', $this->_model->get('TestBackendModel'));
    }

    /**
     * @covers Magento_Core_Model_Config_Data_BackendModelPool::get
     * @expectedException InvalidArgumentException
     */
    public function testGetModelWithWrongInterface()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('WrongBackendModel')
            ->will($this->returnValue(new WrongBackendModel()));

        $this->_model->get('WrongBackendModel');
    }

    /**
     * @covers Magento_Core_Model_Config_Data_BackendModelPool::get
     */
    public function testGetMemoryCache()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('TestBackendModel')
            ->will($this->returnValue(new TestBackendModel()));

        $this->_model->get('TestBackendModel');
        $this->_model->get('TestBackendModel');
    }
}

class TestBackendModel implements Magento_Core_Model_Config_Data_BackendModelInterface
{
    public function processValue($value)
    {
        return $value;
    }
}

class WrongBackendModel
{
}
