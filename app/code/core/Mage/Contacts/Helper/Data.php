<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Contacts
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Contacts base helper
 *
 * @category   Mage
 * @package    Mage_Contacts
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Contacts_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_ENABLED   = 'contacts/contacts/enabled';

    public function isEnabled()
    {
        return Mage::getStoreConfig( self::XML_PATH_ENABLED );
    }

    public function getUserName()
    {
        if (!Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            return '';
        }
        $customer = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();
        return trim($customer->getName());
    }

    public function getUserEmail()
    {
        if (!Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            return '';
        }
        $customer = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();
        return $customer->getEmail();
    }
}
