<?php
namespace Magento\Di\Generator\TestAsset;

/**
 * Factory class for Magento\Di\Generator\TestAsset\SourceClassWithNamespace
 *
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
class SourceClassWithNamespaceFactory
{
    /**
     * Entity class name
     */
    const CLASS_NAME = 'Magento\Di\Generator\TestAsset\SourceClassWithNamespace';

    /**
     * Object Manager instance
     *
     * @var \Magento_ObjectManager
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento_ObjectManager $objectManager
     */
    public function __construct(\Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Di\Generator\TestAsset\SourceClassWithNamespace
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create(self::CLASS_NAME, $data);
    }
}
