<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Dummy layout argument data source object
 */
class Magento_Core_Model_DataSource extends Magento_Data_Collection
{
    /**
     * Property which stores all updater calls
     *
     * @var array
     */
    protected $_calls = array();

    /**
     * Return current updater calls
     *
     * @return array
     */
    public function getUpdaterCall()
    {
        return $this->_calls;
    }

    /**
     * Set updater calls
     *
     * @param array $calls
     * @return Magento_Core_Model_DataSource
     */
    public function setUpdaterCall(array $calls)
    {
        $this->_calls = $calls;
        return $this;
    }
}
