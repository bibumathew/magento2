<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog compare item resource model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Collection_AssociatedProduct
    extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Registry instance
     *
     * @var Mage_Core_Model_Registry
     */
    protected $_registryManager;

    /**
     * Product type configurable instance
     *
     * @var Mage_Catalog_Model_Product_Type_Configurable
     */
    protected $_productType;

    /**
     * Configuration helper instance
     *
     * @var Mage_Catalog_Helper_Product_Configuration
     */
    protected $_configurationHelper;

    /**
     * Collection constructor
     *
     * @param Mage_Core_Model_Registry $registryManager
     * @param Mage_Catalog_Model_Product_Type_Configurable $productType
     * @param Mage_Catalog_Helper_Product_Configuration $configurationHelper
     * @param null $resource
     */
    public function __construct(
        Mage_Core_Model_Registry $registryManager,
        Mage_Catalog_Model_Product_Type_Configurable $productType,
        Mage_Catalog_Helper_Product_Configuration $configurationHelper,
        $resource = null
    ) {
        $this->_registryManager = $registryManager;
        $this->_productType = $productType;
        $this->_configurationHelper = $configurationHelper;

        parent::__construct($resource);
    }

    /**
     * Get product type
     *
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    public function getProductType()
    {
        return $this->_productType;
    }

    /**
     * Retrieve currently edited product object
     *
     * @return mixed
     */
    private function getProduct()
    {
        return $this->_registryManager->registry('current_product');
    }

    /**
     * Prepare select for load
     *
     * @param Varien_Db_Select $select
     * @return string
     */
    public function _prepareSelect(Varien_Db_Select $select)
    {
        $allowedProductTypes = array();
        foreach ($this->_configurationHelper->getConfigurableAllowedTypes() as $type) {
            $allowedProductTypes[] = $type->getName();
        }

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('weight')
            ->addFieldToFilter('type_id', $allowedProductTypes)
            ->addFieldToFilter('entity_id', array('neq' => $this->getProduct()->getId()))
            ->addFilterByRequiredOptions()
            ->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner');

        return parent::_prepareSelect($select);
    }
}
