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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Quote item collection
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Sales_Model_Mysql4_Quote_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Collection quote instance
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    protected function _construct()
    {
        $this->_init('sales/quote_item');
    }

    public function getStoreId()
    {
        return $this->_quote->getStoreId();
    }

    public function setQuote($quote)
    {
        $this->_quote = $quote;
        $this->addFieldToFilter('quote_id', $quote->getId());
        return $this;
    }

    protected function _afterLoad()
    {
        $productCollection = $this->_getProductCollection();
        $recollectQuote = false;

        foreach ($this as $item) {
            if ($productCollection) {
                $product = $productCollection->getItemById($item->getProductId());
            } else {
                $product = false;
            }

            if ($this->_quote) {
                $item->setQuote($this->_quote);
            }

            if (!$product) {
                $item->isDeleted(true);
                $recollectQuote = true;
                continue;
            }

            if ($item->getSuperProductId()) {
                $superProduct = $productCollection->getItemById($item->getSuperProductId());
                if (!$superProduct) {
                    $item->isDeleted(true);
                    $recollectQuote = true;
                    continue;
                }
            }
            else {
                $superProduct = null;
            }

            $itemProduct = clone $product;
            if ($superProduct) {
                $itemProduct->setSuperProduct($superProduct);
                $item->setSuperProduct($superProduct);
            }

            $item->importCatalogProduct($itemProduct);
            $item->checkData();
        }

        if ($recollectQuote && $this->_quote) {
            $this->_quote->collectTotals();
        }
        return $this;
    }

    protected function _getProductCollection()
    {
        $productIds = array();
        foreach ($this as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            if ($item->getSuperProductId()) {
                $productIds[$item->getSuperProductId()] = $item->getSuperProductId();
            }
            if ($item->getParentProductId()) {
                $productIds[$item->getSuperProductId()] = $item->getParentProductId();
            }
        }

        if (empty($productIds)) {
            return false;
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($this->getStoreId())
            ->addIdFilter($productIds)
            ->addAttributeToSelect('*')
            ->addStoreFilter()
            ->addUrlRewrite();

        if (Mage::app()->useCache('checkout_quote')) {
            $collection->initCache(
                $this->_getCacheInstance(),
                $this->_cacheConf['prefix'].'_PRODUCTS',
                $this->_getCacheTags()
            );
        }

        return $collection;
    }
}
