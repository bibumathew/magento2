<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for \Magento\Checkout\Model\Session
 */
namespace Magento\Checkout\Model;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int|null $orderId
     * @param int|null $incrementId
     * @param \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject $orderMock
     * @dataProvider getLastRealOrderDataProvider
     */
    public function testGetLastRealOrder($orderId, $incrementId, $orderMock)
    {
        $orderFactory = $this->getMockBuilder('Magento\Sales\Model\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $orderFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($orderMock));
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $context = $helper->getObject('Magento\Core\Model\Session\Context');
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->getMock(
            'Magento\Checkout\Model\Session',
            array('init'),
            array($context, $orderFactory),
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
     * @return \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock($incrementId, $orderId)
    {
        /** @var $order \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order */
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')
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
