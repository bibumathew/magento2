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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Entity attribute option resource model
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Resource_Entity_Attribute_Option extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('eav/attribute_option', 'option_id');
    }

    /**
     * Add Join with option value for collection select
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param Zend_Db_Expr $valueExpr
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Option
     */
    public function addOptionValueToCollection($collection, $attribute, $valueExpr)
    {
        $adapter        = $this->_getReadAdapter();
        $attributeCode  = $attribute->getAttributeCode();
        $optionTable1   = $attributeCode . '_option_value_t1';
        $optionTable2   = $attributeCode . '_option_value_t2';
        $tableJoinCond1 = "{$optionTable1}.option_id={$valueExpr} AND {$optionTable1}.store_id=0"
        ;
        $tableJoinCond2 = $adapter->quoteInto("{$optionTable2}.option_id={$valueExpr} AND {$optionTable2}.store_id=?",
            $collection->getStoreId());
        $valueExpr      = $adapter->getCheckSql("{$optionTable2}.value_id IS NULL",
            "{$optionTable1}.value",
            "{$optionTable2}.value");

        $collection->getSelect()
            ->joinLeft(
                array($optionTable1 => $this->getTable('eav/attribute_option_value')),
                $tableJoinCond1,
                array())
            ->joinLeft(
                array($optionTable2 => $this->getTable('eav/attribute_option_value')),
                $tableJoinCond2,
                array($attributeCode => $valueExpr)
            );

        return $this;
    }

    /**
     * Retrieve Select for update Flat data
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param int $store
     * @param bool $hasValueField flag which require option value
     * @return Varien_Db_Select
     */
    public function getFlatUpdateSelect(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $store,
        $hasValueField = true
    ) {
        $adapter        = $this->_getReadAdapter();
        $attributeTable = $attribute->getBackend()->getTable();
        $attributeCode  = $attribute->getAttributeCode();

        $joinCondition = 'e.entity_id = t1.entity_id';
        if ($attribute->getFlatAddChildData()) {
            $joinCondition .= ' AND e.child_id = t1.entity_id';
        }

        $valueExpr  = $adapter->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
        /* @var $select Varien_Db_Select */
        $select     = $adapter->select()
            ->joinLeft(
                array('t1' => $attributeTable),
                $joinCondition,
                array())
            ->joinLeft(
                array('t2' => $attributeTable),
                $adapter->quoteInto('t2.entity_id = t1.entity_id'
                    . ' AND t1.entity_type_id = t2.entity_type_id'
                    . ' AND t1.attribute_id = t2.attribute_id'
                    . ' AND t2.store_id = ?', $store),
                array($attributeCode => $valueExpr));

        if (($attribute->getFrontend()->getInputType() != 'multiselect') && $hasValueField) {
            $valueExpr  = $adapter->getCheckSql('to2.value_id > 0', 'to1.value', 'to2.value');
            $select->joinLeft(
                array('to1' => $this->getTable('eav/attribute_option_value')),
                "to1.option_id = {$valueExpr} AND to1.store_id = 0",
                array())
            ->joinLeft(
                array('to2' => $this->getTable('eav/attribute_option_value')),
                $adapter->quoteInto("to2.option_id = {$valueExpr} AND to2.store_id = ?", $store),
                array($attributeCode . '_value' => $valueExpr)
            );
        }

        $select
            ->where('t1.entity_type_id = ?', $attribute->getEntityTypeId())
            ->where('t1.attribute_id = ?', $attribute->getId())
            ->where('t1.store_id = 0');

        if ($attribute->getFlatAddChildData()) {
            $select->where('e.is_child = 0');
        }

        return $select;
    }
}
