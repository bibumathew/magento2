<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for \Magento\Core\Model\Layout\Argument\Handler\Url
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\Handler\Helper
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_urlModleMock = $this->getMock('Magento\Core\Model\Url', array(), array(), '', false);
        $this->_model = $helperObjectManager->getObject(
            'Magento\Core\Model\Layout\Argument\Handler\Url',
            array('urlModel' => $this->_urlModleMock)
        );
    }

    /**
     * @dataProvider parseDataProvider()
     * @param \Magento\View\Layout\Element $argument
     * @param array $expectedResult
     */
    public function testParse($argument, $expectedResult)
    {
        $result = $this->_model->parse($argument);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function parseDataProvider()
    {
        $layout = simplexml_load_file(
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'arguments.xml',
            'Magento\View\Layout\Element'
        );
        $result = $this->processDataProvider();
        $resultWithParams = $resultWithoutParams = $result[0][0];
        $resultWithoutParams['value']['params'] = array();
        $argWithParams = $layout->xpath('//argument[@name="testUrlWithParams"]');
        $argWithoutParams = $layout->xpath('//argument[@name="testUrlWithoutParams"]');
        return array(
            array($argWithParams[0], $resultWithParams + array('type' => 'url')),
            array($argWithoutParams[0], $resultWithoutParams + array('type' => 'url')),
        );
    }

    /**
     * @dataProvider processDataProvider
     * @param array $argument
     * @param boolean $expectedResult
     */
    public function testProcess($argument, $expectedResult)
    {
        $this->_urlModleMock->expects($this->once())
            ->method('getUrl')
            ->with($argument['value']['path'], $argument['value']['params'])
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($this->_model->process($argument), $expectedResult);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            array(
                array(
                    'value' => array(
                        'path' => 'module/controller/action',
                        'params' => array(
                            'firstParam' => 'firstValue',
                            'secondParam' => 'secondValue',
                        ),
                    )
                )
                , 'test/url'
            ),
        );
    }

    /**
     * @dataProvider processExceptionDataProvider
     * @param array $argument
     * @param string $message
     */
    public function testProcessException($argument, $message)
    {
        $this->setExpectedException(
            'InvalidArgumentException', $message
        );
        $this->_model->process($argument);
    }

    /**
     * @return array
     */
    public function processExceptionDataProvider()
    {
        return array(
            array(array(), 'Value is required for argument'),
            array(array('value' => array()), 'Passed value has incorrect format'),
        );
    }
}
