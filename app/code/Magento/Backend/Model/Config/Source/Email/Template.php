<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Backend\Model\Config\Source\Email;

class Template extends \Magento\Object
    implements \Magento\Core\Model\Option\ArrayInterface
{
    /**
     * @var \Magento\Core\Model\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Magento\Core\Model\Email\Template\Config
     */
    private $_emailConfig;

    /**
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Core\Model\Email\Template\Config $emailConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Core\Model\Email\Template\Config $emailConfig,
        array $data = array()
    ) {
        parent::__construct($data);
        $this->_coreRegistry = $coreRegistry;
        $this->_emailConfig = $emailConfig;
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \Magento\Core\Model\Resource\Email\Template\Collection $collection */
        if (!$collection = $this->_coreRegistry->registry('config_system_email_template')) {
            $collection = \Mage::getResourceModel('Magento\Core\Model\Resource\Email\Template\Collection')
                ->load();
            $this->_coreRegistry->register('config_system_email_template', $collection);
        }
        $options = $collection->toOptionArray();
        $templateId = str_replace('/', '_', $this->getPath());
        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, array(
            'value' => $templateId,
            'label' => $templateLabel
        ));
        return $options;
    }
}
