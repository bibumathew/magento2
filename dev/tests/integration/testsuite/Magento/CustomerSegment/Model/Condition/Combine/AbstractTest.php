<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_CustomerSegment_Model_Condition_Combine_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_CustomerSegment_Model_Segment_Condition_Combine_Root
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configShare;

    protected function setUp()
    {
        $this->_model = Magento_Test_Helper_Bootstrap::getObjectManager()
            ->create('Magento_CustomerSegment_Model_Segment_Condition_Combine_Root');
    }


    /**
     * @dataProvider limitByStoreWebsiteDataProvider
     * @param int $website
     */
    public function testLimitByStoreWebsite($website)
    {
        $select = $this->getMock('Zend_Db_Select', array('join', 'where'), array(), '', false);
        $select->expects($this->once())
            ->method('join')
            ->with(
                $this->arrayHasKey('store'),
                $this->equalTo('main.store_id=store.store_id'),
                $this->equalTo(array())
            )
            ->will($this->returnSelf());
        $select->expects($this->once())
            ->method('where')
            ->with($this->equalTo('store.website_id IN (?)'), $this->equalTo($website))
            ->will($this->returnSelf());

        $testMethod = new ReflectionMethod($this->_model, '_limitByStoreWebsite');
        $testMethod->setAccessible(true);

        $testMethod->invoke($this->_model, $select, $website, 'main.store_id');
    }

    public function limitByStoreWebsiteDataProvider()
    {
        return array(
            array(1),
            array(new Zend_Db_Expr(1)),
        );
    }
}
