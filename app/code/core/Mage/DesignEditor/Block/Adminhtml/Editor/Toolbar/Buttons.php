<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * VDE buttons block
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons
    extends Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract
{
    /**
     * Current theme used for preview
     *
     * @var int
     */
    protected $_themeId;

    /**
     * Get current theme id
     *
     * @return int
     */
    public function getThemeId()
    {
        return $this->_themeId;
    }

    /**
     * Get current theme id
     *
     * @param int $themeId
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons
     */
    public function setThemeId($themeId)
    {
        $this->_themeId = $themeId;

        return $this;
    }

    /**
     * Get "View Layout" button URL
     *
     * @return string
     */
    public function getViewLayoutUrl()
    {
        return $this->getUrl('*/*/getLayoutUpdate');
    }

    /**
     * Get "Back" button URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    /**
     * Get "Navigation Mode" button URL
     *
     * @return string
     */
    public function getNavigationModeUrl()
    {
        return $this->getUrl('*/*/launch', array(
            'mode' => Mage_DesignEditor_Model_State::MODE_NAVIGATION,
            'theme_id' => $this->getThemeId()
        ));
    }

    /**
     * Get "Design Mode" button URL
     *
     * @return string
     */
    public function getDesignModeUrl()
    {
        return $this->getUrl('*/*/launch', array(
            'mode' => Mage_DesignEditor_Model_State::MODE_DESIGN,
            'theme_id' => $this->getThemeId()
        ));
    }

    /**
     * Get assign to storeview button
     *
     * @return string
     */
    public function getAssignButtonHtml()
    {
        /** @var $assignButton Mage_Backend_Block_Widget_Button */
        $assignButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');
        $assignButton->setData(array(
            'label'     => $this->__('Assign this Theme'),
            'data_attr' => array(
                'widget-button' => array(
                    'event'     => 'assign',
                    'related'   => 'body',
                    'eventData' => array(
                        'theme_id' => $this->getThemeId()
                    )
                ),
            ),
            'class'     => 'save action-theme-assign',
            'target'    => '_blank'
        ));

        return $assignButton->toHtml();
    }

    /**
     * Get switch mode button
     *
     * @return string
     */
    public function getSwitchModeButtonHtml()
    {
        $eventData = array(
            'theme_id' => $this->getThemeId(),
        );

        if ($this->isNavigationMode()) {
            $label                 = $this->__('Design Mode');
            $eventData['mode_url'] = $this->getDesignModeUrl();
        } else {
            $label                         = $this->__('Navigation Mode');
            $eventData['mode_url']         = $this->getNavigationModeUrl();
            $eventData['save_changes_url'] = $this->getSaveTemporaryLayoutUpdateUrl();
        }

        /** @var $switchButton Mage_Backend_Block_Widget_Button */
        $switchButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');
        $switchButton->setData(array(
            'label'     => $label,
            'data_attr' => array(
                'widget-button' => array(
                    'event'     => 'switchMode',
                    'related'   => 'body',
                    'eventData' => $eventData
                ),
            ),
            'class'     => 'action-switch-mode',
        ));

        return $switchButton->toHtml();
    }

    /**
     * Get save temporary layout changes url
     *
     * @return string
     */
    public function getSaveTemporaryLayoutUpdateUrl()
    {
        return $this->getUrl('*/*/saveTemporaryLayoutUpdate');
    }
}
