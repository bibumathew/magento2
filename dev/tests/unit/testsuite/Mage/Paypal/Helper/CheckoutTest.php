<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Mage_Paypal_Helper_Checkout
 */
class Mage_Paypal_Helper_CheckoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Checkout_Model_Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var Mage_Sales_Model_QuoteFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_quoteFactory;

    /**
     * @var Mage_Paypal_Helper_Checkout
     */
    protected $_checkout;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_session = $this->getMockBuilder('Mage_Checkout_Model_Session')
            ->disableOriginalConstructor()
            ->setMethods(array('getLastRealOrder', 'replaceQuote', 'unsLastRealOrderId'))
            ->getMock();
        $this->_quoteFactory = $this->getMockBuilder('Mage_Sales_Model_QuoteFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $this->_checkout = new Mage_Paypal_Helper_Checkout($this->_session, $this->_quoteFactory);
    }

    /**
     * Get order mock
     *
     * @param bool $hasOrderId
     * @param array $mockMethods
     * @return Mage_Sales_Model_Order|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock($hasOrderId, $mockMethods = array())
    {
        $order = $this->getMockBuilder('Mage_Sales_Model_Order')
            ->disableOriginalConstructor()
            ->setMethods(array_merge(array('getId'), $mockMethods))
            ->getMock();
        $order->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($hasOrderId ? 'order id' : null));
        return $order;
    }

    /**
     * @param bool $hasOrderId
     * @param bool $isOrderCancelled
     * @param bool $expectedResult
     * @dataProvider cancelCurrentOrderDataProvider
     */
    public function testCancelCurrentOrder($hasOrderId, $isOrderCancelled, $expectedResult)
    {
        $comment = 'Some test comment';
        $order = $this->_getOrderMock($hasOrderId, array('registerCancellation', 'save'));
        $order->setData('state', $isOrderCancelled ? Mage_Sales_Model_Order::STATE_CANCELED : 'some another state');
        if ($expectedResult) {
            $order->expects($this->once())
                ->method('registerCancellation')
                ->with($this->equalTo($comment))
                ->will($this->returnSelf());
            $order->expects($this->once())
                ->method('save');
        } else {
            $order->expects($this->never())
                ->method('registerCancellation');
            $order->expects($this->never())
                ->method('save');
        }

        $this->_session->expects($this->any())
            ->method('getLastRealOrder')
            ->will($this->returnValue($order));
        $this->assertEquals($expectedResult, $this->_checkout->cancelCurrentOrder($comment));
    }

    /**
     * @return array
     */
    public function cancelCurrentOrderDataProvider()
    {
        return array(
            array(true, false, true),
            array(true, true, false),
            array(false, true, false),
            array(false, false, false),
        );
    }

    /**
     * @param bool $hasOrderId
     * @param bool $hasQuoteId
     * @dataProvider restoreQuoteDataProvider
     */
    public function testRestoreQuote($hasOrderId, $hasQuoteId)
    {
        $quote = $this->getMockBuilder('Mage_Sales_Model_Quote')
            ->disableOriginalConstructor()
            ->setMethods(array('getId', 'save', 'setIsActive', 'setReservedOrderId', 'load'))
            ->getMock();
        $order = $this->_getOrderMock($hasOrderId);
        $this->_session->expects($this->once())
            ->method('getLastRealOrder')
            ->will($this->returnValue($order));

        if ($hasOrderId) {
            $quoteId = 'quote id';
            $order->setQuoteId($quoteId);
            $this->_quoteFactory->expects($this->once())
                ->method('create')
                ->will($this->returnValue($quote));
            $quote->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($hasQuoteId ? 'some quote id' : null));
            $quote->expects($this->any())
                ->method('load')
                ->with($this->equalTo($quoteId))
                ->will($this->returnValue($quote));
            if ($hasQuoteId) {
                $quote->expects($this->once())
                    ->method('setIsActive')
                    ->with($this->equalTo(1))
                    ->will($this->returnSelf());
                $quote->expects($this->once())
                    ->method('setReservedOrderId')
                    ->with($this->isNull())
                    ->will($this->returnSelf());
                $quote->expects($this->once())
                    ->method('save');
                $this->_session->expects($this->once())
                    ->method('replaceQuote')
                    ->with($quote)
                    ->will($this->returnSelf());
            } else {
                $quote->expects($this->never())
                    ->method('setIsActive');
                $quote->expects($this->never())
                    ->method('setReservedOrderId');
                $quote->expects($this->never())
                    ->method('save');
            }
        }
        if ($hasOrderId && $hasQuoteId) {
            $this->_session->expects($this->once())
                ->method('unsLastRealOrderId');
        } else {
            $this->_session->expects($this->never())
                ->method('replaceQuote');
            $this->_session->expects($this->never())
                ->method('unsLastRealOrderId');
        }
        $result = $this->_checkout->restoreQuote();
        $this->assertEquals($result, $hasOrderId && $hasQuoteId);
    }

    /**
     * @return array
     */
    public function restoreQuoteDataProvider()
    {
        return array(
            array(true, true),
            array(true, false),
            array(false, true),
            array(false, false),
        );
    }
}
