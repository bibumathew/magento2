<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Catalog_Model_Product_Attribute_Group extends Mage_Eav_Model_Entity_Attribute_Group
{

    /**
     * Check if group contains system attributes
     *
     * @return bool
     */
    public function hasSystemAttributes()
    {
        $result = false;
        /** @var $attributesCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributesCollection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Attribute_Collection');
        $attributesCollection->setAttributeGroupFilter($this->getId());
        foreach ($attributesCollection as $attribute) {
            if (!$attribute->getIsUserDefined()) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Check if contains attributes used in the configurable products
     *
     * @return bool
     */
    public function hasConfigurableAttributes()
    {
        $result = false;
        /** @var $attributesCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributesCollection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Attribute_Collection');
        $attributesCollection->setAttributeGroupFilter($this->getId());
        foreach ($attributesCollection as $attribute) {
            if ($attribute->getIsConfigurable()) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}