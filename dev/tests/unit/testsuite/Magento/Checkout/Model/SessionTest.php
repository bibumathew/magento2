<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Magento_Checkout_Model_Session
 */
class Magento_Checkout_Model_SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param int|null $orderId
     * @param int|null $incrementId
     * @param Magento_Sales_Model_Order|PHPUnit_Framework_MockObject_MockObject $orderMock
     * @dataProvider getLastRealOrderDataProvider
     */
    public function testGetLastRealOrder($orderId, $incrementId, $orderMock)
    {
        $orderFactory = $this->getMockBuilder('Magento_Sales_Model_OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $orderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderMock));
        $coreHttp = $this->getMock('Magento_Core_Helper_Http', array(), array(), '', false);

        $eventManager = $this->getMock('Magento_Core_Model_Event_Manager', array(), array(), '', false);
        $logger = $this->getMock('Magento_Core_Model_Logger', array(), array(), '', false);

        $validatorMock = $this->getMock('Magento_Core_Model_Session_Validator', array(), array(), '', false);

        $coreStoreConfig = $this->getMock('Magento_Core_Model_Store_Config', array(), array(), '', false);
        $coreConfig = $this->getMock('Magento_Core_Model_Config', array(), array(), '', false);
        /** @var Magento_Checkout_Model_Session $session */
        $session = $this->getMock(
            'Magento_Checkout_Model_Session',
            array('init'),
            array(
                $orderFactory,
                $validatorMock,
                $logger,
                $eventManager,
                $coreHttp,
                $coreStoreConfig,
                $coreConfig,
                $this->getMock('Magento_Core_Model_Message_CollectionFactory', array(), array(), '', false),
                $this->getMock('Magento_Core_Model_Message', array(), array(), '', false),
                $this->getMock('Magento_Core_Model_Cookie', array(), array(), '', false),
                $this->getMock('Magento_Core_Controller_Request_Http', array(), array(), '', false),
                $this->getMock('Magento_Core_Model_App_State', array(), array(), '', false),
                $this->getMock('Magento_Core_Model_StoreManager', array(), array(), '', false),
                $this->getMock('Magento_Core_Model_Dir', array(), array(), '', false),
                $this->getMock('Magento_Core_Model_Url_Proxy', array(), array(), '', false),
            ),
            ''
        );
        $session->setLastRealOrderId($orderId);

        $this->assertSame($orderMock, $session->getLastRealOrder());
        if ($orderId == $incrementId) {
            $this->assertSame($orderMock, $session->getLastRealOrder());
        }
    }

    /**
     * @return array
     */
    public function getLastRealOrderDataProvider()
    {
        return array(
            array(null, 1, $this->_getOrderMock(1, null)),
            array(1, 1, $this->_getOrderMock(1, 1)),
            array(1, null, $this->_getOrderMock(null, 1))
        );
    }

    /**
     * @param int|null $incrementId
     * @param int|null $orderId
     * @return Magento_Sales_Model_Order|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock($incrementId, $orderId)
    {
        /** @var $order PHPUnit_Framework_MockObject_MockObject|Magento_Sales_Model_Order */
        $order = $this->getMockBuilder('Magento_Sales_Model_Order')
            ->disableOriginalConstructor()
            ->setMethods(array('getIncrementId', 'loadByIncrementId', '__sleep', '__wakeup'))
            ->getMock();

        $order->expects($this->once())
            ->method('getIncrementId')
            ->will($this->returnValue($incrementId));

        if ($orderId) {
            $order->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId);
        }

        if ($orderId == $incrementId) {
            $order->expects($this->once())
                ->method('getIncrementId')
                ->will($this->returnValue($incrementId));
        }

        return $order;
    }
}
