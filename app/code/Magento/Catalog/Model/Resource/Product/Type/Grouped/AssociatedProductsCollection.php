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
 * Associated products collection
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Model_Resource_Product_Type_Grouped_AssociatedProductsCollection
    extends Magento_Catalog_Model_Resource_Product_Link_Product_Collection
{

    /**
     * @var Magento_Core_Model_Config
     */
    protected $_coreConfig;

    /**
     * Constructor
     *
     * @param Magento_Data_Collection_Db_FetchStrategyInterface $fetchStrategy
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_Config $coreConfig
     */
    public function __construct(
        Magento_Data_Collection_Db_FetchStrategyInterface $fetchStrategy,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_Config $coreConfig
    ) {
        $this->_coreConfig = $coreConfig;
        parent::__construct(
            $fetchStrategy,
            $coreStoreConfig
        );
    }

    /**
     * Retrieve currently edited product model
     *
     * @return Magento_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * @inheritdoc
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $allowProductTypes = array();
        $allowProductTypeNodes = $this->_coreConfig
            ->getNode(Magento_Catalog_Model_Config::XML_PATH_GROUPED_ALLOWED_PRODUCT_TYPES)->children();
        foreach ($allowProductTypeNodes as $type) {
            $allowProductTypes[] = $type->getName();
        }

        $this->setProduct($this->_getProduct())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('sku')
            ->addFilterByRequiredOptions()
            ->addAttributeToFilter('type_id', $allowProductTypes);

        return $this;
    }
}
