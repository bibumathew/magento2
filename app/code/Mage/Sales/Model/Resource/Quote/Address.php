<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Quote address resource model
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Quote_Address extends Mage_Sales_Model_Resource_Abstract
{
    /**
     * Main table and field initialization
     *
     */
    protected function _construct()
    {
        $this->_init('sales_flat_quote_address', 'address_id');
    }
}
