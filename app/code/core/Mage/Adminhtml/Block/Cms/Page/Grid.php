<?php
/**
 * Adminhtml cms pages grid
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_Block_Cms_Page_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('cmsPageGrid');
        $this->setDefaultSort('identifier');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('cms/page_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = Mage::getUrl();

        $this->addColumn('title', array(
            'header'=>__('Title'),
            'align' =>'left',
            'index' =>'title',
        ));

        $this->addColumn('identifier', array(
            'header'=>__('Identifier'),
            'align' =>'left',
            'index' =>'identifier'
        ));
        $layouts = array();
        foreach (Mage::getConfig()->getNode('global/cms/layouts')->children() as $layoutName=>$layoutConfig) {
        	$layouts[$layoutName] = (string)$layoutConfig->label;
        }
        $this->addColumn('root_template', array(
            'header'=>__('Layout'),
            'index'=>'root_template',
            'type' => 'options',
            'options' => $layouts,
        ));
        
        $stores = Mage::getResourceModel('core/store_collection')->load()->toOptionHash();
        $stores[0] = __('All stores');

        $this->addColumn('store_id', array(
            'header'=>__('Store'),
            'index'=>'store_id',
            'type' => 'options',
            'options' => $stores,
        ));

        $this->addColumn('is_active', array(
            'header'=>__('Status'),
            'index'=>'is_active',
            'type' => 'options',
            'options' => array(
                0 => __('Disabled'),
                1 => __('Enabled')
            ),
        ));

        $this->addColumn('creation_time', array(
            'header'=>__('Date Created'),
            'index' =>'creation_time',
            'type' => 'datetime',
        ));

        $this->addColumn('update_time', array(
            'header'=>__('Last Modified'),
            'index'=>'update_time',
            'type' => 'datetime',
        ));

        $this->addColumn('page_actions', array(
            'header'    =>__('Action'),
            'width'     =>10,
            'sortable'  =>false,
            'filter'    => false,
            'type' => 'action',
            'actions' => array(
                array(
                    'url' => $baseUrl . '$identifier',
                    'caption' => __('Preview'),
                    'target' => '_blank',
                ),
            )
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/edit', array('page_id' => $row->getId()));
    }

}
