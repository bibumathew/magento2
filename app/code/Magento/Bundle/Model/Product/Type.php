<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Bundle Type Model
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Bundle_Model_Product_Type extends Magento_Catalog_Model_Product_Type_Abstract
{
    /**
     * Product is composite
     *
     * @var bool
     */
    protected $_isComposite = true;

    /**
     * Cache key for Options Collection
     *
     * @var string
     */
    protected $_keyOptionsCollection        = '_cache_instance_options_collection';

    /**
     * Cache key for Selections Collection
     *
     * @var string
     */
    protected $_keySelectionsCollection     = '_cache_instance_selections_collection';

    /**
     * Cache key for used Selections
     *
     * @var string
     */
    protected $_keyUsedSelections           = '_cache_instance_used_selections';

    /**
     * Cache key for used selections ids
     *
     * @var string
     */
    protected $_keyUsedSelectionsIds        = '_cache_instance_used_selections_ids';

    /**
     * Cache key for used options
     *
     * @var string
     */
    protected $_keyUsedOptions              = '_cache_instance_used_options';

    /**
     * Cache key for used options ids
     *
     * @var string
     */
    protected $_keyUsedOptionsIds           = '_cache_instance_used_options_ids';

    /**
     * Product is configurable
     *
     * @var bool
     */
    protected $_canConfigure                = true;

    /**
     * Catalog data
     *
     * @var Magento_Catalog_Helper_Data
     */
    protected $_catalogData = null;

    /**
     * Catalog product
     *
     * @var Magento_Catalog_Helper_Product
     */
    protected $_catalogProduct = null;

    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento_Bundle_Model_OptionFactory
     */
    protected $_bundleOption;

    /**
     * @var Magento_Bundle_Model_Resource_Selection
     */
    protected $_bundleSelection;

    /**
     * @var Magento_Catalog_Model_Config
     */
    protected $_config;

    /**
     * @var Magento_Bundle_Model_Resource_Selection_CollectionFactory
     */
    protected $_bundleCollection;

    /**
     * @var Magento_Bundle_Model_Resource_BundleFactory
     */
    protected $_bundleFactory;

    /**
     * @var Magento_Bundle_Model_SelectionFactory $bundleModelSelection
     */
    protected $_bundleModelSelection;

    /**
     * Construct
     *
     * @param Magento_Catalog_Model_ProductFactory $productFactory
     * @param Magento_Catalog_Model_Product_Option $catalogProductOption
     * @param Magento_Eav_Model_Config $eavConfig
     * @param Magento_Catalog_Model_Product_Type $catalogProductType
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Helper_File_Storage_Database $fileStorageDb
     * @param Magento_Filesystem $filesystem
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param Magento_Core_Model_Logger $logger
     * @param Magento_Catalog_Helper_Product $catalogProduct
     * @param Magento_Catalog_Helper_Data $catalogData
     * @param Magento_Bundle_Model_SelectionFactory $bundleModelSelection
     * @param Magento_Bundle_Model_Resource_BundleFactory $bundleFactory
     * @param Magento_Bundle_Model_Resource_Selection_CollectionFactory $bundleCollection
     * @param Magento_Catalog_Model_Config $config
     * @param Magento_Bundle_Model_Resource_Selection $bundleSelection
     * @param Magento_Bundle_Model_OptionFactory $bundleOption
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Magento_Catalog_Model_ProductFactory $productFactory,
        Magento_Catalog_Model_Product_Option $catalogProductOption,
        Magento_Eav_Model_Config $eavConfig,
        Magento_Catalog_Model_Product_Type $catalogProductType,
        Magento_Core_Model_Event_Manager $eventManager,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Helper_File_Storage_Database $fileStorageDb,
        Magento_Filesystem $filesystem,
        Magento_Core_Model_Registry $coreRegistry,
        Magento_Core_Model_Logger $logger,
        Magento_Catalog_Helper_Product $catalogProduct,
        Magento_Catalog_Helper_Data $catalogData,
        Magento_Bundle_Model_SelectionFactory $bundleModelSelection,
        Magento_Bundle_Model_Resource_BundleFactory $bundleFactory,
        Magento_Bundle_Model_Resource_Selection_CollectionFactory $bundleCollection,
        Magento_Catalog_Model_Config $config,
        Magento_Bundle_Model_Resource_Selection $bundleSelection,
        Magento_Bundle_Model_OptionFactory $bundleOption,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->_bundleOption = $bundleOption;
        $this->_bundleSelection = $bundleSelection;
        $this->_config = $config;
        $this->_bundleCollection = $bundleCollection;
        $this->_bundleFactory = $bundleFactory;
        $this->_bundleModelSelection = $bundleModelSelection;
        parent::__construct($productFactory, $catalogProductOption, $eavConfig, $catalogProductType,
            $eventManager, $coreData, $fileStorageDb, $filesystem, $coreRegistry, $logger, $data);
    }

    /**
     * Return relation info about used products
     *
     * @return Magento_Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = new Magento_Object();
        $info->setTable('catalog_product_bundle_selection')
            ->setParentFieldName('parent_product_id')
            ->setChildFieldName('product_id');
        return $info;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return $this->_bundleSelection->getChildrenIds($parentId, $required);
    }

    /**
     * Retrieve parent ids array by requered child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return $this->_bundleSelection->getParentIdsByChild($childId);
    }

    /**
     * Return product sku based on sku_type attribute
     *
     * @param Magento_Catalog_Model_Product $product
     * @return string
     */
    public function getSku($product)
    {
        $sku = parent::getSku($product);

        if ($product->getData('sku_type')) {
            return $sku;
        } else {
            $skuParts = array($sku);

            if ($product->hasCustomOptions()) {
                $customOption = $product->getCustomOption('bundle_selection_ids');
                $selectionIds = unserialize($customOption->getValue());
                if (!empty($selectionIds)) {
                    $selections = $this->getSelectionsByIds($selectionIds, $product);
                    foreach ($selections->getItems() as $selection) {
                        $skuParts[] = $selection->getSku();
                    }
                }
            }

            return implode('-', $skuParts);
        }
    }

    /**
     * Return product weight based on weight_type attribute
     *
     * @param Magento_Catalog_Model_Product $product
     * @return decimal
     */
    public function getWeight($product)
    {
        if ($product->getData('weight_type')) {
            return $product->getData('weight');
        } else {
            $weight = 0;

            if ($product->hasCustomOptions()) {
                $customOption = $product->getCustomOption('bundle_selection_ids');
                $selectionIds = unserialize($customOption->getValue());
                $selections = $this->getSelectionsByIds($selectionIds, $product);
                foreach ($selections->getItems() as $selection) {
                    $qtyOption = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                    if ($qtyOption) {
                        $weight += $selection->getWeight() * $qtyOption->getValue();
                    } else {
                        $weight += $selection->getWeight();
                    }
                }
            }
            return $weight;
        }
    }

    /**
     * Check is virtual product
     *
     * @param Magento_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = unserialize($customOption->getValue());
            $selections = $this->getSelectionsByIds($selectionIds, $product);
            $virtualCount = 0;
            foreach ($selections->getItems() as $selection) {
                if ($selection->isVirtual()) {
                    $virtualCount++;
                }
            }
            if ($virtualCount == count($selections)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Before save type related data
     *
     * @param Magento_Catalog_Model_Product $product
     */
    public function beforeSave($product)
    {
        parent::beforeSave($product);

        // If bundle product has dynamic weight, than delete weight attribute
        if (!$product->getData('weight_type') && $product->hasData('weight')) {
            $product->setData('weight', false);
        }

        if ($product->getPriceType() == Magento_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) {
            $product->setData(
                'msrp_enabled', Magento_Catalog_Model_Product_Attribute_Source_Msrp_Type_Enabled::MSRP_ENABLE_NO
            );
            $product->unsetData('msrp');
            $product->unsetData('msrp_display_actual_price_type');
        }

        $product->canAffectOptions(false);

        if ($product->getCanSaveBundleSelections()) {
            $product->canAffectOptions(true);
            $selections = $product->getBundleSelectionsData();
            if ($selections) {
                if (!empty($selections)) {
                    $options = $product->getBundleOptionsData();
                    if ($options) {
                        foreach ($options as $option) {
                            if (empty($option['delete']) || 1 != (int)$option['delete']) {
                                $product->setTypeHasOptions(true);
                                if (1 == (int)$option['required']) {
                                    $product->setTypeHasRequiredOptions(true);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Save type related data
     *
     * @param Magento_Catalog_Model_Product $product
     * @return Magento_Bundle_Model_Product_Type
     */
    public function save($product)
    {
        parent::save($product);
        /* @var $resource Magento_Bundle_Model_Resource_Bundle */
        $resource = $this->_bundleFactory->create();

        $options = $product->getBundleOptionsData();
        if ($options) {
            $product->setIsRelationsChanged(true);

            foreach ($options as $key => $option) {
                if (isset($option['option_id']) && $option['option_id'] == '') {
                    unset($option['option_id']);
                }

                $optionModel = $this->_bundleOption->create()
                    ->setData($option)
                    ->setParentId($product->getId())
                    ->setStoreId($product->getStoreId());

                $optionModel->isDeleted((bool)$option['delete']);
                $optionModel->save();

                $options[$key]['option_id'] = $optionModel->getOptionId();
            }

            $usedProductIds      = array();
            $excludeSelectionIds = array();

            $selections = $product->getBundleSelectionsData();
            if ($selections) {
                foreach ($selections as $index => $group) {
                    foreach ($group as $key => $selection) {
                        if (isset($selection['selection_id']) && $selection['selection_id'] == '') {
                            unset($selection['selection_id']);
                        }

                        if (!isset($selection['is_default'])) {
                            $selection['is_default'] = 0;
                        }

                        $selectionModel = $this->_bundleModelSelection->create()
                            ->setData($selection)
                            ->setOptionId($options[$index]['option_id'])
                            ->setWebsiteId($this->_storeManager->getStore($product->getStoreId())->getWebsiteId())
                            ->setParentProductId($product->getId());

                        $selectionModel->isDeleted((bool)$selection['delete']);
                        $selectionModel->save();

                        $selection['selection_id'] = $selectionModel->getSelectionId();

                        if ($selectionModel->getSelectionId()) {
                            $excludeSelectionIds[] = $selectionModel->getSelectionId();
                            $usedProductIds[] = $selectionModel->getProductId();
                        }
                    }
                }

                $resource->dropAllUnneededSelections($product->getId(), $excludeSelectionIds);
                $resource->saveProductRelations($product->getId(), array_unique($usedProductIds));
            }

            if ($product->getData('price_type') != $product->getOrigData('price_type')) {
                $resource->dropAllQuoteChildItems($product->getId());
            }
        }

        return $this;
    }

    /**
     * Retrieve bundle options items
     *
     * @param Magento_Catalog_Model_Product $product
     * @return array
     */
    public function getOptions($product)
    {
        return $this->getOptionsCollection($product)->getItems();
    }

    /**
     * Retrieve bundle options ids
     *
     * @param Magento_Catalog_Model_Product $product
     * @return array
     */
    public function getOptionsIds($product)
    {
        return $this->getOptionsCollection($product)->getAllIds();
    }

    /**
     * Retrieve bundle option collection
     *
     * @param Magento_Catalog_Model_Product $product
     * @return Magento_Bundle_Model_Resource_Option_Collection
     */
    public function getOptionsCollection($product)
    {
        if (!$product->hasData($this->_keyOptionsCollection)) {
            $optionsCollection = $this->_bundleOption->create()->getResourceCollection()
                ->setProductIdFilter($product->getId())
                ->setPositionOrder();

            $storeId = $this->getStoreFilter($product);
            if ($storeId instanceof Magento_Core_Model_Store) {
                $storeId = $storeId->getId();
            }

            $optionsCollection->joinValues($storeId);
            $product->setData($this->_keyOptionsCollection, $optionsCollection);
        }
        return $product->getData($this->_keyOptionsCollection);
    }

    /**
     * Retrive bundle selections collection based on used options
     *
     * @param array $optionIds
     * @param Magento_Catalog_Model_Product $product
     * @return Magento_Bundle_Model_Resource_Selection_Collection
     */
    public function getSelectionsCollection($optionIds, $product)
    {
        $keyOptionIds = (is_array($optionIds) ? implode('_', $optionIds) : '');
        $key = $this->_keySelectionsCollection . $keyOptionIds;
        if (!$product->hasData($key)) {
            $storeId = $product->getStoreId();
            $selectionsCollection = $this->_bundleCollection
                ->create()
                ->addAttributeToSelect($this->_config->getProductAttributes())
                ->addAttributeToSelect('tax_class_id') //used for calculation item taxes in Bundle with Dynamic Price
                ->setFlag('require_stock_items', true)
                ->setFlag('product_children', true)
                ->setPositionOrder()
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->addFilterByRequiredOptions()
                ->setOptionIdsFilter($optionIds);

            if (!$this->_catalogData->isPriceGlobal() && $storeId) {
                $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
                $selectionsCollection->joinPrices($websiteId);
            }

            $product->setData($key, $selectionsCollection);
        }
        return $product->getData($key);
    }

    /**
     * Method is needed for specific actions to change given quote options values
     * according current product type logic
     * Example: the cataloginventory validation of decimal qty can change qty to int,
     * so need to change quote item qty option value too.
     *
     * @param   array           $options
     * @param   Magento_Object   $option
     * @param   mixed           $value
     * @param   Magento_Catalog_Model_Product $product
     * @return  Magento_Bundle_Model_Product_Type
     */
    public function updateQtyOption($options, Magento_Object $option, $value, $product)
    {
        $optionProduct      = $option->getProduct($product);
        $optionUpdateFlag   = $option->getHasQtyOptionUpdate();
        $optionCollection   = $this->getOptionsCollection($product);

        $selections = $this->getSelectionsCollection($optionCollection->getAllIds(), $product);

        foreach ($selections as $selection) {
            if ($selection->getProductId() == $optionProduct->getId()) {
                foreach ($options as &$option) {
                    if ($option->getCode() == 'selection_qty_'.$selection->getSelectionId()) {
                        if ($optionUpdateFlag) {
                            $option->setValue(intval($option->getValue()));
                        }
                        else {
                            $option->setValue($value);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare Quote Item Quantity
     *
     * @param mixed $qty
     * @param Magento_Catalog_Model_Product $product
     * @return int
     */
    public function prepareQuoteItemQty($qty, $product)
    {
        return intval($qty);
    }

    /**
     * Checking if we can sale this bundle
     *
     * @param Magento_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $salable = parent::isSalable($product);
        if (!is_null($salable)) {
            return $salable;
        }

        $optionCollection = $this->getOptionsCollection($product);

        if (!count($optionCollection->getItems())) {
            return false;
        }

        $requiredOptionIds = array();

        foreach ($optionCollection->getItems() as $option) {
            if ($option->getRequired()) {
                $requiredOptionIds[$option->getId()] = 0;
            }
        }

        $selectionCollection = $this->getSelectionsCollection($optionCollection->getAllIds(), $product);

        if (!count($selectionCollection->getItems())) {
            return false;
        }
        $salableSelectionCount = 0;
        foreach ($selectionCollection as $selection) {
            if ($selection->isSalable()) {
                $requiredOptionIds[$selection->getOptionId()] = 1;
                $salableSelectionCount++;
            }

        }

        return (array_sum($requiredOptionIds) == count($requiredOptionIds) && $salableSelectionCount);
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare of bundle selections options.
     *
     * @param Magento_Object $buyRequest
     * @param Magento_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Magento_Object $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        $selections = array();
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        $skipSaleableCheck = $this->_catalogProduct->getSkipSaleableCheck();
        $_appendAllSelections = (bool)$product->getSkipCheckRequiredOption() || $skipSaleableCheck;

        $options = $buyRequest->getBundleOption();
        if (is_array($options)) {
            $options = array_filter($options, 'intval');
            $qtys = $buyRequest->getBundleOptionQty();
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }
            $optionIds = array_keys($options);

            if (empty($optionIds) && $isStrictProcessMode) {
                return __('Please select options for the product.');
            }

            $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection = $this->getOptionsCollection($product);
            if (!$product->getSkipCheckRequiredOption() && $isStrictProcessMode) {
                foreach ($optionsCollection->getItems() as $option) {
                    if ($option->getRequired() && !isset($options[$option->getId()])) {
                        return __('Please select all required options.');
                    }
                }
            }
            $selectionIds = array();

            foreach ($options as $optionId => $selectionId) {
                if (!is_array($selectionId)) {
                    if ($selectionId != '') {
                        $selectionIds[] = (int)$selectionId;
                    }
                } else {
                    foreach ($selectionId as $id) {
                        if ($id != '') {
                            $selectionIds[] = (int)$id;
                        }
                    }
                }
            }
            // If product has not been configured yet then $selections array should be empty
            if (!empty($selectionIds)) {
                $selections = $this->getSelectionsByIds($selectionIds, $product);

                // Check if added selections are still on sale
                foreach ($selections->getItems() as $key => $selection) {
                    if (!$selection->isSalable() && !$skipSaleableCheck) {
                        $_option = $optionsCollection->getItemById($selection->getOptionId());
                        if (is_array($options[$_option->getId()]) && count($options[$_option->getId()]) > 1) {
                            $moreSelections = true;
                        } else {
                            $moreSelections = false;
                        }
                        if ($_option->getRequired()
                            && (!$_option->isMultiSelection() || ($_option->isMultiSelection() && !$moreSelections))
                        ) {
                            return __('The required options you selected are not available.');
                        }
                    }
                }

                $optionsCollection->appendSelections($selections, false, $_appendAllSelections);

                $selections = $selections->getItems();
            } else {
                $selections = array();
            }
        } else {
            $product->setOptionsValidationFail(true);
            $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $product->getTypeInstance()->getOptionsCollection($product);

            $optionIds = $product->getTypeInstance()->getOptionsIds($product);
            $selectionIds = array();

            $selectionCollection = $product->getTypeInstance()
                ->getSelectionsCollection(
                    $optionIds,
                    $product
                );

            $options = $optionCollection->appendSelections($selectionCollection, false, $_appendAllSelections);

            foreach ($options as $option) {
                if ($option->getRequired() && count($option->getSelections()) == 1) {
                    $selections = array_merge($selections, $option->getSelections());
                } else {
                    $selections = array();
                    break;
                }
            }
        }
        if (count($selections) > 0 || !$isStrictProcessMode) {
            $uniqueKey = array($product->getId());
            $selectionIds = array();

            // Shuffle selection array by option position
            usort($selections, array($this, 'shakeSelections'));

            foreach ($selections as $selection) {
                if ($selection->getSelectionCanChangeQty() && isset($qtys[$selection->getOptionId()])) {
                    $qty = (float)$qtys[$selection->getOptionId()] > 0 ? $qtys[$selection->getOptionId()] : 1;
                } else {
                    $qty = (float)$selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                }
                $qty = (float)$qty;

                $product->addCustomOption('selection_qty_' . $selection->getSelectionId(), $qty, $selection);
                $selection->addCustomOption('selection_id', $selection->getSelectionId());

                $beforeQty = 0;
                $customOption = $product->getCustomOption('product_qty_' . $selection->getId());
                if ($customOption && $customOption->getProduct()->getId() == $selection->getId()) {
                    $beforeQty = (float)$customOption->getValue();
                }
                $product->addCustomOption('product_qty_' . $selection->getId(), $qty + $beforeQty, $selection);

                /*
                 * Create extra attributes that will be converted to product options in order item
                 * for selection (not for all bundle)
                 */
                $price = $product->getPriceModel()->getSelectionFinalTotalPrice($product, $selection, 0, $qty);
                $attributes = array(
                    'price'         => $this->_storeManager->getStore()->convertPrice($price),
                    'qty'           => $qty,
                    'option_label'  => $selection->getOption()->getTitle(),
                    'option_id'     => $selection->getOption()->getId()
                );

                $_result = $selection->getTypeInstance()->prepareForCart($buyRequest, $selection);
                if (is_string($_result) && !is_array($_result)) {
                    return $_result;
                }

                if (!isset($_result[0])) {
                    return __('We cannot add this item to your shopping cart.');
                }

                $result[] = $_result[0]->setParentProductId($product->getId())
                    ->addCustomOption('bundle_option_ids', serialize(array_map('intval', $optionIds)))
                    ->addCustomOption('bundle_selection_attributes', serialize($attributes));

                if ($isStrictProcessMode) {
                    $_result[0]->setCartQty($qty);
                }

                $selectionIds[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $qty;
            }

            // "unique" key for bundle selection and add it to selections and bundle for selections
            $uniqueKey = implode('_', $uniqueKey);
            foreach ($result as $item) {
                $item->addCustomOption('bundle_identity', $uniqueKey);
            }
            $product->addCustomOption('bundle_option_ids', serialize(array_map('intval', $optionIds)));
            $product->addCustomOption('bundle_selection_ids', serialize($selectionIds));

            return $result;
        }

        return $this->getSpecifyOptionMessage();
    }

    /**
     * Retrieve message for specify option(s)
     *
     * @return string
     */
    public function getSpecifyOptionMessage()
    {
        return __('Please specify product option(s).');
    }

    /**
     * Retrieve bundle selections collection based on ids
     *
     * @param array $selectionIds
     * @param Magento_Catalog_Model_Product $product
     * @return Magento_Bundle_Model_Resource_Selection_Collection
     */
    public function getSelectionsByIds($selectionIds, $product)
    {
        sort($selectionIds);

        $usedSelections     = $product->getData($this->_keyUsedSelections);
        $usedSelectionsIds  = $product->getData($this->_keyUsedSelectionsIds);

        if (!$usedSelections || serialize($usedSelectionsIds) != serialize($selectionIds)) {
            $storeId = $product->getStoreId();
            $usedSelections = $this->_bundleCollection
                ->create()
                ->addAttributeToSelect('*')
                ->setFlag('require_stock_items', true)
                ->addStoreFilter($this->getStoreFilter($product))
                ->setStoreId($storeId)
                ->setPositionOrder()
                ->addFilterByRequiredOptions()
                ->setSelectionIdsFilter($selectionIds);

                if (!$this->_catalogData->isPriceGlobal() && $storeId) {
                    $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
                    $usedSelections->joinPrices($websiteId);
                }
            $product->setData($this->_keyUsedSelections, $usedSelections);
            $product->setData($this->_keyUsedSelectionsIds, $selectionIds);
        }
        return $usedSelections;
    }

    /**
     * Retrieve bundle options collection based on ids
     *
     * @param array $optionIds
     * @param Magento_Catalog_Model_Product $product
     * @return Magento_Bundle_Model_Resource_Option_Collection
     */
    public function getOptionsByIds($optionIds, $product)
    {
        sort($optionIds);

        $usedOptions     = $product->getData($this->_keyUsedOptions);
        $usedOptionsIds  = $product->getData($this->_keyUsedOptionsIds);

        if (!$usedOptions || serialize($usedOptionsIds) != serialize($optionIds)) {
            $usedOptions = $this->_bundleOption->create()->getResourceCollection()
                ->setProductIdFilter($product->getId())
                ->setPositionOrder()
                ->joinValues($this->_storeManager->getStore()->getId())
                ->setIdFilter($optionIds);
            $product->setData($this->_keyUsedOptions, $usedOptions);
            $product->setData($this->_keyUsedOptionsIds, $optionIds);
        }
        return $usedOptions;
    }

    /**
     * Prepare additional options/information for order item which will be
     * created from this product
     *
     * @param Magento_Catalog_Model_Product $product
     * @return array
     */
    public function getOrderOptions($product)
    {
        $optionArr = parent::getOrderOptions($product);
        $bundleOptions = array();

        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('bundle_option_ids');
            $optionIds = unserialize($customOption->getValue());
            $options = $this->getOptionsByIds($optionIds, $product);
            $customOption = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = unserialize($customOption->getValue());
            $selections = $this->getSelectionsByIds($selectionIds, $product);
            foreach ($selections->getItems() as $selection) {
                if ($selection->isSalable()) {
                    $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                    if ($selectionQty) {
                        $price = $product->getPriceModel()->getSelectionFinalTotalPrice($product, $selection, 0,
                            $selectionQty->getValue()
                        );

                        $option = $options->getItemById($selection->getOptionId());
                        if (!isset($bundleOptions[$option->getId()])) {
                            $bundleOptions[$option->getId()] = array(
                                'option_id' => $option->getId(),
                                'label' => $option->getTitle(),
                                'value' => array()
                            );
                        }

                        $bundleOptions[$option->getId()]['value'][] = array(
                            'title' => $selection->getName(),
                            'qty'   => $selectionQty->getValue(),
                            'price' => $this->_storeManager->getStore()->convertPrice($price)
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

    /**
     * Sort selections method for usort function
     * Sort selections by option position, selection position and selection id
     *
     * @param  Magento_Catalog_Model_Product $a
     * @param  Magento_Catalog_Model_Product $b
     * @return int
     */
    public function shakeSelections($a, $b)
    {
        $aPosition = array(
            $a->getOption()->getPosition(),
            $a->getOptionId(),
            $a->getPosition(),
            $a->getSelectionId()
        );
        $bPosition = array(
            $b->getOption()->getPosition(),
            $b->getOptionId(),
            $b->getPosition(),
            $b->getSelectionId()
        );
        if ($aPosition == $bPosition) {
            return 0;
        } else {
            return $aPosition < $bPosition ? -1 : 1;
        }
    }

    /**
     * Return true if product has options
     *
     * @param Magento_Catalog_Model_Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        $this->setStoreFilter($product->getStoreId(), $product);
        $optionIds  = $this->getOptionsCollection($product)->getAllIds();
        $collection = $this->getSelectionsCollection($optionIds, $product);

        if (count($collection) > 0 || $product->getOptions()) {
            return true;
        }

        return false;
    }

    /**
     * Allow for updates of chidren qty's
     *
     * @param Magento_Catalog_Model_Product $product
     * @return boolean true
     */
    public function getForceChildItemQtyChanges($product)
    {
        return true;
    }

    /**
     * Retrieve additional searchable data from type instance
     * Using based on product id and store_id data
     *
     * @param Magento_Catalog_Model_Product $product
     * @return array
     */
    public function getSearchableData($product)
    {
        $searchData = parent::getSearchableData($product);

        $optionSearchData = $this->_bundleOption->create()
            ->getSearchableData($product->getId(), $product->getStoreId());
        if ($optionSearchData) {
            $searchData = array_merge($searchData, $optionSearchData);
        }

        return $searchData;
    }

    /**
     * Check if product can be bought
     *
     * @param Magento_Catalog_Model_Product $product
     * @return Magento_Bundle_Model_Product_Type
     * @throws Magento_Core_Exception
     */
    public function checkProductBuyState($product)
    {
        parent::checkProductBuyState($product);
        $productOptionIds   = $this->getOptionsIds($product);
        $productSelections  = $this->getSelectionsCollection($productOptionIds, $product);
        $selectionIds       = $product->getCustomOption('bundle_selection_ids');
        $selectionIds       = unserialize($selectionIds->getValue());
        $buyRequest         = $product->getCustomOption('info_buyRequest');
        $buyRequest         = new Magento_Object(unserialize($buyRequest->getValue()));
        $bundleOption       = $buyRequest->getBundleOption();

        if (empty($bundleOption)) {
            throw new Magento_Core_Exception($this->getSpecifyOptionMessage());
        }

        $skipSaleableCheck = $this->_catalogProduct->getSkipSaleableCheck();
        foreach ($selectionIds as $selectionId) {
            /* @var $selection Magento_Bundle_Model_Selection */
            $selection = $productSelections->getItemById($selectionId);
            if (!$selection || (!$selection->isSalable() && !$skipSaleableCheck)) {
                throw new Magento_Core_Exception(__('The required options you selected are not available.'));
            }
        }

        $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);
        $optionsCollection = $this->getOptionsCollection($product);
        foreach ($optionsCollection->getItems() as $option) {
            if ($option->getRequired() && empty($bundleOption[$option->getId()])) {
                throw new Magento_Core_Exception(__('Please select all required options.'));
            }
        }

        return $this;
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param  Magento_Catalog_Model_Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        $groups = array();
        $allProducts = array();
        $hasRequiredOptions = false;
        foreach ($this->getOptions($product) as $option) {
            $groupProducts = array();
            foreach ($this->getSelectionsCollection(array($option->getId()), $product) as $childProduct) {
                $groupProducts[] = $childProduct;
                $allProducts[] = $childProduct;
            }
            if ($option->getRequired()) {
                $groups[] = $groupProducts;
                $hasRequiredOptions = true;
            }
        }
        if (!$hasRequiredOptions) {
            $groups = array($allProducts);
        }
        return $groups;
    }

    /**
     * Prepare selected options for bundle product
     *
     * @param  Magento_Catalog_Model_Product $product
     * @param  Magento_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $option     = $buyRequest->getBundleOption();
        $optionQty  = $buyRequest->getBundleOptionQty();

        $option     = (is_array($option)) ? array_filter($option, 'intval') : array();
        $optionQty  = (is_array($optionQty)) ? array_filter($optionQty, 'intval') : array();

        $options = array(
            'bundle_option'     => $option,
            'bundle_option_qty' => $optionQty
        );

        return $options;
    }

    /**
     * Check if product can be configured
     *
     * @param Magento_Catalog_Model_Product $product
     * @return bool
     */
    public function canConfigure($product)
    {
        return $product instanceof Magento_Catalog_Model_Product
            && $product->isAvailable()
            && parent::canConfigure($product);
    }

    /**
     * Check if Minimum Advertise Price is enabled at least in one option
     *
     * @param Magento_Catalog_Model_Product $product
     * @param int $visibility
     * @return bool|null
     */
    public function isMapEnabledInOptions($product, $visibility = null)
    {
        /**
         * @TODO: In order to clarify is MAP enabled for product we can check associated products.
         * Commented for future improvements.
         */
        /*
        $collection = $this->getUsedProductCollection($product);
        $helper = $this->_catalogData;

        $result = null;
        $parentVisibility = $product->getMsrpDisplayActualPriceType();
        if ($parentVisibility === null) {
            $parentVisibility = $helper->getMsrpDisplayActualPriceType();
        }
        $visibilities = array($parentVisibility);
        foreach ($collection as $item) {
            if ($helper->canApplyMsrp($item)) {
                $productVisibility = $item->getMsrpDisplayActualPriceType();
                if ($productVisibility === null) {
                    $productVisibility = $helper->getMsrpDisplayActualPriceType();
                }
                $visibilities[] = $productVisibility;
                $result = true;
            }
        }

        if ($result && $visibility !== null) {
            if ($visibilities) {
                $maxVisibility = max($visibilities);
                $result = $result && $maxVisibility == $visibility;
            } else {
                $result = false;
            }
        }

        return $result;
        */

        return null;
    }

    /**
     * Delete data specific for Bundle product type
     *
     * @param Magento_Catalog_Model_Product $product
     */
    public function deleteTypeSpecificData(Magento_Catalog_Model_Product $product)
    {
    }
}
