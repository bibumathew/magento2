<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Theme
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Theme editor container
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

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
        $this->_coreRegistry = $registry;
        parent::__construct($context, $coreData, $data);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\View\Block\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_blockGroup = 'Magento_Theme';
        $this->_controller = 'Adminhtml_System_Design_Theme';
        $this->setId('theme_edit');

        if (is_object($this->getLayout()->getBlock('page-title'))) {
            $this->getLayout()->getBlock('page-title')->setPageTitle($this->getHeaderText());
        }

        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $this->_getCurrentTheme();
        if ($theme) {
            if ($theme->isEditable()) {
                $this->_addButton('save_and_continue', array(
                    'label'     => __('Save and Continue Edit'),
                    'class'     => 'save',
                    'data_attribute' => array(
                        'mage-init' => array(
                            'button' => array(
                                'event'  => 'saveAndContinueEdit',
                                'target' => '#edit_form'
                            ),
                        ),
                    ),
                ), 1);
            } else {
                $this->_removeButton('save');
                $this->_removeButton('reset');
            }

            if ($theme->isDeletable()) {
                if ($theme->hasChildThemes()) {
                    $message = __('Are you sure you want to delete this theme?');
                    $onClick = sprintf("deleteConfirm('%s', '%s')",
                        $message,
                        $this->getUrl('adminhtml/*/delete', array('id' => $theme->getId()))
                    );
                    $this->_updateButton('delete', 'onclick', $onClick);
                }
            } else {
                $this->_removeButton('delete');
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare header for container
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $this->_getCurrentTheme();
        if ($theme->getId()) {
            $header = __('Theme: %1', $theme->getThemeTitle());
        } else {
            $header = __('New Theme');
        }
        return $header;
    }

    /**
     * Get current theme
     *
     * @return \Magento\Core\Model\Theme
     */
    protected function _getCurrentTheme()
    {
        return $this->_coreRegistry->registry('current_theme');
    }
}
