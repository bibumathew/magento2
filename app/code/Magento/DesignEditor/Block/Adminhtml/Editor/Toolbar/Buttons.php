<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * VDE buttons block
 *
 * @method Magento_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons setVirtualThemeId(int $id)
 * @method int getVirtualThemeId()
 */
class Magento_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons
    extends Magento_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract
{
    /**
     * Current theme used for preview
     *
     * @var int
     */
    protected $_themeId;

    /**
     * Backend data
     *
     * @var Magento_Backend_Helper_Data
     */
    protected $_backendData = null;

    /**
     * @param Magento_Backend_Helper_Data $backendData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Magento_Backend_Helper_Data $backendData,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_backendData = $backendData;
        parent::__construct($coreData, $context, $data);
    }

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
     * @return Magento_DesignEditor_Block_Adminhtml_Editor_Toolbar_Buttons
     */
    public function setThemeId($themeId)
    {
        $this->_themeId = $themeId;

        return $this;
    }

    /**
     * Get admin panel home page URL
     *
     * @return string
     */
    public function getHomeLink()
    {
        return $this->_backendData->getHomePageUrl();
    }
}
