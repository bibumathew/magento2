<?php
/**
 * Test Webapi Json Interpreter Request Rest Controller.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Webapi_Controller_Rest_Request_Interpreter_JsonTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_helperFactoryMock;

    /** @var Magento_Webapi_Controller_Rest_Request_Interpreter_Json */
    protected $_jsonInterpreter;

    /** @var Magento_Core_Helper_Data */
    protected $_helperMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_appMock;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperFactoryMock = $this->getMock('Magento_Core_Model_Factory_Helper');
        $this->_helperMock = $this->getMockBuilder('Magento_Core_Helper_Data')->disableOriginalConstructor()->getMock();
        $this->_helperFactoryMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->_helperMock));
        $this->_appMock = $this->getMockBuilder('Magento_Core_Model_App')
            ->setMethods(array('isDeveloperMode'))
            ->disableOriginalConstructor()
            ->getMock();
        /** Initialize SUT. */
        $this->_jsonInterpreter = new Magento_Webapi_Controller_Rest_Request_Interpreter_Json(
            $this->_helperFactoryMock,
            $this->_appMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_helperFactoryMock);
        unset($this->_jsonInterpreter);
        unset($this->_helperMock);
        unset($this->_appMock);
        parent::tearDown();
    }

    public function testInterpretInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException', '"boolean" data type is invalid. String is expected.');
        $this->_jsonInterpreter->interpret(false);
    }

    public function testInterpret()
    {
        /** Prepare mocks for SUT constructor. */
        $inputEncodedJson = '{"key1":"test1","key2":"test2","array":{"test01":"some1","test02":"some2"}}';
        $expectedDecodedJson = array(
            'key1' => 'test1',
            'key2' => 'test2',
            'array' => array(
                'test01' => 'some1',
                'test02' => 'some2',
            )
        );
        $this->_helperMock->expects($this->once())
            ->method('jsonDecode')
            ->will($this->returnValue($expectedDecodedJson));
        /** Initialize SUT. */
        $this->assertEquals(
            $expectedDecodedJson,
            $this->_jsonInterpreter->interpret($inputEncodedJson),
            'Interpretation from JSON to array is invalid.'
        );
    }

    public function testInterpretInvalidEncodedBodyExceptionDeveloperModeOff()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperMock->expects($this->once())
            ->method('jsonDecode')
            ->will($this->throwException(new Zend_Json_Exception));
        $this->_appMock->expects($this->once())
            ->method('isDeveloperMode')
            ->will($this->returnValue(false));
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonInterpreter->interpret($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (Magento_Webapi_Exception $e) {
            $this->assertInstanceOf('Magento_Webapi_Exception', $e, 'Exception type is invalid');
            $this->assertEquals('Decoding error.', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(Magento_Webapi_Exception::HTTP_BAD_REQUEST, $e->getHttpCode(), 'HTTP code is invalid');
        }
    }

    public function testInterpretInvalidEncodedBodyExceptionDeveloperModeOn()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperMock->expects($this->once())
            ->method('jsonDecode')
            ->will(
            $this->throwException(
                new Zend_Json_Exception('Decoding error:' . PHP_EOL . 'Decoding failed: Syntax error')
            )
        );
        $this->_appMock->expects($this->once())
            ->method('isDeveloperMode')
            ->will($this->returnValue(true));
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonInterpreter->interpret($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (Magento_Webapi_Exception $e) {
            $this->assertInstanceOf('Magento_Webapi_Exception', $e, 'Exception type is invalid');
            $this->assertContains('Decoding error:', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(Magento_Webapi_Exception::HTTP_BAD_REQUEST, $e->getHttpCode(), 'HTTP code is invalid');
        }
    }
}
