<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Import entity grouped product type model
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_ImportExport_Model_Import_Entity_Product_Type_Grouped
    extends Magento_ImportExport_Model_Import_Entity_Product_Type_Abstract
{
    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_specialAttributes = array(
        '_associated_sku', '_associated_default_qty', '_associated_position'
    );

    /**
     * Import model behavior
     *
     * @var string
     */
    protected $_behavior;

    /**
     * @var Magento_ImportExport_Model_ImportFactory
     */
    protected $_importFactory;

    /**
     * @var Magento_Core_Model_Resource
     */
    protected $_resource;

    /**
     * @var Magento_Catalog_Model_Resource_Product_LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @param Magento_Eav_Model_Resource_Entity_Attribute_Set_CollectionFactory $attrSetColFac
     * @param Magento_Catalog_Model_Resource_Product_Attribute_CollectionFactory $prodAttrColFac
     * @param Magento_ImportExport_Model_ImportFactory $importFactory
     * @param Magento_Catalog_Model_Resource_Product_LinkFactory $productLinkFactory
     * @param Magento_Core_Model_Resource $resource
     * @param array $params
     */
    public function __construct(
        Magento_Eav_Model_Resource_Entity_Attribute_Set_CollectionFactory $attrSetColFac,
        Magento_Catalog_Model_Resource_Product_Attribute_CollectionFactory $prodAttrColFac,
        Magento_ImportExport_Model_ImportFactory $importFactory,
        Magento_Catalog_Model_Resource_Product_LinkFactory $productLinkFactory,
        Magento_Core_Model_Resource $resource,
        array $params
    )
    {
        $this->_importFactory = $importFactory;
        $this->_resource = $resource;
        $this->_productLinkFactory = $productLinkFactory;
        parent::__construct($attrSetColFac, $prodAttrColFac, $params);
    }

    /**
     * Retrive model behavior
     *
     * @return string
     */
    public function getBehavior()
    {
        if (is_null($this->_behavior)) {
            $this->_behavior = $this->_importFactory->create()->getDataSourceModel()->getBehavior();
        }
        return $this->_behavior;
    }

    /**
     * Save product type specific data.
     *
     * @return Magento_ImportExport_Model_Import_Entity_Product_Type_Abstract
     */
    public function saveData()
    {
        $groupedLinkId = Magento_Catalog_Model_Product_Link::LINK_TYPE_GROUPED;
        $connection    = $this->_resource->getConnection('write');
        $resource      = $this->_productLinkFactory->create();
        $mainTable     = $resource->getMainTable();
        $relationTable = $resource->getTable('catalog_product_relation');
        $newSku        = $this->_entityModel->getNewSku();
        $oldSku        = $this->_entityModel->getOldSku();
        $attributes    = array();

        // pre-load attributes parameters
        $select = $connection->select()
            ->from($resource->getTable('catalog_product_link_attribute'), array(
                'id'   => 'product_link_attribute_id',
                'code' => 'product_link_attribute_code',
                'type' => 'data_type'
            ))->where('link_type_id = ?', $groupedLinkId);
        foreach ($connection->fetchAll($select) as $row) {
            $attributes[$row['code']] = array(
                'id' => $row['id'],
                'table' => $resource->getAttributeTypeTable($row['type'])
            );
        }
        while ($bunch = $this->_entityModel->getNextBunch()) {
            $linksData     = array(
                'product_ids'      => array(),
                'links'            => array(),
                'attr_product_ids' => array(),
                'position'         => array(),
                'qty'              => array(),
                'relation'         => array()
            );
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)
                    || empty($rowData['_associated_sku'])
                ) {
                    continue;
                }
                if (isset($newSku[$rowData['_associated_sku']])) {
                    $linkedProductId = $newSku[$rowData['_associated_sku']]['entity_id'];
                } elseif (isset($oldSku[$rowData['_associated_sku']])) {
                    $linkedProductId = $oldSku[$rowData['_associated_sku']]['entity_id'];
                } else {
                    continue;
                }
                $scope = $this->_entityModel->getRowScope($rowData);
                if (Magento_ImportExport_Model_Import_Entity_Product::SCOPE_DEFAULT == $scope) {
                    $productData = $newSku[$rowData[Magento_ImportExport_Model_Import_Entity_Product::COL_SKU]];
                } else {
                    $colAttrSet = Magento_ImportExport_Model_Import_Entity_Product::COL_ATTR_SET;
                    $rowData[$colAttrSet] = $productData['attr_set_code'];
                    $rowData[Magento_ImportExport_Model_Import_Entity_Product::COL_TYPE] = $productData['type_id'];
                }
                $productId = $productData['entity_id'];

                if ($this->_type != $rowData[Magento_ImportExport_Model_Import_Entity_Product::COL_TYPE]) {
                    continue;
                }
                $linksData['product_ids'][$productId] = true;
                $linksData['links'][$productId][$linkedProductId] = $groupedLinkId;
                $linksData['relation'][] = array('parent_id' => $productId, 'child_id' => $linkedProductId);
                $qty = empty($rowData['_associated_default_qty']) ? 0 : $rowData['_associated_default_qty'];
                $pos = empty($rowData['_associated_position']) ? 0 : $rowData['_associated_position'];

                if ($qty || $pos) {
                    $linksData['attr_product_ids'][$productId] = true;
                    if ($pos) {
                        $linksData['position']["{$productId} {$linkedProductId}"] = array(
                            'product_link_attribute_id' => $attributes['position']['id'],
                            'value' => $pos
                        );
                    }
                    if ($qty) {
                        $linksData['qty']["{$productId} {$linkedProductId}"] = array(
                            'product_link_attribute_id' => $attributes['qty']['id'],
                            'value' => $qty
                        );
                    }
                }
            }
            // save links and relations
            if ($linksData['product_ids'] && $this->getBehavior() != Magento_ImportExport_Model_Import::BEHAVIOR_APPEND) {
                $connection->delete(
                    $mainTable,
                    $connection->quoteInto(
                        'product_id IN (?) AND link_type_id = ' . $groupedLinkId,
                        array_keys($linksData['product_ids'])
                    )
                );
            }
            if ($linksData['links']) {
                $mainData = array();

                foreach ($linksData['links'] as $productId => $linkedData) {
                    foreach ($linkedData as $linkedId => $linkType) {
                        $mainData[] = array(
                            'product_id'        => $productId,
                            'linked_product_id' => $linkedId,
                            'link_type_id'      => $linkType
                        );
                    }
                }
                $connection->insertOnDuplicate($mainTable, $mainData);
                $connection->insertOnDuplicate($relationTable, $linksData['relation']);
            }
            // save positions and default quantity
            if ($linksData['attr_product_ids']) {
                $savedData = $connection->fetchPairs($connection->select()
                    ->from($mainTable, array(
                        new Zend_Db_Expr('CONCAT_WS(" ", product_id, linked_product_id)'), 'link_id'
                    ))
                    ->where(
                        'product_id IN (?) AND link_type_id = ' . $groupedLinkId,
                        array_keys($linksData['attr_product_ids'])
                    )
                );
                foreach ($savedData as $pseudoKey => $linkId) {
                    if (isset($linksData['position'][$pseudoKey])) {
                        $linksData['position'][$pseudoKey]['link_id'] = $linkId;
                    }
                    if (isset($linksData['qty'][$pseudoKey])) {
                        $linksData['qty'][$pseudoKey]['link_id'] = $linkId;
                    }
                }
                if ($linksData['position']) {
                    $connection->insertOnDuplicate($attributes['position']['table'], $linksData['position']);
                }
                if ($linksData['qty']) {
                    $connection->insertOnDuplicate($attributes['qty']['table'], $linksData['qty']);
                }
            }
        }
        return $this;
    }
}
