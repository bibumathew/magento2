<?php
/**
 * Google AdWords Conversion Abstract Backend model
 *
 * {license_notice}
 *
 * @copyright {copyright}
 * @license {license_link}
 * @SuppressWarnings(PHPMD.LongVariable)
 */
namespace Magento\GoogleAdwords\Model\Config\Backend;

abstract class ConversionAbstract extends \Magento\Core\Model\Config\Value
{
    /**
     * @var \Magento\Validator\Composite\VarienObject
     */
    protected $_validatorComposite;

    /**
     * @var \Magento\GoogleAdwords\Model\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param Magento_Core_Model_StoreManager $storeManager
     * @param Magento_Core_Model_Config $config
     * @param \Magento\Validator\Composite\VarienObjectFactory $validatorCompositeFactory
     * @param \Magento\GoogleAdwords\Model\Validator\Factory $validatorFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        Magento_Core_Model_StoreManager $storeManager,
        Magento_Core_Model_Config $config,
        \Magento\Validator\Composite\VarienObjectFactory $validatorCompositeFactory,
        \Magento\GoogleAdwords\Model\Validator\Factory $validatorFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $config,
            $resource,
            $resourceCollection
        );

        $this->_validatorFactory = $validatorFactory;
        $this->_validatorComposite = $validatorCompositeFactory->create();
    }
}
