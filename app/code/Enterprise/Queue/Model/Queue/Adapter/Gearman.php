<?php
/**
 * Queue gearman Adapter
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Enterprise_Queue_Model_Queue_Adapter_Gearman implements Enterprise_Queue_Model_Queue_AdapterInterface
{
    /**
     * @var GearmanClient
     */
    protected $_client;

    /**
     * @var Enterprise_Queue_Helper_Gearman
     */
    protected $_helperGearman;

    /**
     * @param GearmanClient $client
     * @param Enterprise_Queue_Helper_Gearman $helperGearman
     */
    public function __construct(
        GearmanClient $client,
        Enterprise_Queue_Helper_Gearman $helperGearman
    ) {
        $this->_client = $client;
        $this->_helperGearman = $helperGearman;
        $this->_client->addServers($this->_helperGearman->getServers());
    }

    /**
     * Add task to queue
     *
     * @param string $eventName
     * @param array $data
     * @param string|null $priority
     * @return Enterprise_Queue_Model_Queue_AdapterInterface
     */
    public function addTask($eventName, $data, $priority = null)
    {
        $this->_client->doBackground($eventName, $this->_helperGearman->encodeData($data));

        return $this;
    }
}
