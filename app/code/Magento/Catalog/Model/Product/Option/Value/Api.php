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
 * Catalog product option values api
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Model_Product_Option_Value_Api extends Magento_Catalog_Model_Api_Resource
{
    /**
     * Catalog data
     *
     * @var Magento_Catalog_Helper_Data
     */
    protected $_catalogData = null;

    /**
     * @param Magento_Catalog_Helper_Data $catalogData
     */
    public function __construct(
        Magento_Catalog_Helper_Data $catalogData
    ) {
        $this->_catalogData = $catalogData;
    }

    /**
     * Retrieve values from specified option
     *
     * @param string $optionId
     * @param int|string|null $store
     * @return array
     */
    public function items($optionId, $store = null)
    {
        /** @var $option Magento_Catalog_Model_Product_Option */
        $option = $this->_prepareOption($optionId, $store);
        $productOptionValues = $option->getValuesCollection();
        $result = array();
        foreach($productOptionValues as $value){
            $result[] = array(
                'value_id' => $value->getId(),
                'title' => $value->getTitle(),
                'price' => $value->getPrice(),
                'price_type' => $value->getPriceType(),
                'sku' => $value->getSku(),
                'sort_order' => $value->getSortOrder()
            );
        }
        return $result;
    }

    /**
     * Retrieve specified option value info
     *
     * @param string $valueId
     * @param int|string|null $store
     * @return array
     */
    public function info($valueId, $store = null)
    {
        /** @var $productOptionValue Magento_Catalog_Model_Product_Option_Value */
        $productOptionValue = Mage::getModel('Magento_Catalog_Model_Product_Option_Value')->load($valueId);
        if (!$productOptionValue->getId()) {
            $this->_fault('value_not_exists');
        }
        $storeId = $this->_getStoreId($store);
        $productOptionValues = $productOptionValue
                ->getValuesByOption(
                    array($valueId),
                    $productOptionValue->getOptionId(),
                    $storeId
                )
                ->addTitleToResult($storeId)
                ->addPriceToResult($storeId);

        $result = $productOptionValues->toArray();
        // reset can be used as the only item is expected
        $result = reset($result['items']);
        if (empty($result)) {
            $this->_fault('value_not_exists');
        }
        // map option_type_id to value_id
        $result['value_id'] = $result['option_type_id'];
        unset($result['option_type_id']);
        return $result;
    }

    /**
     * Add new values to select option
     *
     * @param string $optionId
     * @param array $data
     * @param int|string|null $store
     * @return bool
     */
    public function add($optionId, $data, $store = null)
    {
        /** @var $option Magento_Catalog_Model_Product_Option */
        $option = $this->_prepareOption($optionId, $store);
        /** @var $optionValueModel Magento_Catalog_Model_Product_Option_Value */
        $optionValueModel = Mage::getModel('Magento_Catalog_Model_Product_Option_Value');
        $optionValueModel->setOption($option);
        foreach ($data as &$optionValue) {
            foreach ($optionValue as &$value) {
                $value = $this->_catalogData->stripTags($value);
            }
        }
        $optionValueModel->setValues($data);
        try {
            $optionValueModel->saveValues();
        } catch (Exception $e) {
            $this->_fault('add_option_value_error', $e->getMessage());
        }
        return true;
    }

    /**
     * Update value to select option
     *
     * @param string $valueId
     * @param array $data
     * @param int|string|null $store
     * @return bool
     */
    public function update($valueId, $data, $store = null)
    {
        /** @var $productOptionValue Magento_Catalog_Model_Product_Option_Value */
        $productOptionValue = Mage::getModel('Magento_Catalog_Model_Product_Option_Value')->load($valueId);
        if (!$productOptionValue->getId()) {
            $this->_fault('value_not_exists');
        }

        /** @var $option Magento_Catalog_Model_Product_Option */
        $option = $this->_prepareOption($productOptionValue->getOptionId(), $store);
        if (!$option->getId()) {
            $this->_fault('option_not_exists');
        }
        $productOptionValue->setOption($option);
        // Sanitize data
        foreach ($data as $key => $value) {
            $data[$key] = $this->_catalogData->stripTags($value);
        }
        if (!isset($data['title']) OR empty($data['title'])) {
            $this->_fault('option_value_title_required');
        }
        $data['option_type_id'] = $valueId;
        $data['store_id'] = $this->_getStoreId($store);
        $productOptionValue->addValue($data);
        $productOptionValue->setData($data);

        try {
            $productOptionValue->save()->saveValues();
        } catch (Exception $e) {
            $this->_fault('update_option_value_error', $e->getMessage());
        }

        return true;
    }

    /**
     * Delete value from select option
     *
     * @param int $valueId
     * @return boolean
     */
    public function remove($valueId)
    {
        /** @var $optionValue Magento_Catalog_Model_Product_Option_Value */
        $optionValue = Mage::getModel('Magento_Catalog_Model_Product_Option_Value')->load($valueId);
        if (!$optionValue->getId()) {
            $this->_fault('value_not_exists');
        }

        // check values count
        if(count($this->items($optionValue->getOptionId())) <= 1){
            $this->_fault('cant_delete_last_value');
        }

        try {
            $optionValue->delete();
        } catch (Magento_Core_Exception $e) {
            $this->_fault('not_deleted', $e->getMessage());
        }

        return true;
    }

    /**
     * Load option by id and store
     *
     * @param string $optionId
     * @param int|string|null $store
     * @return Magento_Catalog_Model_Product_Option
     */
    protected function _prepareOption($optionId, $store = null)
    {
        /** @var $option Magento_Catalog_Model_Product_Option */
        $option = Mage::getModel('Magento_Catalog_Model_Product_Option');
        if (is_string($store) || is_integer($store)) {
            $storeId = $this->_getStoreId($store);
            $option->setStoreId($storeId);
        }
        $option->load($optionId);
        if (isset($storeId)) {
            $option->setData('store_id', $storeId);
        }
        if (!$option->getId()) {
            $this->_fault('option_not_exists');
        }
        if ($option->getGroupByType() != Magento_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
            $this->_fault('invalid_option_type');
        }
        return $option;
    }

}
