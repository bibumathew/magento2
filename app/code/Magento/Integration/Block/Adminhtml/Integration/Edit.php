<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Controller\Adminhtml\Integration;

class Edit extends \Magento\Adminhtml\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_registry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $coreData, $data);
    }

    /**
     * Initialize Integration edit page
     *
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_integration';
        $this->_blockGroup = 'Magento_Integration';
        parent::_construct();
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        $this->removeButton('save')->addButton('save', [
            'id' => 'save-split-button',
            'label' => __('Save'),
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'button_class' => 'PrimarySplitButton',
            'data_attribute'  => [
                'mage-init' => [
                    'button' => ['event' => 'save', 'target' => '#edit_form'],
                ],
            ],
            'options' => [
                'save_activate' => [
                    'id' => 'activate',
                    'label' => __('Save & Activate'),
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'saveAndActivate',
                                'target' => '#edit_form',
                            ],
                            'integration' => [
                                'gridUrl' => $this->getUrl('*/*/'),
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get header text for edit page.
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (isset($this->_registry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION)[Info::DATA_ID])) {
            return __(
                "Edit Integration '%1'",
                $this->escapeHtml(
                    $this->_registry->registry(Integration::REGISTRY_KEY_CURRENT_INTEGRATION)[Info::DATA_NAME]
                )
            );
        } else {
            return __('New Integration');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
    }
}
