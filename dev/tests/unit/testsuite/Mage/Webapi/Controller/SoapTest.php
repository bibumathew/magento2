<?php
/**
 * Test SOAP controller class.
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Webapi_Controller_SoapTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Controller_Soap */
    protected $_soapController;

    /** @var Mage_Webapi_Model_Soap_Server */
    protected $_soapServerMock;

    /** @var Mage_Webapi_Model_Soap_Wsdl_Generator */
    protected $_wsdlGeneratorMock;

    /** @var Mage_Webapi_Controller_Soap_Request */
    protected $_requestMock;

    /** @var Mage_Webapi_Controller_Response */
    protected $_responseMock;

    /** @var Mage_Webapi_Controller_ErrorProcessor */
    protected $_errorProcessorMock;

    /** @var Mage_Core_Model_App_State */
    protected $_appStateMock;

    /** @var Mage_Core_Model_App */
    protected $_applicationMock;

    /**
     * Set up Controller object.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->_soapServerMock = $this->getMockBuilder('Mage_Webapi_Model_Soap_Server')
            ->disableOriginalConstructor()
            ->setMethods(array('getApiCharset', 'generateUri', 'handle'))
            ->getMock();
        $this->_wsdlGeneratorMock = $this->getMockBuilder('Mage_Webapi_Model_Soap_Wsdl_Generator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();
        $this->_requestMock = $this->getMockBuilder('Mage_Webapi_Controller_Soap_Request')
            ->disableOriginalConstructor()
            ->setMethods(array('getParam', 'getRequestedServices'))
            ->getMock();
        $this->_responseMock = $this->getMockBuilder('Mage_Webapi_Controller_Response')
            ->disableOriginalConstructor()
            ->setMethods(array('clearHeaders', 'setHeader', 'sendResponse'))
            ->getMock();
        $this->_errorProcessorMock = $this->getMockBuilder('Mage_Webapi_Controller_ErrorProcessor')
            ->disableOriginalConstructor()
            ->setMethods(array('maskException'))
            ->getMock();
        $this->_appStateMock =  $this->getMockBuilder('Mage_Core_Model_App_State')
            ->disableOriginalConstructor()
            ->getMock();
        $localeMock =  $this->getMockBuilder('Mage_Core_Model_Locale')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocale', 'getLanguage'))
            ->getMock();
        $localeMock->expects($this->any())->method('getLocale')->will($this->returnValue($localeMock));
        $localeMock->expects($this->any())->method('getLanguage')->will($this->returnValue('en'));

        $this->_applicationMock =  $this->getMockBuilder('Mage_Core_Model_App')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocale', 'isDeveloperMode'))
            ->getMock();
        $this->_applicationMock->expects($this->any())->method('getLocale')->will($this->returnValue($localeMock));
        $this->_applicationMock->expects($this->any())->method('isDeveloperMode')->will($this->returnValue(false));

        $this->_responseMock->expects($this->any())->method('clearHeaders')->will($this->returnSelf());
        $this->_soapServerMock->expects($this->any())->method('setWSDL')->will($this->returnSelf());
        $this->_soapServerMock->expects($this->any())->method('setEncoding')->will($this->returnSelf());
        $this->_soapServerMock->expects($this->any())->method('setReturnResponse')->will($this->returnSelf());

        $helperMock = $this->getMockBuilder('Mage_Webapi_Helper_Data')->disableOriginalConstructor()->getMock();
        $helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));

        $this->_soapController = new Mage_Webapi_Controller_Soap(
            $this->_requestMock,
            $this->_responseMock,
            $this->_wsdlGeneratorMock,
            $this->_soapServerMock,
            $this->_errorProcessorMock,
            $this->_appStateMock,
            $this->_applicationMock,
            $helperMock
        );
    }

    /**
     * Clean up Controller and its dependencies.
     */
    protected function tearDown()
    {
        unset($this->_soapController);
        unset($this->_requestMock);
        unset($this->_responseMock);
        unset($this->_wsdlGeneratorMock);
        unset($this->_soapServerMock);
        unset($this->_errorProcessorMock);
        unset($this->_applicationMock);
        unset($this->_appStateMock);

        parent::tearDown();
    }


    /**
     * Test redirected to install page
     */
    public function testRedirectToInstallPage()
    {
        $this->_appStateMock->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(false));
        $this->_errorProcessorMock->expects($this->any())
            ->method('maskException')
            ->will($this->returnArgument(0));
        $encoding = "utf-8";
        $this->_soapServerMock->expects($this->any())
            ->method('getApiCharset')
            ->will($this->returnValue($encoding));

        $this->_soapController->dispatch();
        $expectedMessage = <<<EXPECTED_MESSAGE
<?xml version="1.0" encoding="{$encoding}"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" >
    <env:Body>
        <env:Fault>
            <env:Code>
                <env:Value>env:Sender</env:Value>
            </env:Code>
            <env:Reason>
                <env:Text xml:lang="en">Magento is not yet installed</env:Text>
            </env:Reason>
        </env:Fault>
    </env:Body>
</env:Envelope>
EXPECTED_MESSAGE;

        $this->assertXmlStringEqualsXmlString($expectedMessage, $this->_responseMock->getBody());
    }

    /**
     * Test successful WSDL content generation.
     */
    public function testDispatchWsdl()
    {
        $this->_appStateMock->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $this->_mockGetParam(Mage_Webapi_Model_Soap_Server::REQUEST_PARAM_WSDL, 1);
        $wsdl = 'Some WSDL content';
        $this->_wsdlGeneratorMock->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($wsdl));

        $this->_soapController->dispatch();
        $this->assertEquals($wsdl, $this->_responseMock->getBody());
    }

    /**
     * Test successful SOAP action request dispatch.
     */
    public function testDispatchSoapRequest()
    {
        $this->_appStateMock->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $soapResponse = 'Some some response';
        $this->_soapServerMock->expects($this->any())
            ->method('handle')
            ->will($this->returnValue($soapResponse));

        $this->_soapController->dispatch();
        $this->assertEquals($soapResponse, $this->_responseMock->getBody());
    }

    /**
     * Test handling exception during dispatch.
     */
    public function testDispatchWithException()
    {
        $this->_appStateMock->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));
        $exceptionMessage = 'some error message';
        $exception = new Mage_Webapi_Exception($exceptionMessage, Mage_Webapi_Exception::HTTP_BAD_REQUEST);
        $this->_soapServerMock->expects($this->any())
            ->method('handle')
            ->will($this->throwException($exception));
        $this->_errorProcessorMock->expects($this->any())
            ->method('maskException')
            ->will($this->returnValue($exception));
        $encoding = "utf-8";
        $this->_soapServerMock->expects($this->any())
            ->method('getApiCharset')
            ->will($this->returnValue($encoding));

        $this->_soapController->dispatch();

        $expectedMessage = <<<EXPECTED_MESSAGE
<?xml version="1.0" encoding="{$encoding}"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" >
   <env:Body>
      <env:Fault>
         <env:Code>
            <env:Value>env:Sender</env:Value>
         </env:Code>
         <env:Reason>
            <env:Text xml:lang="en">some error message</env:Text>
         </env:Reason>
      </env:Fault>
   </env:Body>
</env:Envelope>
EXPECTED_MESSAGE;
        $this->assertXmlStringEqualsXmlString($expectedMessage, $this->_responseMock->getBody());
    }

    /**
     * Mock getParam() of request object to return given value.
     *
     * @param $value
     */
    protected function _mockGetParam($param, $value)
    {
        $this->_requestMock->expects($this->once())
            ->method('getParam')
            ->with($param)
            ->will($this->returnValue($value));
    }

}
