<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Filesystem\Directory;

class WriteFactory
{
    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a readable directory
     *
     * @param array $config
     * @return \Magento\Filesystem\File\ReadInterface
     */
    public function create(array $config)
    {
        $directoryDriver = isset($config['driver']) ? $config['driver'] : '\Magento\Filesystem\Driver\Base';
        $driver = $this->objectManager->get($directoryDriver);

        return $this->objectManager->create('Magento\Filesystem\Directory\Write',
            array(
                'config' => $config,
                'driver' => $driver
            ));
    }
}
