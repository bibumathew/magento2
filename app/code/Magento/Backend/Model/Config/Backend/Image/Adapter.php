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
 * System config image field backend model for Zend PDF generator
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Model\Config\Backend\Image;

class Adapter extends \Magento\Core\Model\Config\Value
{
    /**
     * @var \Magento\Core\Model\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param \Magento\Core\Model\Image\AdapterFactory $imageFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        Magento_Core_Model_Registry $registry,
        \Magento\Core\Model\Image\AdapterFactory $imageFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_imageFactory = $imageFactory;
    }

    /**
     * Checks if choosen image adapter available
     *
     * @throws \Magento\Core\Exception if some of adapter dipendencies was not loaded
     * @return \Magento\Backend\Model\Config\Backend\File
     */
    protected function _beforeSave()
    {
        try {
            $this->_imageFactory->create($this->getValue());
        } catch (\Exception $e) {
            $message = __('The specified image adapter cannot be used because of: ' . $e->getMessage());
            throw new \Magento\Core\Exception($message);
        }

        return $this;
    }
}
