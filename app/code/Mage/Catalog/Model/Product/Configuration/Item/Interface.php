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
 * Product configurational item interface
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
interface Mage_Catalog_Model_Product_Configuration_Item_Interface
{
    /**
     * Retrieve associated product
     *
     * @return Mage_Catalog_Model_Product
     */
    function getProduct();

    /**
     * Get item option by code
     *
     * @param   string $code
     * @return  Mage_Catalog_Model_Product_Configuration_Item_Option_Interface
     */
    public function getOptionByCode($code);

    /**
     * Returns special download params (if needed) for custom option with type = 'file''
     * Return null, if not special params needed'
     * Or return Varien_Object with any of the following indexes:
     *  - 'url' - url of controller to give the file
     *  - 'urlParams' - additional parameters for url (custom option id, or item id, for example)
     *
     * @return null|Varien_Object
     */
    public function getFileDownloadParams();
}