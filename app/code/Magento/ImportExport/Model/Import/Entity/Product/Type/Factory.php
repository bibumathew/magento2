<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Import product type factory
 */
class Magento_ImportExport_Model_Import_Entity_Product_Type_Factory
{
    /**
     * Object Manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param string $className
     * @param array $arguments
     * @return Magento_ImportExport_Model_Import_Entity_Product_Type_Abstract
     * @throws InvalidArgumentException
     */
    public function create($className, array $arguments = array())
    {
        if (!$className) {
            throw new InvalidArgumentException('Incorrect class name');
        }

        return $this->_objectManager->create($className, $arguments);
    }
}
