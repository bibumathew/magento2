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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order entity resource model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Sales_Model_Mysql4_Order extends Mage_Eav_Model_Entity_Abstract
{

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('order');
        $read = $resource->getConnection('sales_read');
        $write = $resource->getConnection('sales_write');
        $this->setConnection($read, $write);
    }

    /**
     * Count existent products of order items by specified product types
     *
     * @param int $orderId
     * @param array $productTypeIds
     * @param bool $isProductTypeIn
     * @return array
     */
    public function aggregateProductsByTypes($orderId, $productTypeIds = array(), $isProductTypeIn = false)
    {
        $select = $this->getReadConnection()->select()
            ->from(array('o' => $this->getTable('sales/order_item')), new Zend_Db_Expr('o.product_type, COUNT(*)'))
            ->joinInner(array('p' => $this->getTable('catalog/product')), 'o.product_id=p.entity_id', array())
            ->where('o.order_id=?', $orderId)
            ->group('(1)')
        ;
        if ($productTypeIds) {
            $select->where($this->getReadConnection()->quoteInto(
                sprintf('(o.product_type %s (?))', ($isProductTypeIn ? 'IN' : 'NOT IN')),
                $productTypeIds
            ));
        }
        return $this->getReadConnection()->fetchPairs($select);
    }

    /**
     * Aggregate Orders data
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Mysql4_Order
     */
    public function aggregateOrders($from = null, $to = null)
    {
        $this->_aggregateOrderDataByColumn($from, $to, 'sales/order_aggregated_created', 'created_at');
        $this->_aggregateOrderDataByColumn($from, $to, 'sales/order_aggregated_updated', 'updated_at');
    }

    /**
     * Aggregate Orders data by column
     *
     * @param mixed $from
     * @param mixed $to
     * @param string $tableName
     * @param string $column
     * @return Mage_Sales_Model_Mysql4_Order
     */
    protected function _aggregateOrderDataByColumn($from, $to, $tableName, $column)
    {
        try {
            $tableName = $this->getTable($tableName);
            $writeAdapter = $this->getWriteConnection();

            $writeAdapter->beginTransaction();

            if (is_null($from) && is_null($to)) {
                $writeAdapter->query("TRUNCATE TABLE {$tableName}");
            } else {
                $deleteWhereCondition = (!is_null($from)) ? "period >= {$from}" : '';
                $deleteWhereCondition .= (!is_null($to)) ? " AND period <= {$to}" : '';
                $writeAdapter->delete($tableName, $deleteWhereCondition);
            }

            $qtySelect = $writeAdapter->select()
                ->from(array('p' => $this->getTable('sales/order_item')), array())
                ->columns(array(
                    'order_id',
                    'total_qty'  => 'IFNULL(SUM(c.qty_ordered), SUM(p.qty_ordered))'
                ))
                ->joinInner(array('o' => $this->getTable('sales/order')), 'p.order_id = o.entity_id', array())
                ->joinLeft(array('c' => $this->getTable('sales/order_item')),
                    'c.parent_item_id IS NOT NULL AND p.item_id = c.parent_item_id', array()
                )
                ->where('p.parent_item_id IS NULL')
                ->where('o.state <> ?', 'pending');

                if (!is_null($from)) {
                    $qtySelect->where("o.{$column} >= ?", $from);
                }
                if (!is_null($to)) {
                    $qtySelect->where("o.{$column} <= ?", $to);
                }

                $qtySelect->group('p.order_id');

            $columns = array(
                'period'                    => "DATE(e.{$column})",
                'store_id'                  => 'e.store_id',
                'order_status'              => 'e.status',
                'orders_count'              => 'COUNT(e.entity_id)',
                'total_qty_ordered'         => 'SUM(oa.total_qty)',
                'base_profit_amount'        => 'SUM(e.base_total_paid * e.base_to_global_rate) - SUM(e.base_total_refunded * e.base_to_global_rate) - SUM(e.base_total_invoiced_cost * e.base_to_global_rate)',
                'base_subtotal_amount'      => 'SUM(e.base_subtotal * e.base_to_global_rate)',
                'base_tax_amount'           => 'SUM(e.base_tax_amount * e.base_to_global_rate)',
                'base_shipping_amount'      => 'SUM(e.base_shipping_amount * e.base_to_global_rate)',
                'base_discount_amount'      => 'SUM(e.base_discount_amount * e.base_to_global_rate)',
                'base_grand_total_amount'   => 'SUM(e.base_grand_total * e.base_to_global_rate)',
                'base_invoiced_amount'      => 'SUM(e.base_total_paid * e.base_to_global_rate)',
                'base_refunded_amount'      => 'SUM(e.base_total_refunded * e.base_to_global_rate)',
            );

            $select = $writeAdapter->select()
                ->from(array('e' => $this->getTable('sales/order')), array())
                ->columns($columns)
                ->joinLeft(array('oa'=> $qtySelect), 'e.entity_id = oa.order_id', array())
                ->where('e.state <> ?', 'pending');

                if (!is_null($from)) {
                    $select->where("e.{$column} >= ?", $from);
                }
                if (!is_null($to)) {
                    $select->where("e.{$column} <= ?", $to);
                }

                $select->group(new Zend_Db_Expr('1,2,3'));

            $writeAdapter->query("
                INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
            ");

            $select = $writeAdapter->select();
            $columns = array(
                'period'                    => 'period',
                'store_id'                  => new Zend_Db_Expr('0'),
                'order_status'              => 'order_status',
                'orders_count'              => 'SUM(orders_count)',
                'total_qty_ordered'         => 'SUM(total_qty_ordered)',
                'base_profit_amount'        => 'SUM(base_profit_amount)',
                'base_subtotal_amount'      => 'SUM(base_subtotal_amount)',
                'base_tax_amount'           => 'SUM(base_tax_amount)',
                'base_shipping_amount'      => 'SUM(base_shipping_amount)',
                'base_discount_amount'      => 'SUM(base_discount_amount)',
                'base_grand_total_amount'   => 'SUM(base_grand_total_amount)',
                'base_invoiced_amount'      => 'SUM(base_invoiced_amount)',
                'base_refunded_amount'      => 'SUM(base_refunded_amount)'
            );
            $select->from($tableName, $columns)
                ->where("store_id <> 0");

                if (!is_null($from)) {
                    $select->where('period >= ?', $from);
                }
                if (!is_null($to)) {
                    $select->where('period <= ?', $to);
                }

                $select->group(new Zend_Db_Expr('1,2,3'));

            $writeAdapter->query("
                INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
            ");

        } catch (Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        $writeAdapter->commit();
        return $this;
    }
}

