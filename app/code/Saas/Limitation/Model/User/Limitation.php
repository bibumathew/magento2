<?php
/**
 * Limitation of number of users in the system
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Saas_Limitation_Model_User_Limitation implements Saas_Limitation_Model_Limitation_LimitationInterface
{
    /**
     * @var Saas_Limitation_Model_Limitation_Config
     */
    private $_config;

    /**
     * @var Magento_User_Model_Resource_User
     */
    private $_resource;

    /**
     * @param Saas_Limitation_Model_Limitation_Config $config
     * @param Magento_User_Model_Resource_User $resource
     */
    public function __construct(
        Saas_Limitation_Model_Limitation_Config $config,
        Magento_User_Model_Resource_User $resource
    ) {
        $this->_config = $config;
        $this->_resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getThreshold()
    {
        return $this->_config->getThreshold('admin_account');
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->_resource->countAll();
    }
}
