<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * CatalogSearch Fulltext Index resource model
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_CatalogSearch_Model_Resource_Fulltext extends Magento_Core_Model_Resource_Db_Abstract
{
    /**
     * Searchable attributes cache
     *
     * @var array
     */
    protected $_searchableAttributes     = null;

    /**
     * Index values separator
     *
     * @var string
     */
    protected $_separator                = '|';

    /**
     * Array of Zend_Date objects per store
     *
     * @var array
     */
    protected $_dates                    = array();

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $_productTypes             = array();

    /**
     * Product Emulators cache
     *
     * @var array
     */
    protected $_productEmulators         = array();

    /**
     * Store search engine instance
     *
     * @var Magento_CatalogSearch_Model_Resource_EngineInterface
     */
    protected $_engine;

    /**
     * @var Magento_Catalog_Model_Resource_Product_Attribute_CollectionFactory
     */
    protected $_productAttributeCollFactory;

    /**
     * Catalog product status
     *
     * @var Magento_Catalog_Model_Product_Status
     */
    protected $_catalogProductStatus;

    /**
     * Eav config
     *
     * @var Magento_Eav_Model_Config
     */
    protected $_eavConfig;

    /**
     * Catalog product type
     *
     * @var Magento_Catalog_Model_Product_Type
     */
    protected $_catalogProductType;

    /**
     * Catalog search data
     *
     * @var Magento_CatalogSearch_Helper_Data
     */
    protected $_catalogSearchData;

    /**
     * Core string
     *
     * @var Magento_Core_Helper_String
     */
    protected $_coreString;

    /**
     * Core event manager proxy
     *
     * @var Magento_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * Core store config
     *
     * @var Magento_Core_Model_Store_ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * Store manager
     *
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CatalogSearch resource helper
     *
     * @var Magento_CatalogSearch_Model_Resource_Helper_Mysql4
     */
    protected $_resourceHelper;

    /**
     * Construct
     *
     * @param Magento_Core_Model_Resource $resource
     * @param Magento_Catalog_Model_Product_Type $catalogProductType
     * @param Magento_Eav_Model_Config $eavConfig
     * @param Magento_Catalog_Model_Product_Status $catalogProductStatus
     * @param Magento_Catalog_Model_Resource_Product_Attribute_CollectionFactory $productAttributeCollFactory
     * @param Magento_CatalogSearch_Model_Resource_EngineProvider $engineProvider
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Core_Helper_String $coreString
     * @param Magento_CatalogSearch_Helper_Data $catalogSearchData
     * @param Magento_Core_Model_Store_ConfigInterface $coreStoreConfig
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_CatalogSearch_Model_Resource_Helper_Mysql4 $resourceHelper
     */
    public function __construct(
        Magento_Core_Model_Resource $resource,
        Magento_Catalog_Model_Product_Type $catalogProductType,
        Magento_Eav_Model_Config $eavConfig,
        Magento_Catalog_Model_Product_Status $catalogProductStatus,
        Magento_Catalog_Model_Resource_Product_Attribute_CollectionFactory $productAttributeCollFactory,
        Magento_CatalogSearch_Model_Resource_EngineProvider $engineProvider,
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Core_Helper_String $coreString,
        Magento_CatalogSearch_Helper_Data $catalogSearchData,
        Magento_Core_Model_Store_ConfigInterface $coreStoreConfig,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_CatalogSearch_Model_Resource_Helper_Mysql4 $resourceHelper
    ) {
        $this->_catalogProductType = $catalogProductType;
        $this->_eavConfig = $eavConfig;
        $this->_catalogProductStatus = $catalogProductStatus;
        $this->_productAttributeCollFactory = $productAttributeCollFactory;
        $this->_engine = $engineProvider->get();
        $this->_eventManager = $eventManager;
        $this->_coreString = $coreString;
        $this->_catalogSearchData = $catalogSearchData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($resource);
    }

    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_fulltext', 'product_id');
    }

    /**
     * Return options separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Regenerate search index for store(s)
     *
     * @param  int|null $storeId
     * @param  int|array|null $productIds
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    public function rebuildIndex($storeId = null, $productIds = null)
    {
        if (is_null($storeId)) {
            $storeIds = array_keys($this->_storeManager->getStores());
            foreach ($storeIds as $storeId) {
                $this->_rebuildStoreIndex($storeId, $productIds);
            }
        } else {
            $this->_rebuildStoreIndex($storeId, $productIds);
        }

        return $this;
    }

    /**
     * Regenerate search index for specific store
     *
     * @param int $storeId Store View Id
     * @param int|array $productIds Product Entity Id
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _rebuildStoreIndex($storeId, $productIds = null)
    {
        $this->cleanIndex($storeId, $productIds);

        // prepare searchable attributes
        $staticFields = array();
        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        $dynamicFields = array(
            'int'       => array_keys($this->_getSearchableAttributes('int')),
            'varchar'   => array_keys($this->_getSearchableAttributes('varchar')),
            'text'      => array_keys($this->_getSearchableAttributes('text')),
            'decimal'   => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime'  => array_keys($this->_getSearchableAttributes('datetime')),
        );

        // status and visibility filter
        $visibility     = $this->_getSearchableAttribute('visibility');
        $status         = $this->_getSearchableAttribute('status');
        $statusVals     = $this->_catalogProductStatus->getVisibleStatusIds();
        $allowedVisibility = $this->_engine->getAllowedVisibility();

        $lastProductId = 0;
        while (true) {
            $products = $this->_getSearchableProducts($storeId, $staticFields, $productIds, $lastProductId);
            if (!$products) {
                break;
            }

            $productAttributes = array();
            $productRelations  = array();
            foreach ($products as $productData) {
                $lastProductId = $productData['entity_id'];
                $productAttributes[$productData['entity_id']] = $productData['entity_id'];
                $productChildren = $this->_getProductChildIds($productData['entity_id'], $productData['type_id']);
                $productRelations[$productData['entity_id']] = $productChildren;
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        $productAttributes[$productChildId] = $productChildId;
                    }
                }
            }

            $productIndexes    = array();
            $productAttributes = $this->_getProductAttributes($storeId, $productAttributes, $dynamicFields);
            foreach ($products as $productData) {
                if (!isset($productAttributes[$productData['entity_id']])) {
                    continue;
                }

                $productAttr = $productAttributes[$productData['entity_id']];
                if (!isset($productAttr[$visibility->getId()])
                    || !in_array($productAttr[$visibility->getId()], $allowedVisibility)
                ) {
                    continue;
                }
                if (!isset($productAttr[$status->getId()]) || !in_array($productAttr[$status->getId()], $statusVals)) {
                    continue;
                }

                $productIndex = array(
                    $productData['entity_id'] => $productAttr
                );

                $productChildren = $productRelations[$productData['entity_id']];
                if ($productChildren) {
                    foreach ($productChildren as $productChildId) {
                        if (isset($productAttributes[$productChildId])) {
                            $productIndex[$productChildId] = $productAttributes[$productChildId];
                        }
                    }
                }

                $index = $this->_prepareProductIndex($productIndex, $productData, $storeId);

                $productIndexes[$productData['entity_id']] = $index;
            }

            $this->_saveProductIndexes($storeId, $productIndexes);
        }

        // Reset only product-specific queries and results.
        $this->resetSearchResults($storeId, $productIds);

        return $this;
    }

    /**
     * Retrieve searchable products per store
     *
     * @param int $storeId
     * @param array $staticFields
     * @param array|int $productIds
     * @param int $lastProductId
     * @param int $limit
     * @return array
     */
    protected function _getSearchableProducts($storeId, array $staticFields, $productIds = null, $lastProductId = 0,
        $limit = 100
    ) {
        $websiteId      = $this->_storeManager->getStore($storeId)->getWebsiteId();
        $writeAdapter   = $this->_getWriteAdapter();

        $select = $writeAdapter->select()
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->getTable('catalog_product_entity')),
                array_merge(array('entity_id', 'type_id'), $staticFields)
            )
            ->join(
                array('website' => $this->getTable('catalog_product_website')),
                $writeAdapter->quoteInto(
                    'website.product_id=e.entity_id AND website.website_id=?',
                    $websiteId
                ),
                array()
            )
            ->join(
                array('stock_status' => $this->getTable('cataloginventory_stock_status')),
                $writeAdapter->quoteInto(
                    'stock_status.product_id=e.entity_id AND stock_status.website_id=?',
                    $websiteId
                ),
                array('in_stock' => 'stock_status')
            );

        if (!is_null($productIds)) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $select->where('e.entity_id>?', $lastProductId)
            ->limit($limit)
            ->order('e.entity_id');

        $result = $writeAdapter->fetchAll($select);

        return $result;
    }

    /**
     * Reset search results
     *
     * @param null|int $storeId
     * @param null|array $productIds
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    public function resetSearchResults($storeId = null, $productIds = null)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->update($this->getTable('catalogsearch_query'), array('is_processed' => 0));

        if ($storeId === null && $productIds === null) {
            // Keeping public interface
            $adapter->update($this->getTable('catalogsearch_query'), array('is_processed' => 0));
            $adapter->truncateTable($this->getTable('catalogsearch_result'));
            $this->_eventManager->dispatch('catalogsearch_reset_search_result');
        } else {
            // Optimized deletion only product-related records
            /** @var $select Magento_DB_Select */
            $select  = $adapter->select()
                ->from(array('r' => $this->getTable('catalogsearch_result')), null)
                ->join(
                    array('q' => $this->getTable('catalogsearch_query')),
                    'q.query_id=r.query_id',
                    array()
                )
                ->join(
                    array('res' => $this->getTable('catalogsearch_result')),
                    'q.query_id=res.query_id',
                    array()
            );
            if (!empty($storeId)) {
                $select->where('q.store_id = ?', $storeId);
            }
            if (!empty($productIds)) {
                $select->where('r.product_id IN(?)', $productIds);
            }
            $query = $select->deleteFromSelect('res');
            $adapter->query($query);

            /** @var $select Magento_DB_Select */
            $select  = $adapter->select();
            $subSelect = $adapter->select()->from(array('res' => $this->getTable('catalogsearch_result')), null);
            $select->exists($subSelect, 'res.query_id=' . $this->getTable('catalogsearch_query') . '.query_id', false);

            $adapter->update(
                $this->getTable('catalogsearch_query'),
                array('is_processed' => 0),
                $select->getPart(Zend_Db_Select::WHERE)
            );
        }

        return $this;
    }

    /**
     * Delete search index data for store
     *
     * @param int $storeId Store View Id
     * @param int $productId Product Entity Id
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    public function cleanIndex($storeId = null, $productId = null)
    {
        if ($this->_engine) {
            $this->_engine->cleanIndex($storeId, $productId);
        }

        return $this;
    }

    /**
     * Prepare results for query
     *
     * @param Magento_CatalogSearch_Model_Fulltext $object
     * @param string $queryText
     * @param Magento_CatalogSearch_Model_Query $query
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    public function prepareResult($object, $queryText, $query)
    {
        $adapter = $this->_getWriteAdapter();
        if (!$query->getIsProcessed()) {
            $searchType = $object->getSearchType($query->getStoreId());

            $bind = array();
            $like = array();
            $likeCond  = '';
            if ($searchType == Magento_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE
                || $searchType == Magento_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE
            ) {
                $words = $this->_coreString
                    ->splitWords($queryText, true, $query->getMaxQueryWords());
                foreach ($words as $word) {
                    $like[] = $this->_resourceHelper->getCILike('s.data_index', $word, array('position' => 'any'));
                }
                if ($like) {
                    $likeCond = '(' . join(' OR ', $like) . ')';
                }
            }
            $mainTableAlias = 's';
            $fields = array(
                'query_id' => new Zend_Db_Expr($query->getId()),
                'product_id',
            );
            $select = $adapter->select()
                ->from(array($mainTableAlias => $this->getMainTable()), $fields)
                ->joinInner(array('e' => $this->getTable('catalog_product_entity')),
                    'e.entity_id = s.product_id',
                    array())
                ->where($mainTableAlias.'.store_id = ?', (int)$query->getStoreId());

            $where = '';
            if ($searchType == Magento_CatalogSearch_Model_Fulltext::SEARCH_TYPE_FULLTEXT
                || $searchType == Magento_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE
            ) {
                $preparedTerms = $this->_resourceHelper->prepareTerms($queryText, $query->getMaxQueryWords());
                $bind[':query'] = implode(' ', $preparedTerms[0]);
                $where = $this->_resourceHelper->chooseFulltext($this->getMainTable(), $mainTableAlias, $select);
            }

            if ($likeCond != '' && $searchType == Magento_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE) {
                    $where .= ($where ? ' OR ' : '') . $likeCond;
            } elseif ($likeCond != '' && $searchType == Magento_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE) {
                $select->columns(array('relevance'  => new Zend_Db_Expr(0)));
                $where = $likeCond;
            }

            if ($where != '') {
                $select->where($where);
            }

            $sql = $adapter->insertFromSelect($select,
                $this->getTable('catalogsearch_result'),
                array(),
                Magento_DB_Adapter_Interface::INSERT_ON_DUPLICATE);
            $adapter->query($sql, $bind);

            $query->setIsProcessed(1);
        }

        return $this;
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return Magento_Eav_Model_Config
     */
    public function getEavConfig()
    {
        return $this->_eavConfig;
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     * @return array
     */
    protected function _getSearchableAttributes($backendType = null)
    {
        if (null === $this->_searchableAttributes) {
            $this->_searchableAttributes = array();

            $productAttributes = $this->_productAttributeCollFactory->create();

            if ($this->_engine && $this->_engine->allowAdvancedIndex()) {
                $productAttributes->addToIndexFilter(true);
            } else {
                $productAttributes->addSearchableAttributeFilter();
            }
            $attributes = $productAttributes->getItems();

            $this->_eventManager->dispatch('catelogsearch_searchable_attributes_load_after', array(
                'engine' => $this->_engine,
                'attributes' => $attributes
            ));

            $entity = $this->getEavConfig()
                ->getEntityType(Magento_Catalog_Model_Product::ENTITY)
                ->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->_searchableAttributes = $attributes;
        }

        if (!is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchableAttributes as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
        }

        return $this->_searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     * @return Magento_Eav_Model_Entity_Attribute
     */
    protected function _getSearchableAttribute($attribute)
    {
        $attributes = $this->_getSearchableAttributes();
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
        }

        return $this->getEavConfig()->getAttribute(Magento_Catalog_Model_Product::ENTITY, $attribute);
    }

    /**
     * Returns expression for field unification
     *
     * @param string $field
     * @param string $backendType
     * @return Zend_Db_Expr
     */
    protected function _unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->_getReadAdapter()->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }

    /**
     * Load product(s) attributes
     *
     * @param int $storeId
     * @param array $productIds
     * @param array $attributeTypes
     * @return array
     */
    protected function _getProductAttributes($storeId, array $productIds, array $attributeTypes)
    {
        $result  = array();
        $selects = array();
        $adapter = $this->_getWriteAdapter();
        $ifStoreValue = $adapter->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable('catalog_product_entity_' . $backendType);
                $selects[] = $adapter->select()
                    ->from(
                        array('t_default' => $tableName),
                        array('entity_id', 'attribute_id')
                    )->joinLeft(
                        array('t_store' => $tableName),
                        $adapter->quoteInto(
                            't_default.entity_id=t_store.entity_id' .
                                ' AND t_default.attribute_id=t_store.attribute_id' .
                                ' AND t_store.store_id=?',
                            $storeId),
                        array('value' => $this->_unifyField($ifStoreValue, $backendType))
                    )->where('t_default.store_id=?', 0)
                    ->where('t_default.attribute_id IN (?)', $attributeIds)
                    ->where('t_default.entity_id IN (?)', $productIds);
            }
        }

        if ($selects) {
            $select = $adapter->select()->union($selects, Zend_Db_Select::SQL_UNION_ALL);
            $query = $adapter->query($select);
            while ($row = $query->fetch()) {
                $result[$row['entity_id']][$row['attribute_id']] = $row['value'];
            }
        }

        return $result;
    }

    /**
     * Retrieve Product Type Instance
     *
     * @param string $typeId
     * @return Magento_Catalog_Model_Product_Type_Abstract
     */
    protected function _getProductTypeInstance($typeId)
    {
        if (!isset($this->_productTypes[$typeId])) {
            $productEmulator = $this->_getProductEmulator($typeId);

            $this->_productTypes[$typeId] = $this->_catalogProductType->factory($productEmulator);
        }
        return $this->_productTypes[$typeId];
    }

    /**
     * Return all product children ids
     *
     * @param int $productId Product Entity Id
     * @param string $typeId Super Product Link Type
     * @return array
     */
    protected function _getProductChildIds($productId, $typeId)
    {
        $typeInstance = $this->_getProductTypeInstance($typeId);
        $relation = $typeInstance->isComposite($this->_getProductEmulator($typeId))
            ? $typeInstance->getRelationInfo()
            : false;

        if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()) {
            $select = $this->_getReadAdapter()->select()
                ->from(
                    array('main' => $this->getTable($relation->getTable())),
                    array($relation->getChildFieldName())
                )->where($relation->getParentFieldName() . '=?', $productId);
            if (!is_null($relation->getWhere())) {
                $select->where($relation->getWhere());
            }
            return $this->_getReadAdapter()->fetchCol($select);
        }

        return null;
    }

    /**
     * Retrieve Product Emulator (Magento Object)
     *
     * @param string $typeId
     * @return Magento_Object
     */
    protected function _getProductEmulator($typeId)
    {
        if (!isset($this->_productEmulators[$typeId])) {
            $productEmulator = new Magento_Object();
            $productEmulator->setIdFieldName('entity_id')
                ->setTypeId($typeId);
            $this->_productEmulators[$typeId] = $productEmulator;
        }
        return $this->_productEmulators[$typeId];
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return string
     */
    protected function _prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                $value = $this->_getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    //For grouped products
                    if (isset($index[$attributeCode])) {
                        if (!is_array($index[$attributeCode])) {
                            $index[$attributeCode] = array($index[$attributeCode]);
                        }
                        $index[$attributeCode][] = $value;
                    } else {
                        //For other types of products
                        $index[$attributeCode] = $value;
                    }
                }
            }
        }

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {
                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!is_null($value) && $value !== false) {
                    $attributeCode = $this->_getSearchableAttribute($attributeId)->getAttributeCode();

                    if (isset($index[$attributeCode])) {
                        $index[$attributeCode][$entityId] = $value;
                    } else {
                        $index[$attributeCode] = array($entityId => $value);
                    }
                }
            }
        }

        if (!$this->_engine->allowAdvancedIndex()) {
            $product = $this->_getProductEmulator($productData['type_id'])
                ->setId($productData['entity_id'])
                ->setStoreId($storeId);
            $typeInstance = $this->_getProductTypeInstance($productData['type_id']);
            $data = $typeInstance->getSearchableData($product);
            if ($data) {
                $index['options'] = $data;
            }
        }

        if (isset($productData['in_stock'])) {
            $index['in_stock'] = $productData['in_stock'];
        }

        if ($this->_engine) {
            return $this->_engine->prepareEntityIndex($index, $this->_separator);
        }

        return $this->_catalogSearchData->prepareIndexdata($index, $this->_separator);
    }

    /**
     * Retrieve attribute source value for search
     *
     * @param int $attributeId
     * @param mixed $value
     * @param int $storeId
     * @return mixed
     */
    protected function _getAttributeValue($attributeId, $value, $storeId)
    {
        $attribute = $this->_getSearchableAttribute($attributeId);
        if (!$attribute->getIsSearchable()) {
            if ($this->_engine->allowAdvancedIndex()) {
                if ($attribute->getAttributeCode() == 'visibility') {
                    return $value;
                } elseif (!($attribute->getIsVisibleInAdvancedSearch()
                    || $attribute->getIsFilterable()
                    || $attribute->getIsFilterableInSearch()
                    || $attribute->getUsedForSortBy())
                ) {
                    return null;
                }
            } else {
                return null;
            }
        }

        if ($attribute->usesSource()) {
            if ($this->_engine->allowAdvancedIndex()) {
                return $value;
            }

            $attribute->setStoreId($storeId);
            $value = $attribute->getSource()->getIndexOptionText($value);

            if (is_array($value)) {
                $value = implode($this->_separator, $value);
            } elseif (empty($value)) {
                $inputType = $attribute->getFrontend()->getInputType();
                if ($inputType == 'select' || $inputType == 'multiselect') {
                    return null;
                }
            }
        } elseif ($attribute->getBackendType() == 'datetime') {
            $value = $this->_getStoreDate($storeId, $value);
        } else {
            $inputType = $attribute->getFrontend()->getInputType();
            if ($inputType == 'price') {
                $value = $this->_storeManager->getStore($storeId)->roundPrice($value);
            }
        }

        $value = preg_replace("#\s+#siu", ' ', trim(strip_tags($value)));

        return $value;
    }

    /**
     * Save Product index
     *
     * @param int $productId
     * @param int $storeId
     * @param string $index
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _saveProductIndex($productId, $storeId, $index)
    {
        if ($this->_engine) {
            $this->_engine->saveEntityIndex($productId, $storeId, $index);
        }

        return $this;
    }

    /**
     * Save Multiply Product indexes
     *
     * @param int $storeId
     * @param array $productIndexes
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _saveProductIndexes($storeId, $productIndexes)
    {
        if ($this->_engine) {
            $this->_engine->saveEntityIndexes($storeId, $productIndexes);
        }

        return $this;
    }

    /**
     * Retrieve Date value for store
     *
     * @param int $storeId
     * @param string $date
     * @return string
     */
    protected function _getStoreDate($storeId, $date = null)
    {
        if (!isset($this->_dates[$storeId])) {
            $timezone = $this->_coreStoreConfig->getConfig(Magento_Core_Model_LocaleInterface::XML_PATH_DEFAULT_TIMEZONE, $storeId);
            $locale   = $this->_coreStoreConfig->getConfig(Magento_Core_Model_LocaleInterface::XML_PATH_DEFAULT_LOCALE, $storeId);
            $locale   = new Zend_Locale($locale);

            $dateObj = new Zend_Date(null, null, $locale);
            $dateObj->setTimezone($timezone);
            $this->_dates[$storeId] = array($dateObj, $locale->getTranslation(null, 'date', $locale));
        }

        if (!is_empty_date($date)) {
            list($dateObj, $format) = $this->_dates[$storeId];
            $dateObj->setDate($date, Magento_Date::DATETIME_INTERNAL_FORMAT);

            return $dateObj->toString($format);
        }

        return null;
    }

    // Deprecated methods
    /**
     * Update category products indexes
     *
     * deprecated after 1.6.2.0
     *
     * @param array $productIds
     * @param array $categoryIds
     * @return Magento_CatalogSearch_Model_Resource_Fulltext
     */
    public function updateCategoryIndex($productIds, $categoryIds)
    {
        return $this;
    }
}
