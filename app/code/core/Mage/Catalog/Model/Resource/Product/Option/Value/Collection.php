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
 * Catalog product option values collection
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Option_Value_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Enter description here ...
     *
     */
    protected function _construct()
    {
        $this->_init('catalog/product_option_value');
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $store_id
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function getValues($store_id)
    {
        $this->getSelect()
            ->joinLeft(array('default_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`default_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`default_value_price`.store_id=?',0),
                array('default_price'=>'price','default_price_type'=>'price_type'))
            ->joinLeft(array('store_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`store_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_price`.store_id=?', $store_id),
                array('store_price'=>'price','store_price_type'=>'price_type',
                'price'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price,`default_value_price`.price)'),
                'price_type'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price_type,`default_value_price`.price_type)')))
            ->join(array('default_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`default_value_title`.option_type_id=`main_table`.option_type_id',
                array('default_title'=>'title'))
            ->joinLeft(array('store_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`store_value_title`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_title`.store_id=?',$store_id),
                array('store_title'=>'title','title'=>new Zend_Db_Expr('IFNULL(`store_value_title`.title,`default_value_title`.title)')))
            ->where('`default_value_title`.store_id=?',0);
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $store_id
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addTitlesToResult($store_id)
    {
        $this->getSelect()
            ->joinLeft(array('default_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`default_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`default_value_price`.store_id=?',0),
                array('default_price'=>'price','default_price_type'=>'price_type'))
            ->joinLeft(array('store_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`store_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_price`.store_id=?', $store_id),
                array('store_price'=>'price','store_price_type'=>'price_type',
                'price'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price,`default_value_price`.price)'),
                'price_type'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price_type,`default_value_price`.price_type)')))
            ->join(array('default_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`default_value_title`.option_type_id=`main_table`.option_type_id',
                array('default_title'=>'title'))
            ->joinLeft(array('store_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`store_value_title`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_title`.store_id=?',$store_id),
                array('store_title'=>'title','title'=>new Zend_Db_Expr('IFNULL(`store_value_title`.title,`default_value_title`.title)')))
            ->where('`default_value_title`.store_id=?',0);

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $store_id
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addTitleToResult($store_id)
    {
        $this->getSelect()
            ->join(array('default_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`default_value_title`.option_type_id=`main_table`.option_type_id',
                array('default_title'=>'title'))
            ->joinLeft(array('store_value_title'=>$this->getTable('catalog/product_option_type_title')),
                '`store_value_title`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_title`.store_id=?',$store_id),
                array('store_title'=>'title','title'=>new Zend_Db_Expr('IFNULL(`store_value_title`.title,`default_value_title`.title)')))
            ->where('`default_value_title`.store_id=?',0);

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $store_id
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addPriceToResult($store_id)
    {
        $this->getSelect()
            ->joinLeft(array('default_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`default_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`default_value_price`.store_id=?',0),
                array('default_price'=>'price','default_price_type'=>'price_type'))
            ->joinLeft(array('store_value_price'=>$this->getTable('catalog/product_option_type_price')),
                '`store_value_price`.option_type_id=`main_table`.option_type_id AND '.$this->getConnection()->quoteInto('`store_value_price`.store_id=?', $store_id),
                array('store_price'=>'price','store_price_type'=>'price_type',
                'price'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price,`default_value_price`.price)'),
                'price_type'=>new Zend_Db_Expr('IFNULL(`store_value_price`.price_type,`default_value_price`.price_type)')));

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $optionIds
     * @param unknown_type $store_id
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function getValuesByOption($optionIds, $store_id)
    {
        if (!is_array($optionIds)) {
            $optionIds = array($optionIds);
        }

        $this->getSelect()
            ->where('`main_table`.option_type_id IN (?)', $optionIds);

        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $option
     * @return Mage_Catalog_Model_Resource_Product_Option_Value_Collection
     */
    public function addOptionToFilter($option)
    {
        if (empty($option)) {
            $this->addFieldToFilter('option_id', '');
        } elseif (is_array($option)) {
            $this->addFieldToFilter('option_id', array('in' => $option));
        } elseif ($option instanceof Mage_Catalog_Model_Product_Option) {
            $this->addFieldToFilter('option_id', $option->getId());
        } else {
            $this->addFieldToFilter('option_id', $option);
        }

        return $this;
    }
}
