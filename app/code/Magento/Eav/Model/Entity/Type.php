<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Eav
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Entity type model
 *
 * @method \Magento\Eav\Model\Resource\Entity\Type _getResource()
 * @method \Magento\Eav\Model\Resource\Entity\Type getResource()
 * @method \Magento\Eav\Model\Entity\Type setEntityTypeCode(string $value)
 * @method string getEntityModel()
 * @method \Magento\Eav\Model\Entity\Type setEntityModel(string $value)
 * @method \Magento\Eav\Model\Entity\Type setAttributeModel(string $value)
 * @method \Magento\Eav\Model\Entity\Type setEntityTable(string $value)
 * @method \Magento\Eav\Model\Entity\Type setValueTablePrefix(string $value)
 * @method \Magento\Eav\Model\Entity\Type setEntityIdField(string $value)
 * @method int getIsDataSharing()
 * @method \Magento\Eav\Model\Entity\Type setIsDataSharing(int $value)
 * @method string getDataSharingKey()
 * @method \Magento\Eav\Model\Entity\Type setDataSharingKey(string $value)
 * @method \Magento\Eav\Model\Entity\Type setDefaultAttributeSetId(int $value)
 * @method string getIncrementModel()
 * @method \Magento\Eav\Model\Entity\Type setIncrementModel(string $value)
 * @method int getIncrementPerStore()
 * @method \Magento\Eav\Model\Entity\Type setIncrementPerStore(int $value)
 * @method int getIncrementPadLength()
 * @method \Magento\Eav\Model\Entity\Type setIncrementPadLength(int $value)
 * @method string getIncrementPadChar()
 * @method \Magento\Eav\Model\Entity\Type setIncrementPadChar(string $value)
 * @method string getAdditionalAttributeTable()
 * @method \Magento\Eav\Model\Entity\Type setAdditionalAttributeTable(string $value)
 * @method \Magento\Eav\Model\Entity\Type setEntityAttributeCollection(string $value)
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Entity;

class Type extends \Magento\Core\Model\AbstractModel
{
    /**
     * Collection of attributes
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Collection
     */
    protected $_attributes;

    /**
     * Array of attributes
     *
     * @var array
     */
    protected $_attributesBySet             = array();

    /**
     * Collection of sets
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection
     */
    protected $_sets;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Entity\Type');
    }

    /**
     * Load type by code
     *
     * @param string $code
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function loadByCode($code)
    {
        $this->_getResource()->loadByCode($this, $code);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Retrieve entity type attributes collection
     *
     * @param   int $setId
     * @return  \Magento\Eav\Model\Resource\Entity\Attribute\Collection
     */
    public function getAttributeCollection($setId = null)
    {
        if ($setId === null) {
            if ($this->_attributes === null) {
                $this->_attributes = $this->_getAttributeCollection()
                    ->setEntityTypeFilter($this);
            }
            $collection = $this->_attributes;
        } else {
            if (!isset($this->_attributesBySet[$setId])) {
                $this->_attributesBySet[$setId] = $this->_getAttributeCollection()
                    ->setEntityTypeFilter($this)
                    ->setAttributeSetFilter($setId);
            }
            $collection = $this->_attributesBySet[$setId];
        }

        return $collection;
    }

    /**
     * Init and retreive attribute collection
     *
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Collection
     */
    protected function _getAttributeCollection()
    {
        $collection = \Mage::getModel('Magento\Eav\Model\Entity\Attribute')->getCollection();
        $objectsModel = $this->getAttributeModel();
        if ($objectsModel) {
            $collection->setModel($objectsModel);
        }

        return $collection;
    }

    /**
     * Retrieve entity tpe sets collection
     *
     * @return \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection
     */
    public function getAttributeSetCollection()
    {
        if (empty($this->_sets)) {
            $this->_sets = \Mage::getModel('Magento\Eav\Model\Entity\Attribute\Set')->getResourceCollection()
                ->setEntityTypeFilter($this->getId());
        }
        return $this->_sets;
    }

    /**
     * Retreive new incrementId
     *
     * @param int $storeId
     * @return string
     */
    public function fetchNewIncrementId($storeId = null)
    {
        if (!$this->getIncrementModel()) {
            return false;
        }

        if (!$this->getIncrementPerStore() || ($storeId === null)) {
            /**
             * store_id null we can have for entity from removed store
             */
            $storeId = 0;
        }

        // Start transaction to run SELECT ... FOR UPDATE
        $this->_getResource()->beginTransaction();

        $entityStoreConfig = \Mage::getModel('Magento\Eav\Model\Entity\Store')
            ->loadByEntityStore($this->getId(), $storeId);

        if (!$entityStoreConfig->getId()) {
            $entityStoreConfig
                ->setEntityTypeId($this->getId())
                ->setStoreId($storeId)
                ->setIncrementPrefix($storeId)
                ->save();
        }

        $incrementInstance = \Mage::getModel($this->getIncrementModel())
            ->setPrefix($entityStoreConfig->getIncrementPrefix())
            ->setPadLength($this->getIncrementPadLength())
            ->setPadChar($this->getIncrementPadChar())
            ->setLastId($entityStoreConfig->getIncrementLastId())
            ->setEntityTypeId($entityStoreConfig->getEntityTypeId())
            ->setStoreId($entityStoreConfig->getStoreId());

        /**
         * do read lock on eav/entity_store to solve potential timing issues
         * (most probably already done by beginTransaction of entity save)
         */
        $incrementId = $incrementInstance->getNextId();
        $entityStoreConfig->setIncrementLastId($incrementId);
        $entityStoreConfig->save();

        // Commit increment_last_id changes
        $this->_getResource()->commit();

        return $incrementId;
    }

    /**
     * Retreive entity id field
     *
     * @return string|null
     */
    public function getEntityIdField()
    {
        return isset($this->_data['entity_id_field']) ? $this->_data['entity_id_field'] : null;
    }

    /**
     * Retreive entity table name
     *
     * @return string|null
     */
    public function getEntityTable()
    {
        return isset($this->_data['entity_table']) ? $this->_data['entity_table'] : null;
    }

    /**
     * Retrieve entity table prefix name
     *
     * @return string
     */
    public function getValueTablePrefix()
    {
        $prefix = $this->getEntityTablePrefix();
        if ($prefix) {
            return $this->getResource()->getTable($prefix);
        }

        return null;
    }

    /**
     * Retrieve entity table prefix
     *
     * @return string
     */
    public function getEntityTablePrefix()
    {
        $tablePrefix = trim($this->_data['value_table_prefix']);

        if (empty($tablePrefix)) {
            $tablePrefix = $this->getEntityTable();
        }

        return $tablePrefix;
    }

    /**
     * Get default attribute set identifier for etity type
     *
     * @return string|null
     */
    public function getDefaultAttributeSetId()
    {
        return isset($this->_data['default_attribute_set_id']) ? $this->_data['default_attribute_set_id'] : null;
    }

    /**
     * Retreive entity type id
     *
     * @return string|null
     */
    public function getEntityTypeId()
    {
        return isset($this->_data['entity_type_id']) ? $this->_data['entity_type_id'] : null;
    }

    /**
     * Retreive entity type code
     *
     * @return string|null
     */
    public function getEntityTypeCode()
    {
        return isset($this->_data['entity_type_code']) ? $this->_data['entity_type_code'] : null;
    }

    /**
     * Retreive attribute codes
     *
     * @return array|null
     */
    public function getAttributeCodes()
    {
        return isset($this->_data['attribute_codes']) ? $this->_data['attribute_codes'] : null;
    }

    /**
     * Get attribute model code for entity type
     *
     * @return string
     */
    public function getAttributeModel()
    {
        if (empty($this->_data['attribute_model'])) {
            return \Magento\Eav\Model\Entity::DEFAULT_ATTRIBUTE_MODEL;
        }

        return $this->_data['attribute_model'];
    }

    /**
     * Retreive resource entity object
     *
     * @return \Magento\Core\Model\Resource\AbstractResource
     */
    public function getEntity()
    {
        return \Mage::getResourceSingleton($this->_data['entity_model']);
    }

    /**
     * Return attribute collection. If not specify return default
     *
     * @return string
     */
    public function getEntityAttributeCollection()
    {
        $collection = $this->_getData('entity_attribute_collection');
        if ($collection) {
            return $collection;
        }
        return '\Magento\Eav\Model\Resource\Entity\Attribute\Collection';
    }
}
