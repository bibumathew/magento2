<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CustomerSegment
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer Segments Grid
 *
 * @category Magento
 * @package Magento_CustomerSegment
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Magento_CustomerSegment_Block_Adminhtml_Customersegment_Grid extends Magento_Backend_Block_Widget_Grid_Extended
{
    /**
     * @var Magento_CustomerSegment_Model_SegmentFactory
     */
    protected $_segmentFactory;

    /**
     * @var Magento_Core_Model_System_Store
     */
    protected $_systemStore;

    /**
     * @param Magento_Core_Model_System_Store $systemStore
     * @param Magento_CustomerSegment_Model_SegmentFactory $segmentFactory
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Core_Model_Url $urlModel
     * @param array $data
     */
    public function __construct(
        Magento_Core_Model_System_Store $systemStore,
        Magento_CustomerSegment_Model_SegmentFactory $segmentFactory,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Core_Model_Url $urlModel,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_segmentFactory = $segmentFactory;
        parent::__construct($coreData, $context, $storeManager, $urlModel, $data);
    }

    /**
     * Initialize grid
     * Set sort settings
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customersegmentGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Add websites to customer segments collection
     * Set collection
     *
     * @return Magento_CustomerSegment_Block_Adminhtml_Customersegment_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection Magento_CustomerSegment_Model_Resource_Segment_Collection */
        $collection = $this->_segmentFactory->create()
            ->getCollection();
        $collection->addWebsitesToResult();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Add grid columns
     *
     * @return Magento_CustomerSegment_Block_Adminhtml_Customersegment_Grid
     */
    protected function _prepareColumns()
    {
        // this column is mandatory for the chooser mode. It needs to be first
        $this->addColumn('grid_segment_id', array(
            'header'    => __('ID'),
            'align'     => 'right',
            'width'     => 50,
            'index'     => 'segment_id',
        ));

        $this->addColumn('grid_segment_name', array(
            'header'    => __('Segment'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('grid_segment_is_active', array(
            'header'    => __('Status'),
            'align'     => 'left',
            'width'     => 80,
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('grid_segment_website', array(
                'header'    => __('Website'),
                'align'     => 'left',
                'index'     => 'website_ids',
                'type'      => 'options',
                'sortable'  => false,
                'options'   => $this->_systemStore->getWebsiteOptionHash(),
                'width'     => 200,
            ));
        }

        parent::_prepareColumns();
        return $this;
    }

    /**
     * Retrieve row click URL
     *
     * @param Magento_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        if ($this->getIsChooserMode()) {
            return null;
        }
        return $this->getUrl('*/*/edit', array('id' => $row->getSegmentId()));
    }

    /**
     * Row click javascript callback getter
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        if ($this->getIsChooserMode() && $elementId = $this->getRequest()->getParam('value_element_id')) {
            return 'function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                if (trElement) {
                    $(\'' . $elementId . '\').value = trElement.down("td").innerHTML;
                    $(grid.containerId).up().hide();
                }}';
        }
        return 'openGridRow';
    }

    /**
     * Grid URL getter for ajax mode
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/customersegment/grid', array('_current' => true));
    }
}
