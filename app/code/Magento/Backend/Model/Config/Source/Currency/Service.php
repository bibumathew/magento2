<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */


namespace Magento\Backend\Model\Config\Source\Currency;

class Service implements \Magento\Core\Model\Option\ArrayInterface
{
    protected $_options;

    /**
     * @var Magento_Core_Model_Config
     */
    protected $_coreConfig;

    /**
     * Constructor
     *
     * @param Magento_Core_Model_Config $coreConfig
     */
    public function __construct(
        Magento_Core_Model_Config $coreConfig
    ) {
        $this->_coreConfig = $coreConfig;
    }

    public function toOptionArray()
    {
        if (!$this->_options) {
            $services = $this->_coreConfig->getNode('global/currency/import/services')->asArray();
            $this->_options = array();
            foreach ($services as $_code => $_options ) {
                $this->_options[] = array(
                    'label' => $_options['name'],
                    'value' => $_code,
                );
            }
        }

        $options = $this->_options;
        return $options;
    }

}
