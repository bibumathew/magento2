<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Reminder
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Reminder rules resource collection
 *
 * @category    Enterprise
 * @package     Enterprise_Reminder
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reminder_Model_Resource_Rule_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Intialize collection
     *
     */
    protected function _construct()
    {
        $this->_init('Enterprise_Reminder_Model_Rule', 'Enterprise_Reminder_Model_Resource_Rule');
        $this->addFilterToMap('rule_id', 'main_table.rule_id');
    }

    /**
     * Limit rules collection by is_active column
     *
     * @param int $value
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addIsActiveFilter($value)
    {
        $this->getSelect()->where('main_table.is_active = ?', $value);
        return $this;
    }

    /**
     * Limit rules collection by date columns
     *
     * @param string $date
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addDateFilter($date)
    {
        $this->getSelect()
            ->where('active_from IS NULL OR active_from <= ?', $date)
            ->where('active_to IS NULL OR active_to >= ?', $date);

        return $this;
    }

    /**
     * Limit rules collection by separate rule
     *
     * @param int $value
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addRuleFilter($value)
    {
        $this->getSelect()->where('main_table.rule_id = ?', $value);
        return $this;
    }

    /**
     * Redeclare after load method for adding website ids to items
     *
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('add_websites_to_result') && $this->_items) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('enterprise_reminder_rule_website'), array(
                    'rule_id',
                    'website_id'
                ))
                ->where('rule_id IN (?)', array_keys($this->_items));
            $data = $this->getConnection()->fetchAll($select);
            $websites = array_fill_keys(array_keys($this->_items), array());
            foreach ($data as $row) {
                $websites[$row['rule_id']][] = $row['website_id'];
            }
            foreach ($this->_items as $item) {
                if (isset($websites[$item->getId()])) {
                    $item->setWebsiteIds($websites[$item->getId()]);
                }
            }
        }

        return $this;
    }

    /**
     * Init flag for adding rule website ids to collection result
     *
     * @param bool | null $flag
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addWebsitesToResult($flag = null)
    {
        $flag = ($flag === null) ? true : $flag;
        $this->setFlag('add_websites_to_result', $flag);
        return $this;
    }

    /**
     * Limit rules collection by specific website
     *
     * @param int | array | Mage_Core_Model_Website $websiteId
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addWebsiteFilter($websiteId)
    {
        if (!$this->getFlag('is_website_table_joined')) {
            $this->setFlag('is_website_table_joined', true);
            $this->getSelect()->joinInner(
                array('website' => $this->getTable('enterprise_reminder_rule_website')),
                'main_table.rule_id = website.rule_id',
                array()
            );
        }

        if ($websiteId instanceof Mage_Core_Model_Website) {
            $websiteId = $websiteId->getId();
        }
        $this->getSelect()->where('website.website_id IN (?)', $websiteId);

        return $this;
    }

    /**
     * Redeclared for support website id filter
     *
     * @param string $field
     * @param mixed $condition
     * @return Enterprise_Reminder_Model_Resource_Rule_Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'website_ids') {
            return $this->addWebsiteFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }
}