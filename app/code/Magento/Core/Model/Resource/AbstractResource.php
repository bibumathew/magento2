<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Abstract resource model
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Resource;

abstract class AbstractResource
{
    /**
     * @var \Magento\DB\Adapter\AdapterInterface
     */
    protected $_writeAdapter;

    /**
     * Main constructor
     */
    public function __construct()
    {
        /**
         * Please override this one instead of overriding real __construct constructor
         */
        $this->_construct();
    }

    /**
     * Array of callbacks subscribed to commit transaction commit
     *
     * @var array
     */
    static protected $_commitCallbacks = array();

    /**
     * Resource initialization
     */
    abstract protected function _construct();

    /**
     * Retrieve connection for read data
     */
    abstract protected function _getReadAdapter();

    /**
     * Retrieve connection for write data
     */
    abstract protected function _getWriteAdapter();

    /**
     * Start resource transaction
     *
     * @return \Magento\Core\Model\Resource\AbstractResource
     */
    public function beginTransaction()
    {
        $this->_getWriteAdapter()->beginTransaction();
        return $this;
    }

    /**
     * Subscribe some callback to transaction commit
     *
     * @param callback $callback
     * @return \Magento\Core\Model\Resource\AbstractResource
     */
    public function addCommitCallback($callback)
    {
        $adapterKey = spl_object_hash($this->_getWriteAdapter());
        self::$_commitCallbacks[$adapterKey][] = $callback;
        return $this;
    }

    /**
     * Commit resource transaction
     *
     * @return \Magento\Core\Model\Resource\AbstractResource
     */
    public function commit()
    {
        $this->_getWriteAdapter()->commit();
        /**
         * Process after commit callbacks
         */
        if ($this->_getWriteAdapter()->getTransactionLevel() === 0) {
            $adapterKey = spl_object_hash($this->_getWriteAdapter());
            if (isset(self::$_commitCallbacks[$adapterKey])) {
                $callbacks = self::$_commitCallbacks[$adapterKey];
                self::$_commitCallbacks[$adapterKey] = array();
                foreach ($callbacks as $callback) {
                    call_user_func($callback);
                }
            }
        }
        return $this;
    }

    /**
     * Roll back resource transaction
     *
     * @return \Magento\Core\Model\Resource\AbstractResource
     */
    public function rollBack()
    {
        $this->_getWriteAdapter()->rollBack();
        return $this;
    }

    /**
     * Serialize specified field in an object
     *
     * @param \Magento\Object $object
     * @param string $field
     * @param mixed $defaultValue
     * @param bool $unsetEmpty
     * @return \Magento\Core\Model\Resource\AbstractResource
     */
    protected function _serializeField(\Magento\Object $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        $value = $object->getData($field);
        if (empty($value)) {
            if ($unsetEmpty) {
                $object->unsetData($field);
            } else {
                if (is_object($defaultValue) || is_array($defaultValue)) {
                    $defaultValue = serialize($defaultValue);
                }
                $object->setData($field, $defaultValue);
            }
        } elseif (is_array($value) || is_object($value)) {
            $object->setData($field, serialize($value));
        }

        return $this;
    }

    /**
     * Unserialize \Magento\Object field in an object
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @param string $field
     * @param mixed $defaultValue
     */
    protected function _unserializeField(\Magento\Object $object, $field, $defaultValue = null)
    {
        $value = $object->getData($field);
        if (empty($value)) {
            $object->setData($field, $defaultValue);
        } elseif (!is_array($value) && !is_object($value)) {
            $object->setData($field, unserialize($value));
        }
    }

    /**
     * Prepare data for passed table
     *
     * @param \Magento\Object $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(\Magento\Object $object, $table)
    {
        $data = array();
        $fields = $this->_getWriteAdapter()->describeTable($table);
        foreach (array_keys($fields) as $field) {
            if ($object->hasData($field)) {
                $fieldValue = $object->getData($field);
                if ($fieldValue instanceof \Zend_Db_Expr) {
                    $data[$field] = $fieldValue;
                } else {
                    if (null !== $fieldValue) {
                        $fieldValue   = $this->_prepareTableValueForSave($fieldValue, $fields[$field]['DATA_TYPE']);
                        $data[$field] = $this->_getWriteAdapter()->prepareColumnValue($fields[$field], $fieldValue);
                    } else if (!empty($fields[$field]['NULLABLE'])) {
                        $data[$field] = null;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function _prepareTableValueForSave($value, $type)
    {
        $type = strtolower($type);
        if ($type == 'decimal' || $type == 'numeric' || $type == 'float') {
            $value = \Magento\App\ObjectManager::getInstance()->get('Magento\Core\Model\LocaleInterface')
                ->getNumber($value);
        }
        return $value;
    }

    /**
     * Template method to return validate rules to be executed before entity is saved
     *
     * @return \Zend_Validate_Interface|null
     */
    public function getValidationRulesBeforeSave()
    {
        return null;
    }

    /**
     * Prepare the list of entity fields that should be selected from DB. Apply filtration based on active fieldset.
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @param string $tableName
     * @return array|string
     */
    protected function _getColumnsForEntityLoad(\Magento\Core\Model\AbstractModel $object, $tableName)
    {
        $fieldsetColumns = $object->getFieldset();
        if (!empty($fieldsetColumns)) {
            $readAdapter = $this->_getReadAdapter();
            if ($readAdapter instanceof \Magento\Db\Adapter\AdapterInterface) {
                $entityTableColumns = $readAdapter->describeTable($tableName);
                $columns = array_intersect($fieldsetColumns, array_keys($entityTableColumns));
            }
        }
        if (empty($columns)) {
            /** In case when fieldset was specified but no columns were matched with it, ID column is returned. */
            $columns = empty($fieldsetColumns) ? '*' : array($object->getIdFieldName());
        }
        return $columns;
    }
}
