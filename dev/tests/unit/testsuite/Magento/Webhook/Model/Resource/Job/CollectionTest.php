<?php
/**
 * \Magento\Webhook\Model\Resource\Job\Collection
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Webhook\Model\Resource\Job;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        /** @var \Magento\Event\ManagerInterface $eventManager */
        $eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        /** @var \Magento\Data\Collection\Db\FetchStrategyInterface $mockFetchStrategy */
        $mockFetchStrategy = $this->getMockBuilder('Magento\Data\Collection\Db\FetchStrategyInterface')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Core\Model\EntityFactory $entityFactory */
        $entityFactory = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $dateTime = new \Magento\Stdlib\DateTime;
        $mockDBAdapter = $this->getMockBuilder('Magento\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(array('_connect', '_quote', 'formatDate'))
            ->getMockForAbstractClass();
        $mockResourceEvent = $this->getMockBuilder('Magento\Webhook\Model\Resource\Job')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResourceEvent->expects($this->once())
            ->method('getReadConnection')
            ->will($this->returnValue($mockDBAdapter));
        $logger = $this->getMock('Magento\Logger', array(), array(), '', false);

        $collection = new \Magento\Webhook\Model\Resource\Job\Collection(
            $entityFactory, $logger, $mockFetchStrategy, $eventManager, $dateTime, null, $mockResourceEvent
        );
        $this->assertInstanceOf('Magento\Webhook\Model\Resource\Job\Collection', $collection);
        $this->assertEquals('Magento\Webhook\Model\Resource\Job', $collection->getResourceModelName());
    }
}
