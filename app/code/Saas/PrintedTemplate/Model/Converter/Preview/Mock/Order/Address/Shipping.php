<?php
/**
 * {license_notice}
 *
 * @category    Saas
 * @package     Saas_PrintedTemplate
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Mock object for order shipping address model
 *
 * @category   Saas
 * @package    Saas_PrintedTemplate
 * @subpackage Models
 */
class Saas_PrintedTemplate_Model_Converter_Preview_Mock_Order_Address_Shipping extends Mage_Sales_Model_Order_Address
{
    /**
     * Initialize order shipping address with mock data
     */
    protected function _construct()
    {
        $this->setData($this->_getMockData());
    }

    /**
     * Returns data helper
     *
     * @return Saas_PrintedTemplate_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('Saas_PrintedTemplate_Helper_Data');
    }

    /**
     * Returns data for the order shipping address
     *
     * @return array
     */
    protected function _getMockData()
    {
        return array (
            'parent_id' => '-1',
            'customer_address_id' => '4',
            'quote_address_id' => NULL,
            'region_id' => '57',
            'customer_id' => '4',
            'fax' => '',
            'region' => $this->_getHelper()->__('Texas'),
            'postcode' => $this->_getHelper()->__('33065'),
            'lastname' => $this->_getHelper()->__('Hill'),
            'street' => $this->_getHelper()->__('22 Sycamore Fork Road'),
            'city' => $this->_getHelper()->__('Coral Springs, FL'),
            'email' => $this->_getHelper()->__('RamonaKHill@teleworm.com'),
            'telephone' => $this->_getHelper()->__('+19542274713'),
            'country_id' => $this->_getHelper()->__('US'),
            'firstname' => $this->_getHelper()->__('Ramona'),
            'address_type' => 'shipping',
            'prefix' => $this->_getHelper()->__('Mrs.'),
            'middlename' => $this->_getHelper()->__('J.'),
            'suffix' => $this->_getHelper()->__('K.'),
            'company' => $this->_getHelper()->__('Magento'),
            'address_id' => NULL,
            'tax_id' => NULL,
            'gift_message_id' => NULL,
        );
    }
}