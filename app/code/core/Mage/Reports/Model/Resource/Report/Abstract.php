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
 * Abstract report aggregate resource model
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Reports_Model_Resource_Report_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Flag object
     *
     * @var Mage_Reports_Model_Flag
     */
    protected $_flag     = null;

    /**
     * Retrive flag object
     *
     * @return Mage_Reports_Model_Flag
     */
    protected function _getFlag()
    {
        if ($this->_flag === null) {
            $this->_flag = Mage::getModel('reports/flag');
        }
        return $this->_flag;
    }

    /**
     * Saves flag
     *
     * @param string $code
     * @param mixed $value
     * @return Mage_Reports_Model_Resource_Report_Abstract
     */
    protected function _setFlagData($code, $value = null)
    {
        $this->_getFlag()
            ->setReportFlagCode($code)
            ->unsetData()
            ->loadSelf();

        if ($value !== null) {
            $this->_getFlag()->setFlagData($value);
        }

        // touch last_update
        $this->_getFlag()->setLastUpdate($this->formatDate(time()));

        $this->_getFlag()->save();

        return $this;
    }

    /**
     * Retrieve flag data
     *
     * @param string $code
     * @return mixed
     */
    protected function _getFlagData($code)
    {
        $this->_getFlag()
            ->setReportFlagCode($code)
            ->unsetData()
            ->loadSelf();

        return $this->_getFlag()->getFlagData();
    }

    /**
     * Trancate table
     *
     * @param string $table
     * @return Mage_Reports_Model_Resource_Report_Abstract
     */
    protected function _truncateTable($table)
    {
        $this->_getWriteAdapter()->query('TRUNCATE TABLE ' . $this->_getWriteAdapter()->quoteIdentifier($table));
        return $this;
    }

    /**
     * Clear report table by specified date range.
     * If specified source table parameters,
     * condition will be generated by source table subselect.
     *
     * @param string $table
     * @param string|null $from
     * @param string|null $to
     * @param Zend_Db_Select|string|null $subSelect
     * @param unknown_type $doNotUseTruncate
     * @return Mage_Reports_Model_Resource_Report_Abstract
     */
    protected function _clearTableByDateRange($table, $from = null, $to = null, $subSelect = null, 
        $doNotUseTruncate = false)
    {
        if ($from === null && $to === null && !$doNotUseTruncate) {
            $this->_truncateTable($table);
            return $this;
        }

        if ($subSelect !== null) {
            $deleteCondition = $this->_makeConditionFromDateRangeSelect($subSelect, 'period');
        } else {
            $condition = array();
            if ($from !== null) {
                $dt = new Zend_Date($from);
                $dt = $this->formatDate($dt->getDate());
                $condition[] = $this->_getWriteAdapter()->quoteInto('period >= ?', $dt);
            }

            if ($to !== null) {
                $dt = new Zend_Date($to);
                $dt = $this->formatDate($dt->getDate());
                $condition[] = $this->_getWriteAdapter()->quoteInto('period <= ?', $dt);
            }
            $deleteCondition = implode(' AND ', $condition);
        }

        $this->_getWriteAdapter()->delete($table, $deleteCondition);
        return $this;
    }

    /**
     * Generate table date range select
     *
     * @param string $table
     * @param string $column
     * @param string $whereColumn
     * @param string|null $from
     * @param string|null $to
     * @param array $additionalWhere
     * @param unknown_type $alias
     * @return Varien_Db_Select
     */
    protected function _getTableDateRangeSelect($table, $column, $whereColumn, $from = null, $to = null, 
        $additionalWhere = array(), $alias = 'date_range_table')
    {
        $select = $this->_getWriteAdapter()->select()
            ->from(
                array($alias => $table),
                'DATE('. $this->_getWriteAdapter()->quoteIdentifier($alias . '.' . $column) . ')'
            )
            ->distinct(true);

        if ($from !== null) {
           $select->where($alias . '.' . $whereColumn . ' >= ?', $from);
        }

        if ($to !== null) {
           $select->where($alias . '.' . $whereColumn . ' <= ?', $to);
        }

        if (!empty($additionalWhere)) {
            foreach ($additionalWhere as $condition) {
                if (is_array($condition) && count($condition) == 2) {
                   $condition = $this->_getWriteAdapter()->quoteInto($condition[0], $condition[1]);
                } elseif (is_array($condition)) { // Invalid condition
                   continue;
                }
                $condition = str_replace('{{table}}', $this->_getWriteAdapter()->quoteIdentifier($alias), $condition);
                $select->where($condition);
            }
        }

        return $select;
    }

    /**
     * Make condition for using in where section
     * from select statement with single date column
     *
     * @result string|false
     *
     * @param unknown_type $select
     * @param unknown_type $periodColumn
     * @return unknown
     */
    protected function _makeConditionFromDateRangeSelect($select, $periodColumn)
    {
        static $selectResultCache = array();
        $cacheKey = (string)$select;

        if (!array_key_exists($cacheKey, $selectResultCache)) {
            try {
                $selectResult = array();
                $query = $this->_getReadAdapter()->query($select);
                while ($date = $query->fetchColumn()) {
                    $selectResult[] = $date;
                }
            } catch (Exception $e) {
                $selectResult = false;
            }
            $selectResultCache[$cacheKey] = $selectResult;
        } else {
            $selectResult = $selectResultCache[$cacheKey];
        }

        if ($selectResult === false) {
            return false;
        }

        $whereCondition = array();
        foreach ($selectResult as $date) {
            $whereCondition[] = "{$periodColumn} BETWEEN '{$date} 00:00:00' AND '{$date} 23:59:59'";
        }
        $whereCondition = implode(' OR ', $whereCondition);
        if ($whereCondition == '') {
            $whereCondition = '1<>1';  // FALSE condition!
        }

        return $whereCondition;
    }

    /**
     * Generate table date range select
     *
     * @param string $table
     * @param string $relatedTable
     * @param array $joinCondition
     * @param string $column
     * @param string $whereColumn
     * @param string|null $from
     * @param string|null $to
     * @param array $additionalWhere
     * @param unknown_type $alias
     * @param unknown_type $relatedAlias
     * @return Varien_Db_Select
     */
    protected function _getTableDateRangeRelatedSelect($table, $relatedTable, $joinCondition, $column, $whereColumn, 
        $from = null, $to = null, $additionalWhere = array(), $alias = 'date_range_table', 
        $relatedAlias = 'related_date_range_table')
    {
        $joinConditionSql = '';

        foreach ($joinCondition as $fkField => $pkField) {
            if ($joinConditionSql) {
                $joinConditionSql .= ' AND ';
            }

            $joinConditionSql .= $this->_getWriteAdapter()->quoteIdentifier($alias . '.' . $fkField)
                               . ' = ' . $this->_getWriteAdapter()->quoteIdentifier($relatedAlias . '.' . $pkField);
        }

        $select = $this->_getWriteAdapter()->select()
            ->from(
                array($alias => $table),
                'DATE('. $this->_getWriteAdapter()->quoteIdentifier($alias . '.' . $column) . ')'
            )
            ->joinInner(
                array($relatedAlias => $relatedTable),
                $joinConditionSql,
                array()
            )
            ->distinct(true);

        if ($from !== null) {
           $select->where($relatedAlias . '.' . $whereColumn . ' >= ?', $from);
        }

        if ($to !== null) {
           $select->where($relatedAlias . '.' . $whereColumn . ' <= ?', $to);
        }

        if (!empty($additionalWhere)) {
            foreach ($additionalWhere as $condition) {
                if (is_array($condition) && count($condition) == 2) {
                   $condition = $this->_getWriteAdapter()->quoteInto($condition[0], $condition[1]);
                } elseif (is_array($condition)) { // Invalid condition
                   continue;
                }
                $condition = str_replace(
                    array('{{table}}', '{{related_table}}'),
                    array(
                        $this->_getWriteAdapter()->quoteIdentifier($alias),
                        $this->_getWriteAdapter()->quoteIdentifier($relatedAlias)
                    ),
                    $condition
                );
                $select->where($condition);
            }
        }

        return $select;
    }

    /**
     * Check range dates and transforms it to strings
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Reports_Model_Resource_Report_Abstract
     */
    protected function _checkDates(&$from, &$to)
    {
        if ($from !== null) {
            $from = $this->formatDate($from);
        }

        if ($to !== null) {
            $to = $this->formatDate($to);
        }

        return $this;
    }

    /**
     * Retrieve store timezone offset from UTC in the form acceptable by SQL's CONVERT_TZ()
     *
     * @param unknown_type $store
     * @return string
     */
    protected function _getStoreTimezoneUtcOffset($store = null)
    {
        return Mage::app()->getLocale()->storeDate($store)->toString(Zend_Date::GMT_DIFF_SEP);
    }

    /**
     * Retrieve date in UTC timezone
     *
     * @param unknown_type $date
     * @return Zend_Date|null
     */
    protected function _dateToUtc($date)
    {
        if ($date === null) {
            return null;
        }
        $dateUtc = new Zend_Date($date);
        $dateUtc->setTimezone('Etc/UTC');
        return $dateUtc;
    }
}
