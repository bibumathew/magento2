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
 * Cms Hierarchy Node Widget Block
 *
 * @category   Magento
 * @package    Magento_VersionsCms
 */
namespace Magento\VersionsCms\Block\Widget;

class Node
    extends \Magento\Core\Block\Html\Link
    implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Current Hierarchy Node Page Instance
     *
     * @var \Magento\VersionsCms\Model\Hierarchy\Node
     */
    protected $_node;

    /**
     * Current Store Id
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Retrieve specified anchor text
     *
     * @return string
     */
    public function getAnchorText()
    {
        $value = $this->_getInstanceData('anchor_text');

        return ($value !== false ? $value : $this->_node->getLabel());
    }

    /**
     * Retrieve link specified title
     *
     * @return string
     */
    public function getTitle()
    {
        $value = $this->_getInstanceData('title');

        return ($value !== false ? $value : $this->_node->getLabel());
    }

    /**
     * Retrieve Node ID
     *
     * @return mixed|null
     */
    public function getNodeId()
    {
        return $this->_getInstanceData('node_id');
    }

    /**
     * Retrieve Node URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->_node->getUrl();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getNodeId()) {
            $this->_node = \Mage::getModel('Magento\VersionsCms\Model\Hierarchy\Node')
                ->load($this->getNodeId());
        } else {
            $this->_node = $this->_coreRegistry->registry('current_cms_hierarchy_node');
        }

        if (!$this->_node) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    protected function _getStoreId()
    {
        if (null === $this->_storeId) {
            $this->_storeId = \Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Retrieve data from instance
     *
     * @param string $key
     * @return bool|mixed
     */
    protected function _getInstanceData($key)
    {
        $dataKeys = array(
            $key . '_' . $this->_getStoreId(),
            $key . '_' . \Magento\Catalog\Model\AbstractModel::DEFAULT_STORE_ID,
            $key,
        );
        foreach($dataKeys as $value) {
            if ($this->getData($value) !== null) {
               return $this->getData($value);
            }
        }
        return false;
    }
}
