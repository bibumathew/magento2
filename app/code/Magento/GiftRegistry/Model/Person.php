<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GiftRegistry
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Entity registrants data model
 *
 * @method \Magento\GiftRegistry\Model\Resource\Person _getResource()
 * @method \Magento\GiftRegistry\Model\Resource\Person getResource()
 * @method \Magento\GiftRegistry\Model\Person setEntityId(int $value)
 * @method string getFirstname()
 * @method \Magento\GiftRegistry\Model\Person setFirstname(string $value)
 * @method string getLastname()
 * @method \Magento\GiftRegistry\Model\Person setLastname(string $value)
 * @method string getEmail()
 * @method \Magento\GiftRegistry\Model\Person setEmail(string $value)
 * @method string getRole()
 * @method \Magento\GiftRegistry\Model\Person setRole(string $value)
 * @method string getCustomValues()
 * @method \Magento\GiftRegistry\Model\Person setCustomValues(string $value)
 *
 * @category    Magento
 * @package     Magento_GiftRegistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GiftRegistry\Model;

class Person extends \Magento\Core\Model\AbstractModel
{
    /**
     * Gift registry data
     *
     * @var Magento_GiftRegistry_Helper_Data
     */
    protected $_giftRegistryData = null;

    /**
     * @param Magento_GiftRegistry_Helper_Data $giftRegistryData
     * @param Magento_Core_Model_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_GiftRegistry_Model_Resource_Person $resource
     * @param Magento_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Magento_GiftRegistry_Helper_Data $giftRegistryData,
        Magento_Core_Model_Context $context,
        Magento_Core_Model_Registry $registry,
        Magento_GiftRegistry_Model_Resource_Person $resource,
        Magento_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_giftRegistryData = $giftRegistryData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Magento\GiftRegistry\Model\Resource\Person');
    }

    /**
     * Validate registrant attribute values
     *
     * @return array|bool
     */
    public function validate()
    {
        // not Checking entityId !!!
        $errors = array();

        if (!\Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = __('Please enter the first name.');
        }

        if (!\Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = __('Please enter the last name.');
        }

        if (!\Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = __('Please enter a valid email address(for example, daniel@x.com).');
        }

        $customValues = $this->getCustom();
        $attributes = \Mage::getSingleton('Magento\GiftRegistry\Model\Entity')->getRegistrantAttributes();

        $errorsCustom = $this->_giftRegistryData->validateCustomAttributes($customValues, $attributes);
        if ($errorsCustom !== true) {
            $errors = empty($errors) ? $errorsCustom : array_merge($errors, $errorsCustom);
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Unpack "custom" value array
     *
     * @return $this
     */
    public function unserialiseCustom()
    {
        if (is_string($this->getCustomValues())) {
            $this->setCustom(unserialize($this->getCustomValues()));
        }
        return $this;
    }
}
