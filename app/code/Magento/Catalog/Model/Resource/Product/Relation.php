<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog Product Relations Resource model
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Resource\Product;

class Relation extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize resource model and define main table
     *
     */
    protected function _construct()
    {
        $this->_init('catalog_product_relation', 'parent_id');
    }

    /**
     * Save (rebuild) product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return \Magento\Catalog\Model\Resource\Product\Relation
     */
    public function processRelations($parentId, $childIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('child_id'))
            ->where('parent_id = ?', $parentId);
        $old = $this->_getReadAdapter()->fetchCol($select);
        $new = $childIds;

        $insert = array_diff($new, $old);
        $delete = array_diff($old, $new);

        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $childId) {
                $insertData[] = array(
                    'parent_id' => $parentId,
                    'child_id'  => $childId
                );
            }
            $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $insertData);
        }
        if (!empty($delete)) {
            $where = join(' AND ', array(
                $this->_getWriteAdapter()->quoteInto('parent_id = ?', $parentId),
                $this->_getWriteAdapter()->quoteInto('child_id IN(?)', $delete)
            ));
            $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        }

        return $this;
    }
}
