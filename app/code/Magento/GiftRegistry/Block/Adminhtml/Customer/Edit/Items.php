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
 * Adminhtml customer view gift registry items block
 */
class Magento_GiftRegistry_Block_Adminhtml_Customer_Edit_Items
    extends Magento_Adminhtml_Block_Widget_Grid
{
    /**
     * @var Magento_GiftRegistry_Model_ItemFactory
     */
    protected $itemFactory;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Core_Model_Url $urlModel
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param Magento_GiftRegistry_Model_ItemFactory $itemFactory
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Core_Model_Url $urlModel,
        Magento_Core_Model_Registry $coreRegistry,
        Magento_GiftRegistry_Model_ItemFactory $itemFactory,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->itemFactory = $itemFactory;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('giftregistry_customer_items_grid');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        $collection = $this->itemFactory->create()->getCollection()
            ->addRegistryFilter($this->getEntity()->getId());

        $collection->updateItemAttributes();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header' => __('ID'),
            'index'  => 'product_id',
            'type'   => 'number',
            'width'  => '120px'
        ));

        $this->addColumn('name', array(
            'header' => __('Product'),
            'index'  => 'product_name'
        ));

        $this->addColumn('sku', array(
            'header' => __('SKU'),
            'index'  => 'sku',
            'width'  => '200px'
        ));

        $this->addColumn('price', array(
            'header' => __('Price'),
            'index'  => 'price',
            'type'  => 'currency',
            'width' => '120px',
            'currency_code' => (string) $this->_storeConfig->getConfig(Magento_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
        ));

        $this->addColumn('qty', array(
            'header'   => __('Requested'),
            'index'    => 'qty',
            'width'    => '120px',
            'renderer' => 'Magento_GiftRegistry_Block_Adminhtml_Widget_Grid_Column_Renderer_Qty'
        ));

        $this->addColumn('qty_fulfilled', array(
            'header' => __('Fulfilled'),
            'index'  => 'qty_fulfilled',
            'type'   => 'number',
            'width'  => '120px'
        ));

        $this->addColumn('note', array(
            'header' => __('Note'),
            'index'  => 'note',
            'width'  => '120px'
        ));

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width'  => '120px',
            'options'   => array(
                 0 => __('Action'),
                'update' => __('Update Quantity'),
                'remove' => __('Remove Item')
            ),
            'renderer' => 'Magento_GiftRegistry_Block_Adminhtml_Widget_Grid_Column_Renderer_Action'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Return grid row url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }

    /**
     * Return gift registry entity object
     *
     * @return Magento_GiftRegistry_Model_Entity
     */
    public function getEntity()
    {
        return $this->_coreRegistry->registry('current_giftregistry_entity');
    }
}
