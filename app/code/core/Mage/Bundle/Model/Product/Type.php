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
 * Bundle Type Model
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Abstract
{
    protected $_isComposite = true;

    protected $_optionsCollection;
    protected $_selectionsCollection;
    protected $_storeFilter = null;

    protected $_usedSelections = null;
    protected $_usedSelectionsIds = null;
    protected $_usedOptions = null;
    protected $_usedOptionsIds = null;

    const SHIPMENT_SEPARATELY = 1;
    const SHIPMENT_TOGETHER = 0;

    /**
     * Return product sku based on sku_type attribute
     *
     * @return string
     */
    public function getSku()
    {
        $sku = parent::getSku();

        if ($this->getProduct()->getData('sku_type')) {
            return $sku;
        } else {
            $skuParts = array($sku);

            if ($this->getProduct()->hasCustomOptions()) {
                $customOption = $this->getProduct()->getCustomOption('bundle_selection_ids');
                $selectionIds = unserialize($customOption->getValue());
                $selections = $this->getSelectionsByIds($selectionIds);
                foreach ($selections->getItems() as $selection) {
                    $skuParts[] = $selection->getSku();
                }
            }

            return implode('-', $skuParts);
        }
    }

    /**
     * Return product weight based on weight_type attribute
     *
     * @return decimal
     */
    public function getWeight()
    {
        if ($this->getProduct()->getData('weight_type')) {
            return $this->getProduct()->getData('weight');
        } else {
            $weight = 0;

            if ($this->getProduct()->hasCustomOptions()) {
                $customOption = $this->getProduct()->getCustomOption('bundle_selection_ids');
                $selectionIds = unserialize($customOption->getValue());
                $selections = $this->getSelectionsByIds($selectionIds);
                foreach ($selections->getItems() as $selection) {
                    $weight += $selection->getWeight();
                }
            }

            return $weight;
        }
    }

    public function save()
    {
        parent::save();

        if ($options = $this->getProduct()->getBundleOptionsData()) {

            foreach ($options as $key => $option) {
                if (!$option['option_id']) {
                    unset($option['option_id']);
                }

                $optionModel = Mage::getModel('bundle/option')
                    ->setData($option)
                    ->setParentId($this->getProduct()->getId())
                    ->setStoreId($this->getProduct()->getStoreId());

                $optionModel->isDeleted((bool)$option['delete']);
                $optionModel->save();

                $options[$key]['option_id'] = $optionModel->getOptionId();
            }

            if ($selections = $this->getProduct()->getBundleSelectionsData()) {
                foreach ($selections as $index => $group) {
                    foreach ($group as $key => $selection) {
                        if (isset($selection['selection_id']) && $selection['selection_id'] == '') {
                            unset($selection['selection_id']);
                        }

                        if (!isset($selection['is_default'])) {
                            $selection['is_default'] = 0;
                        }

                        $selectionModel = Mage::getModel('bundle/selection')
                            ->setData($selection)
                            ->setOptionId($options[$index]['option_id'])
                            ->setParentProductId($this->getProduct()->getId());

                        $selectionModel->isDeleted((bool)$selection['delete']);
                        $selectionModel->save();

                        $selection['selection_id'] = $selectionModel->getSelectionId();
                    }
                }
            }

            if ($this->getProduct()->getData('price_type') != $this->getProduct()->getOrigData('price_type')) {
                Mage::getResourceModel('bundle/bundle')->dropAllQuoteChildItems($this->getProduct()->getId());
            }
        }

        return $this;
    }

    /**
     * Retrieve bundle options items
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->getOptionsCollection()->getItems();
    }

    /**
     * Retrieve bundle options ids
     *
     * @return array
     */
    public function getOptionsIds()
    {
        return $this->getOptionsCollection()->getAllIds();
    }

    /**
     * Retrieve bundle option collection
     *
     * @return Mage_Bundle_Model_Mysql4_Option_Collection
     */
    public function getOptionsCollection()
    {
        if (!$this->_optionsCollection) {
            $this->_optionsCollection = Mage::getModel('bundle/option')->getResourceCollection()
                ->setProductIdFilter($this->getProduct()->getId())
                ->setPositionOrder()
                ->joinValues($this->getStoreFilter());
        }
        return $this->_optionsCollection;
    }

    /**
     * Retrive bundle selections collection based on used options
     *
     * @param array $optionIds
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function getSelectionsCollection($optionIds)
    {
        if (!$this->_selectionsCollection) {
            $this->_selectionsCollection = Mage::getResourceModel('bundle/selection_collection')
                ->addAttributeToSelect('*')
                ->setPositionOrder()
                ->setOptionIdsFilter($optionIds);
        }
        return $this->_selectionsCollection;
    }

    /**
     * Checking if we can sale this bundle
     *
     * @return bool
     */
    public function isSalable()
    {
        if (!parent::isSalable()) {
            return false;
        }

        $optionCollection = $this->getOptionsCollection();

        $optionIds = array();
        foreach ($optionCollection->getItems() as $option) {
            if ($option->getRequired()) {
                $optionIds[$option->getId()] = 0;
            }
        }

        $selectionCollection = $this->getSelectionsCollection(array_keys($optionIds));
        foreach ($selectionCollection as $selection) {
            if ($selection->isSalable()) {
                $optionIds[$selection->getOptionId()] = 1;
            }
        }
        return (array_sum($optionIds) == count($optionIds));
    }

    /**
     * Retrive store filter for associated products
     *
     * @return int|Mage_Core_Model_Store
     */
    public function getStoreFilter()
    {
        return $this->_storeFilter;
    }

    /**
     * Set store filter for associated products
     *
     * @param $store int|Mage_Core_Model_Store
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    public function setStoreFilter($store=null) {
        $this->_storeFilter = $store;
        return $this;
    }

    /**
     * Initialize product(s) for add to cart process
     *
     * @param   Varien_Object $buyRequest
     * @return  unknown
     */
    public function prepareForCart(Varien_Object $buyRequest)
    {
        $result = parent::prepareForCart($buyRequest);
        if (is_string($result)) {
            return $result;
        }

        $selections = array();

        $product = $this->getProduct();

        if ($options = $buyRequest->getBundleOption()) {
            $qtys = $buyRequest->getBundleOptionQty();
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }
            $optionIds = array_keys($options);

            $optionsCollection = $this->getOptionsByIds($optionIds);
            foreach ($optionsCollection as $option) {
                if ($option->getRequired() && !isset($options[$option->getId()])) {
                    return Mage::helper('bundle')->__('Required options not selected.');
                }
            }

            $selectionIds = array();

            foreach ($options as $optionId => $selectionId) {
                if (!is_array($selectionId)) {
                    if ($selectionId != '') {
                        $selectionIds[] = $selectionId;
                    }
                } else {
                    foreach ($selectionId as $id) {
                        if ($id != '') {
                            $selectionIds[] = $id;
                        }
                    }
                }
            }

            $selections = $this->getSelectionsByIds($selectionIds)->getItems();
        } else {
            $product->getTypeInstance()->setStoreFilter($product->getStoreId());

            $optionCollection = $product->getTypeInstance()->getOptionsCollection();

            $optionIds = $product->getTypeInstance()->getOptionsIds();
            $selectionIds = array();

            $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
                    $product->getTypeInstance()->getOptionsIds()
                );

            $options = $optionCollection->appendSelections($selectionCollection);

            foreach ($options as $option) {
                if ($option->getRequired() && count($option->getSelections()) == 1) {
                    $selections = array_merge($selections, $option->getSelections());
                } else {
                    $selections = array();
                    break;
                }
            }
        }

        if (count($selections) > 0) {

            $uniqueKey = array($product->getId());
            $selectionIds = array();

            foreach ($selections as $selection) {
                if ($selection->getSelectionCanChangeQty() && isset($qtys[$selection->getOptionId()])) {
                    $qty = $qtys[$selection->getOptionId()] > 0 ? $qtys[$selection->getOptionId()] : 1;
                } else {
                    $qty = $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                }

                $product->addCustomOption('selection_qty_' . $selection->getSelectionId(), $qty, $selection);

                if ($customOption = $product->getCustomOption('product_qty_' . $selection->getId())) {
                    $customOption->setValue($customOption->getValue() + $qty);
                } else {
                    $product->addCustomOption('product_qty_' . $selection->getId(), $qty, $selection);
                }

                //if (!$product->getPriceType()) {
                    $result[] = $selection->setParentProductId($product->getId())
                        ->addCustomOption('bundle_option_ids', serialize($optionIds))
                        ->setCartQty($qty);
                //}
                $selectionIds[] = $selection->getSelectionId();
                $uniqueKey[] = $selection->getSelectionId();
                $uniqueKey[] = $qty;
            }
            /**
             * "unique" key for bundle selection and add it to selections and bundle for selections
             */
            $uniqueKey = implode('_', $uniqueKey);
            foreach ($result as $item) {
                $item->addCustomOption('bundle_identity', $uniqueKey);
            }
            $product->addCustomOption('bundle_option_ids', serialize($optionIds));
            $product->addCustomOption('bundle_selection_ids', serialize($selectionIds));

            /**
             * Saving Bundle Shipment Type
             */
            $product->addCustomOption('bundle_shipment_type', $product->getShipmentType());

            /**
             * Product Prices calculations
             */
            if ($product->getPriceType()) {
                $product->addCustomOption('product_calculations', self::CALCULATE_PARENT);
            } else {
                $product->addCustomOption('product_calculations', self::CALCULATE_CHILD);
            }

            return $result;
        }

        return Mage::helper('bundle')->__('Please specify the bundle option(s)');
    }

    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array $selectionIds
     * @return Mage_Bundle_Model_Mysql4_Selection_Collection
     */
    public function getSelectionsByIds($selectionIds)
    {
        sort($selectionIds);
        if (!$this->_usedSelections || serialize($this->_usedSelectionsIds) != serialize($selectionIds)) {
            $this->_usedSelections = Mage::getResourceModel('bundle/selection_collection')
                    ->addAttributeToSelect('*')
                    ->setSelectionIdsFilter($selectionIds);
            $this->_usedSelectionsIds = $selectionIds;
        }
        return $this->_usedSelections;
    }

    /**
     * Retrieve bundle options collection based on ids
     *
     * @param array $optionIds
     * @return Mage_Bundle_Model_Mysql4_Option_Collection
     */
    public function getOptionsByIds($optionIds)
    {
        sort($optionIds);
        if (!$this->_usedOptions || serialize($this->_usedOptionsIds) != serialize($optionIds)) {
            $this->_usedOptions = Mage::getModel('bundle/option')->getResourceCollection()
                    ->setProductIdFilter($this->getProduct()->getId())
                    ->joinValues(Mage::app()->getStore()->getId())
                    ->setIdFilter($optionIds);
            $this->_usedOptionsIds = $optionIds;
        }
        return $this->_usedOptions;
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @return attay
     */

    public function getOrderOptions()
    {
        $optionArr = parent::getOrderOptions();

        $bundleOptions = array();

        $product = $this->getProduct();

        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('bundle_option_ids');
            $optionIds = unserialize($customOption->getValue());
            $options = $this->getOptionsByIds($optionIds);
            $customOption = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = unserialize($customOption->getValue());
            $selections = $this->getSelectionsByIds($selectionIds);
            foreach ($selections->getItems() as $selection) {
                if ($selection->isSalable()) {
                    $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                    if ($selectionQty) {
                        $price = $product->getPriceModel()->getSelectionPrice($product, $selection, $selectionQty->getValue());
                        $option = $options->getItemById($selection->getOptionId());
                        if (!isset($bundleOptions[$option->getId()])) {
                            $bundleOptions[$option->getId()] = array(
                                'label' => $option->getTitle(),
                                'value' => array()
                            );
                        }

                        $bundleOptions[$option->getId()]['value'][] = array(
                            'title' => $selection->getName(),
                            'qty'   => $selectionQty->getValue(),
                            'price' => $price
                        );

                    }
                }
            }
        }

        $optionArr['bundle_options'] = $bundleOptions;

        /**
         * Product Prices calculations save
         */
        if ($product->getPriceType()) {
            $optionArr['product_calculations'] = self::CALCULATE_PARENT;
        } else {
            $optionArr['product_calculations'] = self::CALCULATE_CHILD;
        }

        $optionArr['shipment_type'] = $product->getShipmentType();

        return $optionArr;
    }
}
