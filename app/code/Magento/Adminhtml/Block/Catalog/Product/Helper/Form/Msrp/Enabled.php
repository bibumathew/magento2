<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Product form MAP Enabled field helper
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Catalog_Product_Helper_Form_Msrp_Enabled extends Magento_Data_Form_Element_Select
{
    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        if (is_null($this->getValue())) {
            $this->setValue(Magento_Catalog_Model_Product_Attribute_Source_Msrp_Type_Enabled::MSRP_ENABLE_USE_CONFIG);
        }
        return parent::getElementHtml();
    }
}

