<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogEvent
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog Events grid
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogEvent
 */
class Enterprise_CatalogEvent_Block_Adminhtml_Event_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function _construct()
    {
        parent::_construct();
        $this->setId('catalogEventGrid');
        $this->setDefaultSort('event_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepares events collection
     *
     * @return Enterprise_CatalogEvent_Block_Adminhtml_Event_Grid
     */
    protected function _prepareCollection()
    {
           $collection = Mage::getModel('Enterprise_CatalogEvent_Model_Event')->getCollection()
               ->addCategoryData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare event grid columns
     *
     * @return Enterprise_CatalogEvent_Block_Adminhtml_Event_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn('event_id', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('ID'),
            'width'  => '80px',
            'type'   => 'text',
            'index'  => 'event_id'
        ));

        $this->addColumn('category_id', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Category ID'),
            'index' => 'category_id',
            'type'  => 'text',
            'width' => 70
        ));

        $this->addColumn('category', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Category'),
            'index' => 'category_name',
            'type'  => 'text'
        ));

        $this->addColumn('date_start', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Starts On'),
            'index' => 'date_start',
            'type' => 'datetime',
            'filter_time' => true,
            'width' => 150
        ));

        $this->addColumn('date_end', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Ends On'),
            'index' => 'date_end',
            'type' => 'datetime',
            'filter_time' => true,
            'width' => 150
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                Enterprise_CatalogEvent_Model_Event::STATUS_UPCOMING => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Upcoming'),
                Enterprise_CatalogEvent_Model_Event::STATUS_OPEN 	  => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Open'),
                Enterprise_CatalogEvent_Model_Event::STATUS_CLOSED   => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Closed')
            ),
            'width' => 140
        ));

        $this->addColumn('display_state', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Display Countdown Ticker On'),
            'index' => 'display_state',
            'type' => 'options',
            'renderer' => 'Enterprise_CatalogEvent_Block_Adminhtml_Event_Grid_Column_Renderer_Bitmask',
            'options' => array(
                0 => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Lister Block'),
                Enterprise_CatalogEvent_Model_Event::DISPLAY_CATEGORY_PAGE => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Category Page'),
                Enterprise_CatalogEvent_Model_Event::DISPLAY_PRODUCT_PAGE  => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Product Page')
            )
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('Enterprise_CatalogEvent_Helper_Data')->__('Sort Order'),
            'index' => 'sort_order',
            'type'  => 'text',
            'width' => 70
        ));

        $this->addColumn('actions', array(
            'header'    => $this->helper('Enterprise_CatalogEvent_Helper_Data')->__('Action'),
            'width'     => 15,
            'sortable'  => false,
            'filter'    => false,
            'type'      => 'action',
            'actions'   => array(
                array(
                    'url'       => $this->getUrl('*/*/edit') . 'id/$event_id',
                    'caption'   => $this->helper('Enterprise_CatalogEvent_Helper_Data')->__('Edit'),
                ),
            )
        ));

        return parent::_prepareColumns();
    }


    /**
     * Grid row event edit url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}