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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog product linked products collection
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Link_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Store product model
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Store product link model
     *
     * @var Mage_Catalog_Model_Product_Link
     */
    protected $_linkModel;

    /**
     * Store link type id
     *
     * @var int
     */
    protected $_linkTypeId;

    /**
     * Store strong mode flag that determine if needed for inner join or left join of linked products
     *
     * @var bool
     */
    protected $_isStrongMode;

    /**
     * Store flag that determine if product filter was enabled
     *
     * @var bool
     */
    protected $_hasLinkFilter  = false;

    /**
     * Declare link model and initialize type attributes join
     *
     * @param Mage_Catalog_Model_Product_Link $linkModel
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function setLinkModel($linkModel)
    {
        $this->_linkModel = $linkModel;
        if ($linkModel->hasLinkTypeId()) {
            $this->_linkTypeId = $linkModel->getLinkTypeId();
        }
        return $this;
    }

    /**
     * Enable strong mode for inner join of linked products
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function setIsStrongMode()
    {
        $this->_isStrongMode = true;
        return $this;
    }

    /**
     * Retrieve collection link model
     *
     * @return Mage_Catalog_Model_Product_Link
     */
    public function getLinkModel()
    {
        return $this->_linkModel;
    }

    /**
     * Initialize collection parent product and add limitation join
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        if ($product && $product->getId()) {
            $this->_hasLinkFilter = true;
        }
        return $this;
    }

    /**
     * Retrieve collection base product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Exclude products from filter
     *
     * @param array $products
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function addExcludeProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = array($products);
            }
            $this->_hasLinkFilter = true;
            $this->addFieldToFilter('links.linked_product_id', array('nin' => $products));
        }
        return $this;
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir = Varien_Db_Select::SQL_ASC)
    {
        /**
         * Position is not eav attribute (it is links attribute) so we cannot use default attributes to sort
         */
        if ($attribute == 'position' && $this->_hasLinkFilter) {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::addAttributeToSort($attribute, $dir);
        }
        return $this;
    }

    /**
     * Add products to filter
     *
     * @param array|int|string $products
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function addProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = array($products);
            }
            $this->addFieldToFilter('links.product_id', array('in' => $products));
            $this->_hasLinkFilter = true;
        }

        return $this;
    }

    /**
     * Add random sorting order
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function setRandomOrder()
    {
        //TODO need to optimaze: SELECT RAND() * MAX(ID) + ORDER BY ID
        $this->getSelect()->order(new Zend_Db_Expr('RAND()'));
        return $this;
    }

    /**
     * Setting group by to exclude duplications in collection
     *
     * @param string $groupBy
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function setGroupBy($groupBy = 'e.entity_id')
    {
        $this->getSelect()->group($groupBy);
        return $this;
    }

    /**
     * Join linked products when specified link model
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    protected function _beforeLoad()
    {
        if ($this->getLinkModel()) {
            $this->_joinLinks();
        }
        return parent::_beforeLoad();
    }

    /**
     * Join linked products and their attributes
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    protected function _joinLinks()
    {
        $select  = $this->getSelect();
        $adapter = $select->getAdapter();

        $joinCondition = array(
            'links.linked_product_id = e.entity_id',
            $adapter->quoteInto('links.link_type_id = ?', $this->_linkTypeId)
        );
        $joinType = 'join';
        if ($this->getProduct() && $this->getProduct()->getId()) {
            $productId = $this->getProduct()->getId();
            if ($this->_isStrongMode) {
                $this->addFieldToFilter('links.product_id', array('eq' => $productId));
            } else {
                $joinType = 'joinLeft';
                $joinCondition[] = $adapter->quoteInto('links.product_id = ?', $productId);
            }
            $this->addFieldToFilter('entity_id', array('neq' => $productId));
        } elseif ($this->_isStrongMode) {
            $this->addFieldToFilter('entity_id', array('eq' => -1));
        }
        if($this->_hasLinkFilter) {
            $select->$joinType(
                array('links' => $this->getTable('catalog/product_link')),
                implode(Varien_Db_Select::SQL_AND, $joinCondition),
                array('link_id')
            );
            $this->joinAttributes();
        }

        return $this;
    }

    /**
     * Enable sorting products by its position
     *
     * @param string $dir sort type asc|desc
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function setPositionOrder($dir = Varien_Db_Select::SQL_ASC)
    {
        return $this->setOrder('position', $dir);
    }

    /**
     * Join attributes
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function joinAttributes()
    {
        if (!$this->getLinkModel()) {
            return $this;
        }
        $attributes = $this->getLinkModel()->getAttributes();
        $attributesByType = array();
        foreach ($attributes as $attribute) {
            $table = $this->getLinkModel()->getAttributeTypeTable($attribute['type']);
            $alias = sprintf('link_attribute_%s_%s', $attribute['code'], $attribute['type']);

            $joinCondiotion = array(
                "{$alias}.link_id = main_table.link_id",
                $this->getSelect()->getAdapter()->quoteInto("{$alias}.product_link_attribute_id = ?", $attribute['id'])
            );
            $this->getSelect()->joinLeft(
                array($alias => $table),
                implode(Varien_Db_Select::SQL_AND, $joinCondiotion),
                array($attribute['code'] => 'value')
            );
        }


        return $this;
    }
}
