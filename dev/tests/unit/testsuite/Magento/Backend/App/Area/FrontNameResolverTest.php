<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Backend\App\Area;

class FrontNameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Area\FrontNameResolver
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var string
     */
    protected $_defaultFrontName = 'defaultFrontName';

    protected function setUp()
    {
        $this->_configMock
            = $this->getMock('\Magento\Core\Model\ConfigInterface');
        $this->_model = new \Magento\Backend\App\Area\FrontNameResolver($this->_configMock, $this->_defaultFrontName);
    }

    public function testIfCustomPathUsed()
    {
        $this->_configMock->expects($this->at(0))
            ->method('getValue')->with('admin/url/use_custom_path', 'default')->will($this->returnValue(true));
        $this->_configMock->expects($this->at(1))
            ->method('getValue')->with('admin/url/custom_path', 'default')->will($this->returnValue('expectedValue'));
        $this->assertEquals('expectedValue', $this->_model->getFrontName());
    }

    public function testIfCustomPathNotUsed()
    {
        $this->_configMock->expects($this->once())->
            method('getValue')->with('admin/url/use_custom_path', 'default')->will($this->returnValue(false));
        $this->assertEquals($this->_defaultFrontName, $this->_model->getFrontName());
    }
}