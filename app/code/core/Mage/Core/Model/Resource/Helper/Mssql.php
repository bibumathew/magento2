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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Resource helper class for MS SQL Varien DB Adapter
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Helper_Mssql extends Mage_Core_Model_Resource_Helper_Abstract
{
    /**
     * Returns analytic expression for database column
     *
     * @param string $column
     * @param string $groupAliasName OPTIONAL
     * @param string $orderBy OPTIONAL
     * @return Zend_Db_Expr
     */
    public function prepareColumn($column, $groupByCondition = null, $orderBy = null)
    {
        // if the column already have OVER()
        if (strpos($column, ' OVER(') !== false) {
            return $column;
        }

        if ($groupByCondition) {
            if (is_array($groupByCondition)) {
                $groupByCondition = implode(', ', $groupByCondition);
            }
            $groupByCondition = 'PARTITION BY ' . $groupByCondition;
        }

        if ($orderBy) {
            if (is_array($orderBy)) {
                $orderBy = implode(', ', $orderBy);
            }
            $orderBy = ' ORDER BY ' . $orderBy;
        }
        $expression = sprintf('%s OVER(%s%s)', $column, $groupByCondition, $orderBy);

        return new Zend_Db_Expr($expression);
    }

    /**
     * Returns select query with analytic functions
     *
     * @param Varien_Db_Select $select
     * @return string
     */
    public function getQueryUsingAnalyticFunction(Varien_Db_Select $select)
    {
        $clonedSelect                = clone $select;
        $adapter                     = $this->_getReadAdapter();
        $wrapperTableName            = 'analytic_tbl';
        $wrapperTableColumnName      = 'analytic_clmn';

        /**
         * Prepare where condition for wrapper select
         */
        $quotedCondition = $adapter->quoteInto('WHERE %s.%s = ?', 1);
        $whereCondition = array(
            sprintf($quotedCondition, $wrapperTableName, $wrapperTableColumnName)
        );


        $orderCondition   = implode(', ', $this->_prepareOrder($clonedSelect, true));
        $groupByCondition = implode(', ', $this->_prepareGroup($clonedSelect, true));
        $having           = $this->_prepareHaving($clonedSelect, true);
        if ($having) {
            $whereCondition[] = implode(', ', $having);
        }

        $columnList = $this->prepareColumnsList($clonedSelect, $groupByCondition);

        $clonedSelect->columns(array(
            $wrapperTableColumnName => $this->prepareColumn('RANK()', $groupByCondition, 'NEWID()')
        ));

        $limitCount  = $clonedSelect->getPart(Zend_Db_Select::LIMIT_COUNT);
        $limitOffset = $clonedSelect->getPart(Zend_Db_Select::LIMIT_OFFSET);
        $clonedSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $clonedSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        /**
         * Assemble sql query
         */
        $query = sprintf('SELECT %s.* FROM (%s) %s %s',
            $wrapperTableName,
            $clonedSelect->assemble(),
            $wrapperTableName,
            implode(' AND ', $whereCondition)
        );

        if (!empty($orderCondition)) {
            $query .= sprintf(' ORDER BY %s', $orderCondition);
        }

        $query = $this->_assembleLimit($query, $limitCount, $limitOffset, $columnList);

        return $query;
    }

    /**
     * Returns array of quoted orders with direction
     *
     * @param Varien_Db_Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepareOrder(Varien_Db_Select $select, $autoReset = false)
    {
        $selectOrders = $select->getPart(Zend_Db_Select::ORDER);
        if (!$selectOrders) {
            return array();
        }

        $orders = array();
        foreach ($selectOrders as $term) {
            if (is_array($term)) {
                $field    = $this->_getReadAdapter()->quoteIdentifier($this->_truncateAliasName($term[0]), true);
                $orders[] = $field . ' ' . $term[1];
            } else {
                $orders[] = $this->_getReadAdapter()->quoteIdentifier($this->_truncateAliasName($term), true);
            }
        }

        if ($autoReset) {
            $select->reset(Zend_Db_Select::ORDER);
        }

        return $orders;
    }

    /**
     * Truncate alias name from field.
     *
     * Result string depends from second optional argument $reverse
     * which can be true if you need the first part of the field.
     * Field can be with 'dot' delimiter.
     * 
     * @param string $field
     * @param bool   $reverse OPTIONAL
     * @return string
     */
    protected function _truncateAliasName($field, $reverse = false)
    {
        $string = $field;
        if (!is_numeric($field) && (strpos($field, '.') !== false)) {
            $size  = strpos($field, '.');
            if ($reverse) {
                $string = substr($field, 0, $size);
            } else {
                $string = substr($field, $size + 1);
            }
        }

        return $string;
    }

    /**
     * Returns quoted group by fields
     *  
     * @param Varien_Db_Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepareGroup(Varien_Db_Select $select, $autoReset = false)
    {
        $selectGroups = $select->getPart(Zend_Db_Select::GROUP);
        if (!$selectGroups) {
            return array();
        }

        $groups = array();
        foreach ($selectGroups as $term) {
            $groups[] = $this->_getReadAdapter()->quoteIdentifier($term, true);
        }

        if ($autoReset) {
            $select->reset(Zend_Db_Select::GROUP);
        }

        return $groups;
    }

    /**
     * Prepare and returns having array 
     *
     * @param Varien_Db_Select $select
     * @param bool $autoReset
     * @return array
     * @throws Exception
     */
    protected function _prepareHaving(Varien_Db_Select $select, $autoReset = false)
    {
        $selectHavings = $select->getPart(Zend_Db_Select::HAVING);
        if (!$selectHavings) {
            return array();
        }

        $havings = array();
        $columns = $select->getPart(Zend_Db_Select::COLUMNS);
        foreach ($columns as $columnEntry) {
            $correlationName = (string)$columnEntry[1];
            $column          = $columnEntry[2];
            foreach ($selectHavings as $having) {
                /**
                 * Looking for column expression in the having clause
                 */
                if (strpos($having, $correlationName) !== false) {
                    if (is_string($column)) {
                        /**
                         * Replace column expression to column alias in having clause
                         */
                        $havings[] = str_replace($correlationName, $column, $having);
                    } else {
                        throw new Exception(sprintf("Can't prepare expression without column alias: '%s'", $correlationName));
                    }
                }
            }
        }

        if ($autoReset) {
            $select->reset(Zend_Db_Select::HAVING);
        }

        return $havings;
    }

    /**
     * Assemble limit to the query
     *
     * @param string $query
     * @param int $limitCount
     * @param int $limitOffset
     * @param array $columnList
     * @return string
     * @throws Exception
     */
    protected function _assembleLimit($query, $limitCount, $limitOffset, $columnList)
    {
        if ($limitCount !== null) {
              $limitCount = intval($limitCount);
            if ($limitCount <= 0) {
                throw new Exception("LIMIT argument count={$limitCount} is not valid");
            }

            $limitOffset = intval($limitOffset);
            if ($limitOffset < 0) {
                throw new Exception("LIMIT argument offset={$limitOffset} is not valid");
            }

            $query = preg_replace('/^SELECT\s+(DISTINCT\s)?/i', 'SELECT $1TOP ' . ($limitCount + $limitOffset) . ' ', $query);

            if ($limitOffset + $limitCount != $limitOffset + 1) {
                $columns = array();
                foreach ($columnList as $columnEntry) {
                    $columns[] = $columnEntry[2] ? $columnEntry[2] : $columnEntry[1];
                }
                $rowNumberColumn = $this->prepareColumn('ROW_NUMBER()', null, 'RAND()');
                $query = sprintf('SELECT %s FROM (SELECT m1.*, %s AS analytic_row_number_tbl FROM (%s) m1) m2 WHERE m2.analytic_row_number_tbl >= %d',
                    implode(', ', $columns),
                    $rowNumberColumn,
                    $query,
                    $limitOffset + 1
                );
            }
        }

        return $query;
    }

    /**
     * Prepare select column list
     *
     * @param Varien_Db_Select $select
     * @param  $groupByCondition OPTIONAL
     * @return array|mixed
     * @throws Zend_Db_Exception
     */
    public function prepareColumnsList(Varien_Db_Select $select, $groupByCondition = null)
    {
        if (!count($select->getPart(Zend_Db_Select::FROM))) {
            return $select->getPart(Zend_Db_Select::COLUMNS);
        }

        $columns          = $select->getPart(Zend_Db_Select::COLUMNS);
        $tables           = $select->getPart(Zend_Db_Select::FROM);
        $preparedColumns  = array();

        foreach ($columns as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if ($column instanceof Zend_Db_Expr) {
                if ($alias !== null) {
                    if (preg_match('/(^|[^a-zA-Z_])^(SELECT)?(SUM|MIN|MAX|AVG|COUNT)\s*\(/i', $column, $matches)) {
                        $column = $this->prepareColumn($column, $groupByCondition);
                    }
                    $preparedColumns[strtoupper($alias)] = array(null, $column, $alias);
                } else {
                    throw new Zend_Db_Exception("Can't prepare expression without alias");
                }
            } else {
                if ($column == Zend_Db_Select::SQL_WILDCARD) {
                    if ($tables[$correlationName]['tableName'] instanceof Zend_Db_Expr) {
                        throw new Zend_Db_Exception("Can't prepare expression when tableName is instance of Zend_Db_Expr");
                    }
                    $tableColumns = $this->_getReadAdapter()->describeTable($tables[$correlationName]['tableName']);
                    foreach(array_keys($tableColumns) as $col) {
                        $preparedColumns[strtoupper($col)] = array($correlationName, $col, null);
                    }
                } else {
                    $columnKey = is_null($alias) ? $column : $alias;
                    $preparedColumns[strtoupper($columnKey)] = array($correlationName, $column, $alias);
                }
            }
        }

        $select->reset(Zend_Db_Select::COLUMNS);
        $select->setPart(Zend_Db_Select::COLUMNS, array_values($preparedColumns));
        
        return $preparedColumns;
    }
}
