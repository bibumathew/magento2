<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_VersionsCms
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Cms Hierarchy Model for config processing
 *
 * @category   Magento
 * @package    Magento_VersionsCms
 */
namespace Magento\VersionsCms\Model\Hierarchy;

class Config
{
    const XML_PATH_CONTEXT_MENU_LAYOUTS = 'global/magento_versionscms/hierarchy/menu/layouts';

    /**
     * Menu layouts configuration
     * @var array
     */
    protected $_contextMenuLayouts = null;

    /**
     * Defalt code for menu layouts
     * @var string
     */
    protected $_defaultMenuLayoutCode;

    /**
     * @var Magento_Core_Model_Config
     */
    protected $_coreConfig;

    /**
     * Constructor
     *
     * @param Magento_Core_Model_Config $coreConfig
     */
    public function __construct(
        Magento_Core_Model_Config $coreConfig
    ) {
        $this->_coreConfig = $coreConfig;
    }

    /**
     * Initialization for $_contextMenuLayouts
     *
     * @return \Magento\VersionsCms\Model\Hierarchy\Config
     */
    protected function _initContextMenuLayouts()
    {
        $config = $this->_coreConfig->getNode(self::XML_PATH_CONTEXT_MENU_LAYOUTS);
        if ($this->_contextMenuLayouts !== null || !$config) {
            return $this;
        }
        if (!is_array($this->_contextMenuLayouts)) {
            $this->_contextMenuLayouts = array();
        }
        foreach ($config->children() as $layoutCode => $layoutConfig) {
            $this->_contextMenuLayouts[$layoutCode] = new \Magento\Object(array(
                'label'                 => __((string)$layoutConfig->label),
                'code'                  => $layoutCode,
                'layout_handle'         => (string)$layoutConfig->layout_handle,
                'is_default'            => (int)$layoutConfig->is_default,
                'page_layout_handles'   => (array)$layoutConfig->page_layout_handles,
            ));
            if ((bool)$layoutConfig->is_default) {
                $this->_defaultMenuLayoutCode = $layoutCode;
            }
        }
        return $this;
    }

    /**
     * Return available Context Menu layouts output
     *
     * @return array
     */
    public function getContextMenuLayouts()
    {
        $this->_initContextMenuLayouts();
        return $this->_contextMenuLayouts;
    }

    /**
     * Return Context Menu layout by its code
     *
     * @param string $layoutCode
     * @return \Magento\Object|boolean
     */
    public function getContextMenuLayout($layoutCode)
    {
        $this->_initContextMenuLayouts();
        return isset($this->_contextMenuLayouts[$layoutCode]) ? $this->_contextMenuLayouts[$layoutCode] : false;
    }

    /**
     * Getter for $_defaultMenuLayoutCode
     *
     * @return string
     */
    public function getDefaultMenuLayoutCode()
    {
        $this->_initContextMenuLayouts();
        return $this->_defaultMenuLayoutCode;
    }
}
