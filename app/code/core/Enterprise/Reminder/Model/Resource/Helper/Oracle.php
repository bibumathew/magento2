<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_Reminder
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Resource helper class for Oracle Varien DB Adapter
 *
 * @category    Enterprise
 * @package     Enterprise_Reminder
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reminder_Model_Resource_Helper_Oracle extends Mage_Core_Model_Resource_Helper_Oracle
{
    /**
     * Returns expression of days difference between field and date/field
     * @param string $field
     * @param string $date
     * @param bool $dateIsField
     * @return Zend_Db_Expr
     */
    public function getDaysDifferenceSql($field, $date, $dateIsField = false)
    {
        $toDateMask = "TO_DATE(%s,'yyyy-mm-dd HH24:MI:SS')";
        $dateSql = ($dateIsField) ? $date : sprintf($toDateMask, $this->_getReadAdapter()->quote($date));
        $query = sprintf('(trunc(%s) - trunc(%s))', $dateSql, $field);
        return new Zend_Db_Expr($query);
    }

    /**
     * Sets limit for rules specific select
     *
     * @param Varien_Db_Select $select
     * @param int $limit
     * @return void
     */
    public function setRuleLimit(Varien_Db_Select $select, $limit)
    {
        $select->where('ROWNUM <= ?', $limit);
    }
}