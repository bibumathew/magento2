<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\App\Updater;
use \Magento\ObjectManager;

class SetupFactory
{
    const INSTANCE_TYPE = 'Magento\App\Updater\SetupInterface';

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_resourceTypes;

    /**
     * @param ObjectManager $objectManager
     * @param array $resourceTypes
     */
    public function __construct(ObjectManager $objectManager, array $resourceTypes)
    {
        $this->_objectManager = $objectManager;
        $this->_resourceTypes = $resourceTypes;
    }

    /**
     * @param string $resourceName
     * @param string $moduleName
     * @return SetupInterface
     * @throws \LogicException
     */
    public function create($resourceName, $moduleName)
    {
        $className = isset($this->_resourceTypes[$resourceName])
            ? $this->_resourceTypes[$resourceName]
            : 'Magento\App\Updater\SetupInterface';

        if (false == (is_subclass_of($className, self::INSTANCE_TYPE)) && $className !== self::INSTANCE_TYPE) {
            throw new \LogicException($className . ' is not a \Magento\App\Updater\SetupInterface');
        }

        return $this->_objectManager->create($className, array(
            'resourceName' => $resourceName,
            'moduleName' => $moduleName,
        ));
    }
}