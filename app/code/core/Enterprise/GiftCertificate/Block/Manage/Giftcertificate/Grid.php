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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_GiftCertificate
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Enterprise_GiftCertificate_Block_Manage_Giftcertificate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('giftcertificateGrid');
        $this->setDefaultSort('giftcertificate_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('giftcertificate_filter');

    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getResourceModel('giftcertificate/giftcertificate_collection');

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('giftcertificate_id',
            array(
                'header'=> Mage::helper('giftcertificate')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'giftcertificate_id',
        ));

        $this->addColumn('code',
            array(
                'header'=> Mage::helper('giftcertificate')->__('Code'),
                'index' => 'code',
        ));

        $this->addColumn('websites',
            array(
                'header'=> Mage::helper('giftcertificate')->__('Website'),
                'width' => '100px',
                'sortable'  => false,
                'index'     => 'website_id',
                'type'      => 'options',
                'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
        ));


        $this->addColumn('date_created',
            array(
                'header'=> Mage::helper('giftcertificate')->__('Date Created'),
                'type'  => 'datetime',
                'index' => 'date_created',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'=>$row->getId())
        );
    }
}