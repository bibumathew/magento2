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
 * Stores collection
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Store_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_loadDefault    = false;

    /**
     * Enter description here ...
     *
     */
    protected function _construct()
    {
        $this->_init('core/store');
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $loadDefault
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function setLoadDefault($loadDefault)
    {
        $this->_loadDefault = $loadDefault;
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    public function getLoadDefault()
    {
        return $this->_loadDefault;
    }

    /**
     * Enter description here ...
     *
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function setWithoutDefaultFilter()
    {
        $this->getSelect()->where($this->getConnection()->quoteInto('main_table.store_id>?', 0));
        return $this;
    }

    /**
     * Add filter by group id.
     * Group id can be passed as one single value or array of values.
     *
     * @param int|array $groupId
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function addGroupFilter($groupId)
    {
        if (is_array($groupId)) {
            $condition = $this->getConnection()->quoteInto("main_table.group_id IN (?)", $groupId);
        } else {
            $condition = $this->getConnection()->quoteInto("main_table.group_id = ?",$groupId);
        }

        $this->addFilter('group_id', $condition, 'string');
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $store
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function addIdFilter($store)
    {
        if (is_array($store)) {
            $condition = $this->getConnection()->quoteInto("main_table.store_id IN (?)", $store);
        }
        else {
            $condition = $this->getConnection()->quoteInto("main_table.store_id=?",$store);
        }

        $this->addFilter('store_id', $condition, 'string');
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $website
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function addWebsiteFilter($website)
    {
        if (is_array($website)) {
            $condition = $this->getConnection()->quoteInto("main_table.website_id IN (?)", $website);
        }
        else {
            $condition = $this->getConnection()->quoteInto("main_table.website_id=?",$website);
        }

        $this->addFilter('website_id', $condition, 'string');
        return $this;
    }

    /**
     * Add root category id filter to store collection
     *
     * @param int|array $category
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function addCategoryFilter($category)
    {
        if (!is_array($category)) {
            $category = array($category);
        }
        return $this->loadByCategoryIds($category);
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('store_id', 'name');
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('store_id', 'name');
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $printQuery
     * @param unknown_type $logQuery
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->getLoadDefault()) {
            $this->getSelect()->where($this->getConnection()->quoteInto('main_table.store_id>?', 0));
        }
        $this->addOrder('CASE WHEN main_table.store_id = 0 THEN 0 ELSE 1 END', 'ASC')
            ->addOrder('main_table.sort_order', 'ASC')
            ->addOrder('main_table.name', 'ASC');
        parent::load($printQuery, $logQuery);
        return $this;
    }

    /**
     * Add root category id filter to store collection
     *
     * @param array $categories
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function loadByCategoryIds(array $categories)
    {
        $this->setLoadDefault(true);
        $condition = $this->getConnection()->quoteInto('group_table.root_category_id IN(?)', $categories);
        $this->_select->joinLeft(
            array('group_table' => $this->getTable('core/store_group')),
            'main_table.group_id=group_table.group_id',
            array('root_category_id')
        )->where($condition);

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function addRootCategoryIdAttribute()
    {
        $this->_select->joinLeft(
            array('group_table' => $this->getTable('core/store_group')),
            'main_table.group_id=group_table.group_id',
            array('root_category_id')
        );
        return $this;
    }
}
