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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Flat sales order shipment resource
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Order_Shipment extends Mage_Sales_Model_Resource_Order_Abstract
{
    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_eventPrefix                  = 'sales_order_shipment_resource';

    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_grid                         = true;

    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_useIncrementId               = true;

    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_entityTypeForIncrementId     = 'shipment';

    /**
     * Enter description here ...
     *
     */
    protected function _construct()
    {
        $this->_init('sales/shipment', 'entity_id');
    }

    /**
     * Init virtual grid records for entity
     *
     * @return Mage_Sales_Model_Resource_Order_Shipment
     */
    protected function _initVirtualGridColumns()
    {
        parent::_initVirtualGridColumns();
        $this->addVirtualGridColumn(
                'shipping_name',
                'sales/order_address',
                array('shipping_address_id' => 'entity_id'),
                'CONCAT(IFNULL({{table}}.firstname, ""), " ", IFNULL({{table}}.lastname, ""))'
            )
            ->addVirtualGridColumn(
                'order_increment_id',
                'sales/order',
                array('order_id' => 'entity_id'),
                'increment_id'
            )
            ->addVirtualGridColumn(
                'order_created_at',
                'sales/order',
                array('order_id' => 'entity_id'),
                'created_at'
            )
            ;

        return $this;
    }
}
