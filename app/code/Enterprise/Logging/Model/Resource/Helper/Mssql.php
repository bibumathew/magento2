<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Logging
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Eav Mssql resource helper model
 *
 * @category    Enterprise
 * @package     Enterprise_Logging
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Logging_Model_Resource_Helper_Mssql extends Mage_Eav_Model_Resource_Helper_Mssql
{
    /**
     * Returns expression for converting int field to IP string
     *
     * @param string | null $field if field is null then this field must be user for prepareSqlCondition
     * @return Zend_Db_Expr
     */
    public function getInetNtoaExpr($field = null)
    {
        $field = $field ? $this->_getReadAdapter()->quoteIdentifier($field) : '#?';
        return new Zend_Db_Expr('dbo.INET_NTOA(' . $field . ')');
    }
}