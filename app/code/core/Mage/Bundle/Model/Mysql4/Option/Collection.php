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
 * @package    Mage_Bundle
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bundle Options Resource Collection
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Model_Mysql4_Option_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_showAllSelections = false;
    protected $_selectionsAppended = false;
    protected function _construct()
    {
        $this->_init('bundle/option');
    }

    public function joinValues($storeId)
    {
        $this->getSelect()->joinLeft(array('option_value_default' => $this->getTable('bundle/option_value')),
                '`main_table`.`option_id` = `option_value_default`.`option_id` and `option_value_default`.`store_id` = "0"',
                array())
            ->from('', array('title' => 'IFNULL(`option_value`.`title`, `option_value_default`.`title`)'));

        if ($storeId !== null) {
            $this->getSelect()->joinLeft(array('option_value' => $this->getTable('bundle/option_value')),
                '`main_table`.`option_id` = `option_value`.`option_id` and `option_value`.`store_id` = "' . $storeId . '"',
                array());
        }

        return $this;
    }

    public function setProductIdFilter($productId)
    {
        $this->addFieldToFilter('`main_table`.`parent_id`', $productId);
        return $this;
    }

    public function setPositionOrder()
    {
        $this->setOrder('`main_table`.`position`', 'asc');
        return $this;
    }

    public function appendSelections($selectionsCollection)
    {
        if (!$this->_selectionsAppended) {
            foreach ($selectionsCollection as $_selection) {
                if ($this->getShowAllSelections() || $_selection->isSaleable()) {
                    if ($_option = $this->getItemById($_selection->getOptionId())) {
                        $_option->addSelection($_selection);
                    }
                }
            }
            $this->_selectionsAppended = true;
        }
        return $this->getItems();
    }

    public function setShowAllSelections($status)
    {
        $this->_showAllSelections = $status;
    }

    public function getShowAllSelections()
    {
        return $this->_showAllSelections;
    }
}