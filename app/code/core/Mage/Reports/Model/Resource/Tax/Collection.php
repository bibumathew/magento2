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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Reports tax collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Tax_Collection extends Mage_Sales_Model_Entity_Order_Collection
{
    /**
     * Enter description here ...
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setRowIdFieldName('tax_id');
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $from
     * @param unknown_type $to
     * @return Mage_Reports_Model_Resource_Tax_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset();

        $this->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to))
            ->addExpressionAttributeToSelect('orders', 'COUNT(DISTINCT({{entity_id}}))', array('entity_id'))
            ->getSelect()
            ->join(array('tax_table' => $this->getTable('sales/order_tax')), 'e.entity_id = tax_table.order_id')
            ->group('tax_table.code')
            ->order(array('process', 'priority'));

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $storeIds
     * @return Mage_Reports_Model_Resource_Tax_Collection
     */
    public function setStoreIds($storeIds)
    {
        $vals = array_values($storeIds);
        if (count($storeIds) >= 1 && $vals[0] != '') {
            $this->getSelect()
                ->where('e.store_id in (?)', (array)$storeIds)
                ->columns(array('tax'=>'SUM(tax_table.base_real_amount)'));
        } else {
            $this->addExpressionAttributeToSelect(
                    'tax',
                    'SUM(tax_table.base_real_amount*{{base_to_global_rate}})',
                    array('base_to_global_rate'));
        }

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);
        $countSelect->columns("count(DISTINCT e.entity_id)");
        $sql = $countSelect->__toString();
        return $sql;
    }
}
