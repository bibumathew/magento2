<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Magento_Test_Bootstrap_Profiler.
 */
class Magento_Test_Bootstrap_ProfilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Bootstrap_Profiler
     */
    protected $_object;

    /**
     * @var Magento_Profiler_Driver_Standard|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_driver;

    protected function setUp()
    {
        $this->expectOutputString('');
        $this->_driver = $this->getMock('Magento_Profiler_Driver_Standard', array('registerOutput'));
        $this->_object = new Magento_Test_Bootstrap_Profiler($this->_driver);
    }

    protected function tearDown()
    {
        $this->_driver = null;
        $this->_object = null;
    }

    public function testRegisterFileProfiler()
    {
        $this->_driver
            ->expects($this->once())
            ->method('registerOutput')
            ->with($this->isInstanceOf('Magento_Profiler_Driver_Standard_Output_Csvfile'))
        ;
        $this->_object->registerFileProfiler('php://output');
    }

    public function testRegisterBambooProfiler()
    {
        $this->_driver
            ->expects($this->once())
            ->method('registerOutput')
            ->with($this->isInstanceOf('Magento_Test_Profiler_OutputBamboo'))
        ;
        $this->_object->registerBambooProfiler('php://output', __DIR__ . '/_files/metrics.php');
    }
}