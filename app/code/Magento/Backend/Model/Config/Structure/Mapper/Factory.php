<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * System Configuration Mapper Factory
 */
namespace Magento\Backend\Model\Config\Structure\Mapper;

class Factory
{
    const MAPPER_SORTING                = 'sorting';
    const MAPPER_PATH                   = 'path';
    const MAPPER_IGNORE                 = 'ignore';
    const MAPPER_DEPENDENCIES           = 'dependencies';
    const MAPPER_ATTRIBUTE_INHERITANCE  = 'attribute_inheritance';
    const MAPPER_EXTENDS                = 'extends';

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_typeMap = array(
        self::MAPPER_SORTING => 'Magento\Backend\Model\Config\Structure\Mapper\Sorting',
        self::MAPPER_PATH => 'Magento\Backend\Model\Config\Structure\Mapper\Path',
        self::MAPPER_IGNORE => 'Magento\Backend\Model\Config\Structure\Mapper\Ignore',
        self::MAPPER_DEPENDENCIES => 'Magento\Backend\Model\Config\Structure\Mapper\Dependencies',
        self::MAPPER_ATTRIBUTE_INHERITANCE => 'Magento\Backend\Model\Config\Structure\Mapper\Attribute\Inheritance',
        self::MAPPER_EXTENDS => 'Magento\Backend\Model\Config\Structure\Mapper\ExtendsMapper',
    );

    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get mapper instance
     *
     * @param string $type
     * @param array $arguments
     * @return \Magento\Backend\Model\Config\Structure\MapperInterface
     * @throws \Exception
     */
    public function create($type)
    {
        $className = $this->_getMapperClassNameByType($type);

        /** @var \Magento\Backend\Model\Config\Structure\MapperInterface $mapperInstance  */
        $mapperInstance =  $this->_objectManager->create($className);

        if (false == ($mapperInstance instanceof \Magento\Backend\Model\Config\Structure\MapperInterface)) {
            throw new \Exception(
                'Mapper object is not instance on \Magento\Backend\Model\Config\Structure\MapperInterface'
            );
        }
        return $mapperInstance;
    }

    /**
     * Get mapper class name by type
     *
     * @param string $type
     * @return string mixed
     * @throws \InvalidArgumentException
     */
    protected function _getMapperClassNameByType($type)
    {
        if (false == isset($this->_typeMap[$type])) {
            throw new \InvalidArgumentException('Invalid mapper type: ' . $type);
        }
        return $this->_typeMap[$type];
    }
}
