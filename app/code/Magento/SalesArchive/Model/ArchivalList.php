<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_SalesArchive
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Sales archival model list
 */
namespace Magento\SalesArchive\Model;

class ArchivalList
{
    /**
     * Archival entity names
     */
    const ORDER      = 'order';
    const INVOICE    = 'invoice';
    const SHIPMENT   = 'shipment';
    const CREDITMEMO = 'creditmemo';

    /**
     * Archival entities definition
     *
     * @var $_entities array
     */
    protected $_entities = array(
        self::ORDER => array(
            'model' => 'Magento\Sales\Model\Order',
            'resource_model' => 'Magento\Sales\Model\Resource\Order'
        ),
        self::INVOICE => array(
            'model' => 'Magento\Sales\Model\Order\Invoice',
            'resource_model' => 'Magento\Sales\Model\Resource\Order\Invoice'
        ),
        self::SHIPMENT  => array(
            'model' => 'Magento\Sales\Model\Order\Shipment',
            'resource_model' => 'Magento\Sales\Model\Resource\Order\Shipment'
        ),
        self::CREDITMEMO => array(
            'model' => 'Magento\Sales\Model\Order\Creditmemo',
            'resource_model' => 'Magento\Sales\Model\Resource\Order\Creditmemo'
        )
    );

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get archival resource model singleton
     *
     * @param string $entity
     * @param array $arguments
     * @return \Magento\Sales\Model\Resource\Order\AbstractOrder
     * @throws \LogicException
     */
    public function getResource($entity, array $arguments = array())
    {
        $className = $this->_getClassByEntity($entity);

        if ($className === false) {
            throw new \LogicException(
                $entity . ' entity isn\'t allowed'
            );
        }
        $model = $this->_objectManager->get($className, $arguments);
        return $model;
    }

    /**
     * Returns resource model class of an entity
     *
     * @param string $entity
     * @return string|bool
     */
    protected function _getClassByEntity($entity)
    {
        return isset($this->_entities[$entity]) ? $this->_entities[$entity]['resource_model'] : false;
    }

    /**
     * Return entity by object
     *
     * @param \Magento\Object $object
     * @return string|boolean
     */
    public function getEntityByObject($object)
    {
        $keys = array('model', 'resource_model');
        foreach ($this->_entities as $archiveEntity => $entityClasses) {
            foreach ($keys as $key) {
                $className = $entityClasses[$key];
                if ($object instanceof $className) {
                    return $archiveEntity;
                }
            }
        }
        return false;
    }
}
