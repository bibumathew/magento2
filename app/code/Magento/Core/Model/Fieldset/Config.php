<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Core\Model\Fieldset;

class Config
{
    /**
     * @var \Magento\Core\Model\Fieldset\Config\Data
     */
    protected $_dataStorage;

    /**
     * @param \Magento\Core\Model\Fieldset\Config\Data $dataStorage
     */
    public function __construct(\Magento\Core\Model\Fieldset\Config\Data $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Get fieldsets by $path
     *
     * @param string $path
     * @return array
     */
    public function getFieldsets($path)
    {
        return $this->_dataStorage->get($path);
    }

    /**
     * Get the fieldset for an area
     *
     * @param string $name fieldset name
     * @param string $root fieldset area, could be 'admin'
     * @return null|array
     */
    public function getFieldset($name, $root = 'global')
    {
        $fieldsets = $this->getFieldsets($root);
        if (empty($fieldsets)) {
            return null;
        }
        return isset($fieldsets[$name]) ? $fieldsets[$name] : null;
    }
}
