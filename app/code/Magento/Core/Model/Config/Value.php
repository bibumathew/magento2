<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Config data model
 *
 * @method \Magento\Core\Model\Resource\Config\Data _getResource()
 * @method \Magento\Core\Model\Resource\Config\Data getResource()
 * @method string getScope()
 * @method \Magento\Core\Model\Config\Value setScope(string $value)
 * @method int getScopeId()
 * @method \Magento\Core\Model\Config\Value setScopeId(int $value)
 * @method string getPath()
 * @method \Magento\Core\Model\Config\Value setPath(string $value)
 * @method string getValue()
 * @method \Magento\Core\Model\Config\Value setValue(string $value)
 *
 * @category    Mage
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
namespace Magento\Core\Model\Config;

class Value extends \Magento\Core\Model\AbstractModel
{
    const ENTITY = 'core_config_data';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_config_data';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'config_data';

    /**
     * Magento model constructor
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Resource\Config\Data');
    }

    /**
     * Add availability call after load as public
     */
    public function afterLoad()
    {
        $this->_afterLoad();
    }

    /**
     * Check if config data value was changed
     *
     * @return bool
     */
    public function isValueChanged()
    {
        return $this->getValue() != $this->getOldValue();
    }

    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function getOldValue()
    {
        $storeCode   = $this->getStoreCode();
        $websiteCode = $this->getWebsiteCode();
        $path        = $this->getPath();

        if ($storeCode) {
            return \Mage::app()->getStore($storeCode)->getConfig($path);
        }
        if ($websiteCode) {
            return \Mage::app()->getWebsite($websiteCode)->getConfig($path);
        }
        return (string) \Mage::getConfig()->getValue($path, 'default');
    }


    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @return string
     */
    public function getFieldsetDataValue($key)
    {
        $data = $this->_getData('fieldset_data');
        return (is_array($data) && isset($data[$key])) ? $data[$key] : null;
    }
}
