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
 * Layer category filter
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Model_Layer_Filter_Category extends Magento_Catalog_Model_Layer_Filter_Abstract
{
    /**
     * Active Category Id
     *
     * @var int
     */
    protected $_categoryId;

    /**
     * Applied Category
     *
     * @var Magento_Catalog_Model_Category
     */
    protected $_appliedCategory = null;

    /**
     * Core data
     *
     * @var Magento_Core_Helper_Data
     */
    protected $_coreData = null;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * Category factory
     *
     * @var Magento_Catalog_Model_CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Construct
     *
     * @param Magento_Catalog_Model_Layer_Filter_ItemFactory $filterItemFactory
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Catalog_Model_Layer $catalogLayer
     * @param Magento_Catalog_Model_CategoryFactory $categoryFactory
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Model_Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Magento_Catalog_Model_Layer_Filter_ItemFactory $filterItemFactory,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Catalog_Model_Layer $catalogLayer,
        Magento_Catalog_Model_CategoryFactory $categoryFactory,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Model_Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_coreData = $coreData;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($filterItemFactory, $storeManager, $catalogLayer, $data);
        $this->_requestVar = 'cat';
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed
     */
    public function getResetValue()
    {
        if ($this->_appliedCategory) {
            /**
             * Revert path ids
             */
            $pathIds = array_reverse($this->_appliedCategory->getPathIds());
            $curCategoryId = $this->getLayer()->getCurrentCategory()->getId();
            if (isset($pathIds[1]) && $pathIds[1] != $curCategoryId) {
                return $pathIds[1];
            }
        }
        return null;
    }

    /**
     * Apply category filter to layer
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @param   Magento_Core_Block_Abstract $filterBlock
     * @return  Magento_Catalog_Model_Layer_Filter_Category
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = (int)$request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
        $this->_categoryId = $filter;
        $this->_coreRegistry->register('current_category_filter', $this->getCategory(), true);

        $this->_appliedCategory = $this->_categoryFactory->create()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->load($filter);

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                ->addCategoryFilter($this->_appliedCategory);

            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_appliedCategory->getName(), $filter)
            );
        }

        return $this;
    }

    /**
     * Validate category for be using as filter
     *
     * @param   Magento_Catalog_Model_Category $category
     * @return unknown
     */
    protected function _isValidCategory($category)
    {
        return $category->getId();
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return __('Category');
    }

    /**
     * Get selected category object
     *
     * @return Magento_Catalog_Model_Category
     */
    public function getCategory()
    {
        if (!is_null($this->_categoryId)) {
            /** @var Magento_Catalog_Model_Category $category */
            $category = $this->_categoryFactory->create()
                ->load($this->_categoryId);
            if ($category->getId()) {
                return $category;
            }
        }
        return $this->getLayer()->getCurrentCategory();
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $categoty   = $this->getCategory();
        /** @var $category Magento_Catalog_Model_Categeory */
        $categories = $categoty->getChildrenCategories();

        $this->getLayer()->getProductCollection()
            ->addCountToCategories($categories);

        $data = array();
        foreach ($categories as $category) {
            if ($category->getIsActive() && $category->getProductCount()) {
                $data[] = array(
                    'label' => $this->_coreData->escapeHtml($category->getName()),
                    'value' => $category->getId(),
                    'count' => $category->getProductCount(),
                );
            }
        }
        return $data;
    }
}
