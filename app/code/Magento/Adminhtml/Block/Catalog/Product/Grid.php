<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Catalog_Product_Grid extends Magento_Backend_Block_Widget_Grid_Extended
{
    /**
     * Catalog data
     *
     * @var Magento_Catalog_Helper_Data
     */
    protected $_catalogData = null;

    /**
     * @var Magento_Eav_Model_Resource_Entity_Attribute_Set_CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var Magento_Catalog_Model_ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Magento_Catalog_Model_Product_Type
     */

    protected $_type;
    /**
     * @var Magento_Catalog_Model_Product_Status
     */
    protected $_status;

    /**
     * @var Magento_Catalog_Model_Product_Visibility
     */
    protected $_visibility;

    protected $_websiteFactory;

    /**
     * @param Magento_Core_Model_WebsiteFactory $websiteFactory
     * @param Magento_Eav_Model_Resource_Entity_Attribute_Set_CollectionFactory $setsFactory
     * @param Magento_Catalog_Model_ProductFactory $productFactory
     * @param Magento_Catalog_Model_Product_Type $type
     * @param Magento_Catalog_Model_Product_Status $status
     * @param Magento_Catalog_Model_Product_Visibility $visibility
     * @param Magento_Catalog_Helper_Data $catalogData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Core_Model_Url $urlModel
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Magento_Core_Model_WebsiteFactory $websiteFactory,
        Magento_Eav_Model_Resource_Entity_Attribute_Set_CollectionFactory $setsFactory,
        Magento_Catalog_Model_ProductFactory $productFactory,
        Magento_Catalog_Model_Product_Type $type,
        Magento_Catalog_Model_Product_Status $status,
        Magento_Catalog_Model_Product_Visibility $visibility,
        Magento_Catalog_Helper_Data $catalogData,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Core_Model_Url $urlModel,
        array $data = array()
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->_catalogData = $catalogData;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if ($this->_catalogData->isModuleEnabled('Magento_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Magento_Core_Model_AppInterface::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'=> __('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
                'header_css_class'  => 'col-id',
                'column_css_class'  => 'col-id'
        ));
        $this->addColumn('name',
            array(
                'header'=> __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'header_css_class'  => 'col-name',
                'column_css_class'  => 'col-name'
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> __('Name in %1', $store->getName()),
                    'index' => 'custom_name',
                    'header_css_class'  => 'col-name',
                    'column_css_class'  => 'col-name'
            ));
        }

        $this->addColumn('type',
            array(
                'header'=> __('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => $this->_type->getOptionArray(),
                'header_css_class'  => 'col-type',
                'column_css_class'  => 'col-type'
        ));

        $sets = $this->_setsFactory->create()
            ->setEntityTypeFilter($this->_productFactory->create()->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> __('Attribute Set'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
                'header_css_class'  => 'col-attr-name',
                'column_css_class'  => 'col-attr-name'
        ));

        $this->addColumn('sku',
            array(
                'header'=> __('SKU'),
                'width' => '80px',
                'index' => 'sku',
                'header_css_class'  => 'col-sku',
                'column_css_class'  => 'col-sku'
        ));

        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header'=> __('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class'  => 'col-price',
                'column_css_class'  => 'col-price'
        ));

        if ($this->_catalogData->isModuleEnabled('Magento_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> __('Quantity'),
                    'width' => '100px',
                    'type'  => 'number',
                    'index' => 'qty',
                    'header_css_class'  => 'col-qty',
                    'column_css_class'  => 'col-qty'
            ));
        }

        $this->addColumn('visibility',
            array(
                'header'=> __('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => $this->_visibility->getOptionArray(),
                'header_css_class'  => 'col-visibility',
                'column_css_class'  => 'col-visibility'
        ));

        $this->addColumn('status',
            array(
                'header'=> __('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => $this->_status->getOptionArray(),
                'header_css_class'  => 'col-status',
                'column_css_class'  => 'col-status'
        ));

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('websites',
                array(
                    'header'=> __('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => $this->_websiteFactory->create()->getCollection()->toOptionHash(),
                    'header_css_class'  => 'col-websites',
                    'column_css_class'  => 'col-websites'
            ));
        }

        $this->addColumn('edit',
            array(
                'header'    => __('Edit'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => __('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'header_css_class'  => 'col-action',
                'column_css_class'  => 'col-action'
        ));

        if ($this->_catalogData->isModuleEnabled('Magento_Rss')) {
            $this->addRssList('rss/catalog/notifystock', __('Notify Low Stock RSS'));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('product');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> __('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => __('Are you sure?')
        ));

        $statuses = $this->_status->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> __('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => __('Status'),
                         'values' => $statuses
                     )
             )
        ));

        if ($this->_authorization->isAllowed('Magento_Catalog::update_attributes')){
            $this->getMassactionBlock()->addItem('attributes', array(
                'label' => __('Update Attributes'),
                'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
            ));
        }

        $this->_eventManager->dispatch('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getId())
        );
    }
}
