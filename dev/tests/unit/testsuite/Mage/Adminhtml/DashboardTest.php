<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Adminhtml_DashboardTest extends PHPUnit_Framework_TestCase
{
    public function testTunnelAction()
    {
        $fixture = uniqid();
        /** @var $request Mage_Core_Controller_Request_Http|PHPUnit_Framework_MockObject_MockObject */
        $request = $this->getMockForAbstractClass('Mage_Core_Controller_Request_Http');
        $request->setParam('ga', urlencode(base64_encode(json_encode(array(1)))));
        $request->setParam('h', $fixture);

        $tunnelResponse = new Zend_Http_Response(200, array('Content-Type' => 'test_header'), 'success_msg');
        $httpClient = $this->getMock('Magento_HTTP_ZendClient', array('request'));
        $httpClient->expects($this->once())->method('request')->will($this->returnValue($tunnelResponse));
        /** @var $helper Mage_Adminhtml_Helper_Dashboard_Data|PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Mage_Adminhtml_Helper_Dashboard_Data',
            array('getChartDataHash'), array(), '', false, false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $objectManager = $this->getMock('Magento_ObjectManager');
        $objectManager->expects($this->at(0))
            ->method('get')
            ->with('Mage_Adminhtml_Helper_Dashboard_Data')
            ->will($this->returnValue($helper));
        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento_HTTP_ZendClient')
            ->will($this->returnValue($httpClient));

        $controller = $this->_factory($request, null, $objectManager);
        $controller->tunnelAction();
        $this->assertEquals('success_msg', $controller->getResponse()->getBody());
    }

    public function testTunnelAction400()
    {
        $controller = $this->_factory($this->getMockForAbstractClass('Mage_Core_Controller_Request_Http'));
        $controller->tunnelAction();
        $this->assertEquals(400, $controller->getResponse()->getHttpResponseCode());
    }

    public function testTunnelAction503()
    {
        $fixture = uniqid();
        /** @var $request Mage_Core_Controller_Request_Http|PHPUnit_Framework_MockObject_MockObject */
        $request = $this->getMockForAbstractClass('Mage_Core_Controller_Request_Http');
        $request->setParam('ga', urlencode(base64_encode(json_encode(array(1)))));
        $request->setParam('h', $fixture);

        /** @var $helper Mage_Adminhtml_Helper_Dashboard_Data|PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock('Mage_Adminhtml_Helper_Dashboard_Data',
            array('getChartDataHash'), array(), '', false, false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $objectManager = $this->getMock('Magento_ObjectManager');
        $objectManager->expects($this->at(0))
            ->method('get')
            ->with('Mage_Adminhtml_Helper_Dashboard_Data')
            ->will($this->returnValue($helper));
        $exceptionMock = new Exception();
        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento_HTTP_ZendClient')
            ->will($this->throwException($exceptionMock));
        $loggerMock = $this->getMock('Mage_Core_Model_Logger', array('logException'), array(), '', false);
        $loggerMock->expects($this->once())->method('logException')->with($exceptionMock);
        $objectManager->expects($this->at(2))
            ->method('get')
            ->with('Mage_Core_Model_Logger')
            ->will($this->returnValue($loggerMock));

        $controller = $this->_factory($request, null, $objectManager);
        $controller->tunnelAction();
        $this->assertEquals(503, $controller->getResponse()->getHttpResponseCode());
    }

    /**
     * Create the tested object
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http|null $response
     * @param Magento_ObjectManager|null $objectManager
     * @return Mage_Adminhtml_Controller_Dashboard|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _factory($request, $response = null, $objectManager = null)
    {
        if (!$response) {
            /** @var $response Mage_Core_Controller_Response_Http|PHPUnit_Framework_MockObject_MockObject */
            $response = $this->getMockForAbstractClass('Mage_Core_Controller_Response_Http');
            $response->headersSentThrowsException = false;
        }
        if (!$objectManager) {
            $objectManager = new Magento_ObjectManager_ObjectManager();
        }
        $rewriteFactory = $this->getMock('Mage_Core_Model_Url_RewriteFactory', array('create'), array(), '', false);
        $helper = new Magento_Test_Helper_ObjectManager($this);
        $varienFront = $helper->getObject('Mage_Core_Controller_Varien_Front',
            array('rewriteFactory' => $rewriteFactory)
        );

        $arguments = array(
            'request' => $request,
            'response' => $response,
            'objectManager' => $objectManager,
            'frontController' => $varienFront,

        );
        $context = $helper->getObject('Mage_Backend_Controller_Context', $arguments);
        return $this->getMock('Mage_Adminhtml_Controller_Dashboard', array('__'), array($context));
    }
}

