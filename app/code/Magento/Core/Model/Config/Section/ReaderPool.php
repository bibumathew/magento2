<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
class Magento_Core_Model_Config_Section_ReaderPool
{
    /**
     * List of readers
     *
     * @var array
     */
    protected $_readers = array();

    /**
     * @param Magento_Core_Model_Config_Section_Reader_DefaultReader $default
     * @param Magento_Core_Model_Config_Section_Reader_Website $website
     * @param Magento_Core_Model_Config_Section_Reader_Store $store
     */
    public function __construct(
        Magento_Core_Model_Config_Section_Reader_DefaultReader $default,
        Magento_Core_Model_Config_Section_Reader_Website $website,
        Magento_Core_Model_Config_Section_Reader_Store $store
    ) {
        $this->_readers = array(
            'default' => $default,
            'website' => $website,
            'websites' => $website,
            'store' => $store,
            'stores' => $store
        );
    }

    /**
     * Retrieve reader by scope
     *
     * @param string $scope
     * @return mixed
     */
    public function getReader($scope)
    {
        return $this->_readers[$scope];
    }
} 
