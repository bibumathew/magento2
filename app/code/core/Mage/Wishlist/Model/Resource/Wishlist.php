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
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist model resource
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_Model_Resource_Wishlist extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_itemsCount             = null;

    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_customerIdFieldName    = 'customer_id';

    /**
     * Enter description here ...
     *
     */
    protected function _construct()
    {
        $this->_init('wishlist/wishlist', 'wishlist_id');
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    public function getCustomerIdFieldName()
    {
        return $this->_customerIdFieldName;
    }

    /**
     * Enter description here ...
     *
     * @param unknown_type $fieldName
     * @return Mage_Wishlist_Model_Resource_Wishlist
     */
    public function setCustomerIdFieldName($fieldName)
    {
        $this->_customerIdFieldName = $fieldName;
        return $this;
    }

    /**
     * Enter description here ...
     *
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     * @return unknown
     */
    public function fetchItemsCount(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        if (is_null($this->_itemsCount)) {
            $collection = $wishlist->getProductCollection()
                //->addAttributeToFilter('store_id', array('in'=>$wishlist->getSharedStoreIds()))
                ->addStoreFilter();

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($collection);

            $this->_itemsCount = $collection->getSize();
        }

        return $this->_itemsCount;
    }
}
