<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Contacts
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Contacts base helper
 *
 * @category   Magento
 * @package    Magento_Contacts
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Contacts\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
{

    const XML_PATH_ENABLED   = 'contacts/contacts/enabled';

    /**
     * Core store config
     *
     * @var Magento_Core_Model_Store_Config
     */
    protected $_coreStoreConfig;

    /**
     * @param Magento_Core_Helper_Context $context
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     */
    public function __construct(
        Magento_Core_Helper_Context $context,
        Magento_Core_Model_Store_Config $coreStoreConfig
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        parent::__construct($context);
    }

    public function isEnabled()
    {
        return $this->_coreStoreConfig->getConfig( self::XML_PATH_ENABLED );
    }

    public function getUserName()
    {
        if (!\Mage::getSingleton('Magento\Customer\Model\Session')->isLoggedIn()) {
            return '';
        }
        $customer = \Mage::getSingleton('Magento\Customer\Model\Session')->getCustomer();
        return trim($customer->getName());
    }

    public function getUserEmail()
    {
        if (!\Mage::getSingleton('Magento\Customer\Model\Session')->isLoggedIn()) {
            return '';
        }
        $customer = \Mage::getSingleton('Magento\Customer\Model\Session')->getCustomer();
        return $customer->getEmail();
    }
}
