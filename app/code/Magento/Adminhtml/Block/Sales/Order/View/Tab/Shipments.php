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
 * Order Shipments grid
 */
class Magento_Adminhtml_Block_Sales_Order_View_Tab_Shipments
    extends Magento_Adminhtml_Block_Widget_Grid
    implements Magento_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Magento_Sales_Model_Resource_Order_Collection_Factory
     */
    protected $_collectionFactory;

    /**
     * @param Magento_Sales_Model_Resource_Order_Collection_Factory $collectionFactory
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Core_Model_Url $urlModel
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Magento_Sales_Model_Resource_Order_Collection_Factory $collectionFactory,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Core_Model_Url $urlModel,
        Magento_Core_Model_Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_shipments');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'Magento_Sales_Model_Resource_Order_Shipment_Grid_Collection';
    }

    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create($this->_getCollectionClass())
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('total_qty')
            ->addFieldToSelect('shipping_name')
            ->setOrderFilter($this->getOrder())
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => __('Shipment'),
            'index' => 'increment_id',
            'header_css_class'  => 'col-memo',
            'column_css_class'  => 'col-memo'
        ));

        $this->addColumn('shipping_name', array(
            'header' => __('Ship-to Name'),
            'index' => 'shipping_name',
            'header_css_class'  => 'col-name',
            'column_css_class'  => 'col-name'
        ));

        $this->addColumn('created_at', array(
            'header' => __('Ship Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'header_css_class'  => 'col-period',
            'column_css_class'  => 'col-period'
        ));

        $this->addColumn('total_qty', array(
            'header' => __('Total Quantity'),
            'index' => 'total_qty',
            'type'  => 'number',
            'header_css_class'  => 'col-qty',
            'column_css_class'  => 'col-qty'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Magento_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/sales_order_shipment/view',
            array(
                'shipment_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
         ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/shipments', array('_current' => true));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return __('Shipments');
    }

    public function getTabTitle()
    {
        return __('Order Shipments');
    }

    public function canShowTab()
    {
        if ($this->getOrder()->getIsVirtual()) {
            return false;
        }
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
