<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Customer
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Customer Order Address resource model
 *
 * @category    Enterprise
 * @package     Enterprise_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Customer_Model_Resource_Sales_Order_Address
    extends Enterprise_Customer_Model_Resource_Sales_Address_Abstract
{
    /**
     * Main entity resource model name
     *
     * @var string
     */
    protected $_parentResourceModelName = 'Mage_Sales_Model_Resource_Order_Address';

    /**
     * Initializes resource
     */
    protected function _construct()
    {
        $this->_init('enterprise_customer_sales_flat_order_address', 'entity_id');
    }
}