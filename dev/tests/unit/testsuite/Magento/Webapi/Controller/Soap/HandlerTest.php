<?php
/**
 * Test for \Magento\Webapi\Controller\Soap\Handler.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Webapi\Controller\Soap;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Soap\Handler */
    protected $_handler;

    /** @var \Magento\ObjectManager */
    protected $_objectManagerMock;

    /** @var \Magento\Webapi\Model\Soap\Config */
    protected $_apiConfigMock;

    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_requestMock;

    /** @var array */
    protected $_arguments;

    protected function setUp()
    {
        $this->markTestIncomplete("Needs to be fixed after service layer implementation.");

        /** Prepare mocks for SUT constructor. */
        $this->_apiConfigMock = $this->getMockBuilder('Magento\Webapi\Model\Soap\Config')
            ->setMethods(
                array(
                    'getServiceNameByOperation',
                    'getControllerClassByOperationName',
                    'getMethodNameByOperation',
                )
            )->disableOriginalConstructor()
            ->getMock();

        $this->_requestMock = $this->getMockBuilder('Magento\Webapi\Controller\Soap\Request')
            ->setMethods(array('getRequestedServices'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_objectManagerMock = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        /** Initialize SUT. */
        $this->_handler = new \Magento\Webapi\Controller\Soap\Handler(
            $this->_requestMock,
            $this->_objectManagerMock,
            $this->_apiConfigMock
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_handler);
        unset($this->_objectManagerMock);
        unset($this->_apiConfigMock);
        unset($this->_requestMock);
        parent::tearDown();
    }

    public function testCallEmptyUsernameTokenException()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_handler->setRequestHeaders(array('invalidHeader'));
        $this->setExpectedException(
            'Magento\Webapi\Model\Soap\Fault',
            'WS-Security UsernameToken is not found in SOAP-request.'
        );
        /** Execute SUT. */
        $this->_handler->__call('operation', array());
    }

    public function testCallMethodNotFoundException()
    {
        /** Prepare mock for _getOperationVersion() method. */
        $this->_requestMock->expects($this->once())
            ->method('getRequestedServices')
            ->will($this->returnValue(array('serviceName' => 'v1')));
        /** Create the arguments map of returned values for getServiceNameByOperation() method. */
        $getServiceValueMap = array(
            array('operation', null, 'serviceName'),
            array('operation', 'v1', false)
        );
        $this->_apiConfigMock->expects($this->any())
            ->method('getServiceNameByOperation')
            ->will($this->returnValueMap($getServiceValueMap));
        $this->setExpectedException(
            'Magento\Webapi\Model\Soap\Fault',
            'Method "operation" is not found.'
        );
        /** Execute SUT. */
        $this->_prepareSoapRequest();
        $this->_handler->__call('operation', $this->_arguments);
    }

    public function testCallInvalidOperationVersionException()
    {
        /** Prepare mock for _getOperationVersion() method. */
        $this->_requestMock->expects($this->once())
            ->method('getRequestedServices')
            ->will($this->returnValue(array('serviceName' => 'v1')));
        $this->_apiConfigMock->expects($this->once())
            ->method('getServiceNameByOperation')
            ->will($this->returnValue(false));
        $this->setExpectedException(
            'Magento\Webapi\Model\Soap\Fault',
            'The version of "operationName" operation cannot be identified.'
        );
        /** Execute SUT. */
        $this->_prepareSoapRequest();
        $this->_handler->__call('operationName', $this->_arguments);
    }

    public function testCall()
    {
        /** Prepare mock for SUT. */
        $this->_prepareSoapRequest();
        $method = 'Get';
        $service = 'serviceName';
        $operation = $service . $method;
        $this->_requestMock->expects($this->once())
            ->method('getRequestedServices')
            ->will($this->returnValue(array($service => 'v1')));
        $this->_apiConfigMock->expects($this->any())
            ->method('getServiceNameByOperation')
            ->will($this->returnValue($service));
        $this->_apiConfigMock->expects($this->once())
            ->method('validateVersionNumber')
            ->with(1, $service);
        $versionAfterFallback = 'V1';
        $action = $method . $versionAfterFallback;
        $this->_apiConfigMock->expects($this->once())
            ->method('getControllerClassByOperationName')
            ->with($operation)
            ->will($this->returnValue('Vendor\Module\Controller\Webapi\Resource'));
        $controllerMock = $this->getMockBuilder('Vendor\Module\Controller\Webapi\Resource')
            ->disableOriginalConstructor()
            ->setMethods(array($action))
            ->getMock();
        $this->_apiConfigMock->expects($this->once())
            ->method('getMethodNameByOperation')
            ->with($operation, '1')
            ->will($this->returnValue($method));
        $this->_apiConfigMock->expects($this->once())
            ->method('identifyVersionSuffix')
            ->with($operation, '1', $controllerMock)
            ->will($this->returnValue($versionAfterFallback));
        $this->_apiConfigMock->expects($this->once())
            ->method('checkDeprecationPolicy')
            ->with($service, $method, $versionAfterFallback);
        $arguments = reset($this->_arguments);
        $arguments = get_object_vars($arguments);
        $expectedResult = array('foo' => 'bar');
        $controllerMock->expects($this->once())
            ->method($action)
            ->with($arguments['customerId'])
            ->will($this->returnValue($expectedResult));

        /** Execute SUT. */
        $this->assertEquals(
            (object)array(\Magento\Webapi\Controller\Soap\Handler::RESULT_NODE_NAME => $expectedResult),
            $this->_handler->__call($operation, $this->_arguments)
        );
    }

    /**
     * Process security header and prepare request arguments.
     */
    protected function _prepareSoapRequest()
    {
        /** Process security header by __call() method. */
        $this->_handler->setRequestHeaders(array(\Magento\Webapi\Controller\Soap\Security::HEADER_SECURITY));
        $usernameToken = new \stdClass();
        // @codingStandardsIgnoreStart
        $usernameToken->UsernameToken = new \stdClass();
        $usernameToken->UsernameToken->Username = 'username';
        $usernameToken->UsernameToken->Password = 'password';
        $usernameToken->UsernameToken->Nonce = 'nonce';
        $usernameToken->UsernameToken->Created = 'created';
        // @codingStandardsIgnoreEnd
        $this->_handler->__call(
            \Magento\Webapi\Controller\Soap\Security::HEADER_SECURITY,
            array($usernameToken)
        );

        /** Override arguments for process action header. */
        $request = new \stdClass();
        $request->customerId = 1;
        $this->_arguments = array($request);
    }
}
