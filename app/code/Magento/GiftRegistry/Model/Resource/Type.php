<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GiftRegistry
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Gift registry type data resource model
 *
 * @category    Magento
 * @package     Magento_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GiftRegistry\Model\Resource;

class Type extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Info table name
     *
     * @var string
     */
    protected $_infoTable;

    /**
     * Label table name
     *
     * @var string
     */
    protected $_labelTable;

    /**
     * Initialization. Set main entity table name and primary key field name.
     * Set label and info tables
     *
     */
    protected function _construct()
    {
        $this->_init('magento_giftregistry_type', 'type_id');
        $this->_infoTable  = $this->getTable('magento_giftregistry_type_info');
        $this->_labelTable = $this->getTable('magento_giftregistry_label');
    }

    /**
     * Add store date to registry type data
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Core\Model\Resource\Db\AbstractDb
     */
    protected function _afterLoad(\Magento\Core\Model\AbstractModel $object)
    {
        $adapter = $this->_getReadAdapter();

        $scopeCheckExpr = $adapter->getCheckSql(
            'store_id = 0',
            $adapter->quote('default'),
            $adapter->quote('store')
        );
        $storeIds       = array(\Magento\Core\Model\AppInterface::ADMIN_STORE_ID);
        if ($object->getStoreId()) {
            $storeIds[] = (int)$object->getStoreId();
        }
        $select = $adapter->select()
            ->from($this->_infoTable, array(
                'scope' => $scopeCheckExpr,
                'label',
                'is_listed',
                'sort_order'
            ))
            ->where('type_id = ?', (int)$object->getId())
            ->where('store_id IN (?)', $storeIds);

        $data = $adapter->fetchAssoc($select);

        if (isset($data['store']) && is_array($data['store'])) {
            foreach ($data['store'] as $key => $value) {
                $object->setData($key, ($value !== null) ? $value : $data['default'][$key]);
                $object->setData($key . '_store', $value);
            }
        } elseif (isset($data['default']) && is_array($data['default'])) {
            foreach ($data['default'] as $key => $value) {
                $object->setData($key, $value);
            }
        }

        return parent::_afterLoad($object);
    }

    /**
     * Save registry type per store view data
     *
     * @param \Magento\GiftRegistry\Model\Type $type
     * @return \Magento\GiftRegistry\Model\Resource\Type
     */
    public function saveTypeStoreData($type)
    {
        $this->_getWriteAdapter()->delete($this->_infoTable, array(
            'type_id = ?'  => (int)$type->getId(),
            'store_id = ?' => (int)$type->getStoreId()
        ));

        $this->_getWriteAdapter()->insert($this->_infoTable, array(
            'type_id'   => (int)$type->getId(),
            'store_id'  => (int)$type->getStoreId(),
            'label'     => $type->getLabel(),
            'is_listed' => (int)$type->getIsListed(),
            'sort_order'=> (int)$type->getSortOrder()
        ));

        return $this;
    }

    /**
     * Save store data
     *
     * @param \Magento\GiftRegistry\Model\Type $type
     * @param array $data
     * @param string $optionCode
     * @return \Magento\GiftRegistry\Model\Resource\Type
     */
    public function saveStoreData($type, $data, $optionCode = '')
    {
        $adapter = $this->_getWriteAdapter();
        if (isset($data['use_default'])) {
            $adapter->delete($this->_labelTable, array(
                'type_id = ?'        => (int)$type->getId(),
                'attribute_code = ?' => $data['code'],
                'store_id = ?'       => (int)$type->getStoreId(),
                'option_code = ?'    => $optionCode
            ));
        } else {
            $values = array(
                'type_id'        => (int)$type->getId(),
                'attribute_code' => $data['code'],
                'store_id'       => (int)$type->getStoreId(),
                'option_code'    => $optionCode,
                'label'          => $data['label']
            );
            $adapter->insertOnDuplicate($this->_labelTable, $values, array('label'));
        }

        return $this;
    }

    /**
     * Get attribute store data
     *
     * @param \Magento\GiftRegistry\Model\Type $type
     * @return null|array
     */
    public function getAttributesStoreData($type)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->_labelTable, array('attribute_code', 'option_code', 'label'))
            ->where('type_id = :type_id')
            ->where('store_id = :store_id');
        $bind = array(
            ':type_id'  => (int)$type->getId(),
            ':store_id' => (int)$type->getStoreId()
        );
        return $this->_getReadAdapter()->fetchAll($select, $bind);
    }

    /**
     * Delete attribute store data
     *
     * @param int $typeId
     * @param string $attributeCode
     * @param string $optionCode
     * @return \Magento\GiftRegistry\Model\Resource\Type
     */
    public function deleteAttributeStoreData($typeId, $attributeCode, $optionCode = null)
    {
        $where = array(
            'type_id = ?'        => (int)$typeId,
            'attribute_code = ?' => $attributeCode
        );

        if ($optionCode !== null) {
            $where['option_code = ?'] = $optionCode;
        }

        $this->_getWriteAdapter()->delete($this->_labelTable, $where);
        return $this;
    }

    /**
     * Delete attribute values
     *
     * @param int $typeId
     * @param string $attributeCode
     * @param bool $personValue
     * @return \Magento\GiftRegistry\Model\Resource\Type
     */
    public function deleteAttributeValues($typeId, $attributeCode, $personValue = false)
    {
        $entityTable = $this->getTable('magento_giftregistry_entity');
        $select      = $this->_getReadAdapter()->select();
        $select->from(array('e' => $entityTable), array('entity_id'))
            ->where('type_id = ?', (int)$typeId);

        if ($personValue) {
            $table = $this->getTable('magento_giftregistry_person');
        } else {
            $table = $this->getTable('magento_giftregistry_data');
        }

        $this->_getWriteAdapter()->update($table,
            array($attributeCode => new \Zend_Db_Expr('NULL')),
            array('entity_id IN (?)' => $select)
        );

        return $this;
    }
}
