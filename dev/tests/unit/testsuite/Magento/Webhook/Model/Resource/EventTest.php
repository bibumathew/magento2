<?php
/**
 * \Magento\Webhook\Model\Resource\Event
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Webhook\Model\Resource;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $tableName = 'webhook_event_table';
        $idFieldName = 'event_id';

        $resourceMock = $this->getMockBuilder('Magento\App\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('webhook_event')
            ->will($this->returnValue($tableName));

        $event = new \Magento\Webhook\Model\Resource\Event ($resourceMock);
        $this->assertEquals($tableName, $event->getMainTable() );
        $this->assertEquals($idFieldName, $event->getIdFieldName());
    }
}
