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
 * @category   Mage
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Eav_Model_Mysql4_Entity_Attribute_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('eav/entity_attribute');
    }

    public function setEntityTypeFilter($typeId)
    {
        $this->getSelect()->where('main_table.entity_type_id=?', $typeId);
        return $this;
    }

    public function setAttributeSetFilter($setId)
    {
        if (is_array($setId)) {
            if (!empty($setId)) {
                $this->join('entity_attribute', 'entity_attribute.attribute_id=main_table.attribute_id', 'attribute_id');
                $this->getSelect()->where('entity_attribute.attribute_set_id IN(?)', $setId);
            }
        }
        elseif($setId) {
            $this->join('entity_attribute', 'entity_attribute.attribute_id=main_table.attribute_id', '*');
            $this->getSelect()->where('entity_attribute.attribute_set_id=?', $setId);
            $this->setOrder('sort_order', 'asc');
        }
        return $this;
    }

    public function setAttributeSetsFilter(array $setIds)
    {
        $this->join('entity_attribute', 'entity_attribute.attribute_id=main_table.attribute_id', '*');
        $this->getSelect()->where('entity_attribute.attribute_set_id IN(?)', $setIds);
        $this->setOrder('sort_order', 'asc');
        return $this;
    }
    
    public function setAttributeSetExcludeFilter($setId)
    {
        $this->join('entity_attribute', 'entity_attribute.attribute_id=main_table.attribute_id', '*');
        $this->getSelect()->where('entity_attribute.attribute_set_id != ?', $setId);
        $this->setOrder('sort_order', 'asc');
        return $this;
    }

    public function setAttributesExcludeFilter($attributes)
    {
        #$this->join('entity_attribute', 'entity_attribute.attribute_id=main_table.attribute_id', 'sort_order');
        $this->getSelect()->where('main_table.attribute_id NOT IN(?)', $attributes);
        return $this;
    }

    public function setAttributeGroupFilter($groupId)
    {
        $this->join('entity_attribute', 'entity_attribute.attribute_id=main_table.attribute_id', '*');
        $this->getSelect()->where('entity_attribute.attribute_group_id=?', $groupId);
        $this->setOrder('sort_order', 'asc');
        return $this;
    }

    public function addAttributeGrouping()
    {
        $this->getSelect()->group('entity_attribute.attribute_id');
        return $this;
    }

    public function addVisibleFilter()
    {
        $this->getSelect()->where('main_table.is_visible=?', 1);
        return $this;
    }
    
    public function addIsFilterableFilter()
    {
        $this->getSelect()->where('main_table.is_filterable>0');
        return $this;
    }
    
    public function addIsSearchableFilter()
    {
        $this->getSelect()->where('main_table.is_searchable=1');
        return $this;
    }
}