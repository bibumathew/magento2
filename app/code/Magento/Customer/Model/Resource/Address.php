<?php
/**
 * Customer address entity resource model
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Customer_Model_Resource_Address extends Magento_Eav_Model_Entity_Abstract
{
    /**
     * @var Magento_Core_Model_Validator_Factory
     */
    protected $_validatorFactory;

    /**
     * @var Magento_Customer_Model_CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param Magento_Core_Model_Resource $resource
     * @param Magento_Eav_Model_Config $eavConfig
     * @param Magento_Eav_Model_Entity_Attribute_Set $attrSetEntity
     * @param Magento_Core_Model_LocaleInterface $locale
     * @param Magento_Eav_Model_Resource_Helper $resourceHelper
     * @param Magento_Eav_Model_Factory_Helper $helperFactory
     * @param Magento_Core_Model_Validator_Factory $validatorFactory
     * @param Magento_Customer_Model_CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Magento_Core_Model_Resource $resource,
        Magento_Eav_Model_Config $eavConfig,
        Magento_Eav_Model_Entity_Attribute_Set $attrSetEntity,
        Magento_Core_Model_LocaleInterface $locale,
        Magento_Eav_Model_Resource_Helper $resourceHelper,
        Magento_Eav_Model_Factory_Helper $helperFactory,
        Magento_Core_Model_Validator_Factory $validatorFactory,
        Magento_Customer_Model_CustomerFactory $customerFactory,
        $data = array()
    ) {
        $this->_validatorFactory = $validatorFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct($resource, $eavConfig, $attrSetEntity, $locale, $resourceHelper, $helperFactory, $data);
    }

    /**
     * Resource initialization.
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType('customer_address')->setConnection(
            $resource->getConnection('customer_read'),
            $resource->getConnection('customer_write')
        );
    }

    /**
     * Set default shipping to address
     *
     * @param Magento_Object $address
     * @return Magento_Customer_Model_Resource_Address
     */
    protected function _afterSave(Magento_Object $address)
    {
        if ($address->getIsCustomerSaveTransaction()) {
            return $this;
        }
        if ($address->getId() && ($address->getIsDefaultBilling() || $address->getIsDefaultShipping())) {
            $customer = $this->_createCustomer()->load($address->getCustomerId());

            if ($address->getIsDefaultBilling()) {
                $customer->setDefaultBilling($address->getId());
            }
            if ($address->getIsDefaultShipping()) {
                $customer->setDefaultShipping($address->getId());
            }
            $customer->save();
        }
        return $this;
    }

    /**
     * Check customer address before saving
     *
     * @param Magento_Object $address
     * @return Magento_Customer_Model_Resource_Address
     */
    protected function _beforeSave(Magento_Object $address)
    {
        parent::_beforeSave($address);

        $this->_validate($address);

        return $this;
    }

    /**
     * Validate customer address entity
     *
     * @param Magento_Customer_Model_Customer $address
     * @throws Magento_Validator_Exception when validation failed
     */
    protected function _validate($address)
    {
        $validator = $this->_validatorFactory->createValidator('customer_address', 'save');

        if (!$validator->isValid($address)) {
            throw new Magento_Validator_Exception($validator->getMessages());
        }
    }

    /**
     * @return Magento_Customer_Model_Customer
     */
    protected function _createCustomer()
    {
        return $this->_customerFactory->create();
    }
}
