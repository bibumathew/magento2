<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml cms blocks grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Alexander Stadnitski <alexander@varien.com>
 * @author     Michael Bessolov <michael@varien.com>
 */
class Mage_Adminhtml_Block_Cms_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('cmsBlockGrid');
        $this->setDefaultSort('block_identifier');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('cms/block_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('title', array(
            'header'=>Mage::helper('cms')->__('Title'),
            'align' =>'left',
            'index' =>'title',
        ));

        $this->addColumn('identifier', array(
            'header'=>Mage::helper('cms')->__('Identifier'),
            'align' =>'left',
            'index' =>'identifier'
        ));

        $this->addColumn('store_id', array(
            'header'    =>Mage::helper('cms')->__('Store View'),
            'index'     =>'store_id',
            'type'      => 'store',
            'store_all' => true
        ));

        $this->addColumn('is_active', array(
            'header'=>Mage::helper('cms')->__('Status'),
            'index'=>'is_active',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('cms')->__('Disabled'),
                1 => Mage::helper('cms')->__('Enabled')
            ),
        ));

        $this->addColumn('creation_time', array(
            'header'=>Mage::helper('cms')->__('Date Created'),
            'index' =>'creation_time',
            'type' => 'datetime',
        ));

        $this->addColumn('update_time', array(
            'header'=>Mage::helper('cms')->__('Last Modified'),
            'index'=>'update_time',
            'type' => 'datetime',
        ));

//        $this->addColumn('block_actions', array(
//            'header'    =>Mage::helper('cms')->__('Action'),
//            'width'     =>10,
//            'sortable'  =>false,
//            'filter'    => false,
//            'type' => 'action',
//            'actions' => array(
//                array(
//                    'url' => $baseUrl . '$identifier',
//                    'caption' => Mage::helper('cms')->__('Preview'),
//                    'target' => '_blank',
//                ),
//            )
//        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('block_id' => $row->getId()));
    }

}
