<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Abstract placeholder container
 */
abstract class Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * @var null|Enterprise_PageCache_Model_Processor
     */
    protected $_processor;

    /**
     * Placeholder instance
     *
     * @var Enterprise_PageCache_Model_Container_Placeholder
     */
    protected $_placeholder;

    /**
     * Class constructor
     *
     * @param Enterprise_PageCache_Model_Container_Placeholder $placeholder
     */
    public function __construct($placeholder)
    {
        $this->_placeholder = $placeholder;
    }

    /**
     * Get container individual cache id
     *
     * @return string|false
     */
    protected function _getCacheId()
    {
        return false;
    }

    /**
     * Generate placeholder content before application was initialized and apply to page content if possible
     *
     * @param string $content
     * @return bool
     */
    public function applyWithoutApp(&$content)
    {
        $cacheId = $this->_getCacheId();

        if ($cacheId === false) {
            $this->_applyToContent($content, '');
            return true;
        }

        $block = $this->_loadCache($cacheId);
        if ($block === false) {
            return false;
        }

        $block = Enterprise_PageCache_Helper_Url::replaceUenc($block);
        $this->_applyToContent($content, $block);
        return true;
    }

    /**
     * Generate and apply container content in controller after application is initialized
     *
     * @param string $content
     * @return bool
     */
    public function applyInApp(&$content)
    {
        $blockContent = $this->_renderBlock();
        if ($blockContent === false) {
            return false;
        }

        if (Mage::getStoreConfig(Enterprise_PageCache_Model_Processor::XML_PATH_CACHE_DEBUG)) {
            $debugBlock = Mage::app()->getLayout()->createBlock('Enterprise_PageCache_Block_Debug');
            $debugBlock->setDynamicBlockContent($blockContent);
            $this->_applyToContent($content, $debugBlock->toHtml());
        } else {
            $this->_applyToContent($content, $blockContent);
        }

        $subprocessor = $this->_processor->getSubprocessor();
        if ($subprocessor) {
            $contentWithOutNestedBlocks = $subprocessor->replaceContentToPlaceholderReplacer($blockContent);
            $this->saveCache($contentWithOutNestedBlocks);
        }

        return true;
    }

    /**
     * Save rendered block content to cache storage
     *
     * @param string $blockContent
     * @return Enterprise_PageCache_Model_Container_Abstract
     */
    public function saveCache($blockContent)
    {
        $cacheId = $this->_getCacheId();
        if ($cacheId !== false) {
            $this->_saveCache($blockContent, $cacheId);
        }
        return $this;
    }

    /**
     * Render block content from placeholder
     *
     * @return string|false
     */
    protected function _renderBlock()
    {
        return false;
    }

    /**
     * Replace container placeholder in content on container content
     *
     * @param string $content
     * @param string $containerContent
     */
    protected function _applyToContent(&$content, $containerContent)
    {
        $containerContent = $this->_placeholder->getStartTag() . $containerContent . $this->_placeholder->getEndTag();
        $content = str_replace($this->_placeholder->getReplacer(), $containerContent, $content);
    }

    /**
     * Load cached data by cache id
     *
     * @param string $id
     * @return string|false
     */
    protected function _loadCache($id)
    {
        return Enterprise_PageCache_Model_Cache::getCacheInstance()->load($id);
    }

    /**
     * Save data to cache storage
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param null|int $lifetime
     * @return Enterprise_PageCache_Model_Container_Abstract
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        $tags[] = Enterprise_PageCache_Model_Processor::CACHE_TAG;
        if (is_null($lifetime)) {
            $lifetime = $this->_placeholder->getAttribute('cache_lifetime') ?
                $this->_placeholder->getAttribute('cache_lifetime') : false;
        }

        /**
         * Replace all occurrences of session_id with unique marker
         */
        Enterprise_PageCache_Helper_Url::replaceSid($data);

        Enterprise_PageCache_Model_Cache::getCacheInstance()->save($data, $id, $tags, $lifetime);
        return $this;
    }

    /**
     * Retrieve cookie value
     *
     * @param string $cookieName
     * @param mixed $defaultValue
     * @return string
     */
    protected static function _getCookieValue($cookieName, $defaultValue = null)
    {
        return (array_key_exists($cookieName, $_COOKIE) ? $_COOKIE[$cookieName] : $defaultValue);
    }

    /**
     * Set processor for container needs
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return Enterprise_PageCache_Model_Container_Abstract
     */
    public function setProcessor(Enterprise_PageCache_Model_Processor $processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    /**
     * Get last visited category id
     *
     * @return string|null
     */
    protected function _getCategoryId()
    {
        if ($this->_processor) {
            $categoryId = $this->_processor
                ->getMetadata(Enterprise_PageCache_Model_Processor_Category::METADATA_CATEGORY_ID);
            if ($categoryId) {
                return $categoryId;
            }
        }

        //If it is not product page and not category page - we have no any category (not using last visited)
        if (!$this->_getProductId()) {
            return null;
        }

        return self::_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CATEGORY_ID, null);
    }

    /**
     * Get current product id
     *
     * @return string|null
     */
    protected function _getProductId()
    {
        if (!$this->_processor) {
            return null;
        }

        return $this->_processor
            ->getMetadata(Enterprise_PageCache_Model_Processor_Product::METADATA_PRODUCT_ID);
    }

    /**
     * Get current request id
     *
     * @return string|null
     */
    protected function _getRequestId()
    {
        if (!$this->_processor) {
            return null;
        }

        return $this->_processor->getRequestId();
    }

    /**
     * Get Place Holder Block
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _getPlaceHolderBlock()
    {
        $blockName = $this->_placeholder->getAttribute('block');
        $block = Mage::app()->getLayout()->createBlock($blockName);
        $block->setTemplate($this->_placeholder->getAttribute('template'));
        $block->setLayout(Mage::app()->getLayout());
        $block->setSkipRenderTag(true);
        return $block;
    }
}