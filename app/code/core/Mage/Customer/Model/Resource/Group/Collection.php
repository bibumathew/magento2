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
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer group collection
 *
 * @category    Mage
 * @package     Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Resource_Group_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Enter description here ...
     *
     */
    protected function _construct()
    {
        $this->_init('customer/group');
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $classId
     * @return Mage_Customer_Model_Resource_Group_Collection
     */
    public function setTaxGroupFilter($classId)
    {
        $taxClassGroupTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_class_group');
        $this->_select->joinLeft($taxClassGroupTable, "{$taxClassGroupTable}.class_group_id=main_table.customer_group_id");
        $this->_select->where("{$taxClassGroupTable}.class_parent_id = ?", $classId);
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $indexes
     * @return Mage_Customer_Model_Resource_Group_Collection
     */
    public function setIgnoreIdFilter($indexes)
    {
        if( !count($indexes) > 0 ) {
            return $this;
        }
        $this->_select->where('main_table.customer_group_id NOT IN(?)', $indexes);
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @return Mage_Customer_Model_Resource_Group_Collection
     */
    public function setRealGroupsFilter()
    {
        $this->addFieldToFilter('customer_group_id', array('gt'=>0));
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @return Mage_Customer_Model_Resource_Group_Collection
     */
    public function addTaxClass()
    {
        $taxClassTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_class');
        $this->_select->joinLeft($taxClassTable, "main_table.tax_class_id = {$taxClassTable}.class_id");

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('customer_group_id', 'customer_group_code');
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    public function toOptionHash()
    {
        return parent::_toOptionHash('customer_group_id', 'customer_group_code');
    }
}
