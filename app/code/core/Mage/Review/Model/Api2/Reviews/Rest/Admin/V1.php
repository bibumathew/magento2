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
 * @package     Mage_Api2
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 class for reviews
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Review_Model_Api2_Reviews_Rest_Admin_V1 extends Mage_Review_Model_Api2_Reviews_Rest
{
    /**
     * Create new review
     *
     * @param $data
     * @return bool
     */
    protected function _create(array $data)
    {
        $required = array('product_id', 'status_id', 'stores', 'nickname', 'title', 'detail');
        $valuable = array('product_id', 'status_id', 'stores', 'nickname', 'title', 'detail');

        $this->_validate($data, $required, $valuable);

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load($data['product_id']);
        if (!$product->getId()) {
            $this->_critical('Product not found', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        /** @var $review Mage_Review_Model_Review */
        $review = Mage::getModel('review/review')->setData($data);
        try {
            $entityId = $review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE);
            $review->setEntityId($entityId)
                ->setEntityPkValue($product->getId())
                ->setStoreId($product->getStoreId())
                ->setStatusId($data['status_id'])
                ->save();

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e){
            $this->_critical('An error occurred while saving review.', Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $this->_getLocation($review);
    }

    /**
     * Validate status input data
     *
     * @param array $data
     * @param array $required
     * @param array $valueable
     */
    protected function _validate(array $data, array $required = array(), array $valueable = array())
    {
        parent::_validate($data, $required, $valueable);

        $validStatusList = array();
        $statusList = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toArray();

        foreach ($statusList['items'] as $status) {
            $validStatusList[] = $status['status_id'];
        }

        if (!in_array($data['status_id'], $validStatusList)) {
            $this->_critical('Invalid status provided', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        if (!is_array($data['stores'])) {
            $this->_critical('Invalid stores provided', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        $validStores = array();
        foreach (Mage::app()->getStores() as $store) {
            $validStores[] = $store->getId();
        }
        foreach ($data['stores'] as $store) {
            if (!in_array($store, $validStores)) {
                $this->_critical(sprintf('Invalid store ID "%s" provided', $store),
                    Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }
    }

    /**
     * Get review location
     *
     * @param Mage_Core_Model_Abstract $review
     * @return string
     */
    protected function _getLocation(Mage_Core_Model_Abstract $review)
    {
        return $this->getType() . '/' . $review->getId();
    }
}
