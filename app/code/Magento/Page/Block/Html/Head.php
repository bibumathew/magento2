<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Page
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Html page block
 *
 * @category   Magento
 * @package    Magento_Page
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_Page_Block_Html_Head extends Magento_Core_Block_Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'html/head.phtml';

    /**
     * Chunks of title (necessary for backend)
     *
     * @var array
     */
    protected $_titleChunks;

    /**
     * Page title without prefix and suffix when not chunked
     *
     * @var string
     */
    protected $_pureTitle;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Magento_Core_Model_Page_Asset_MergeService
     */
    private $_assetMergeService;

    /**
     * @var Magento_Core_Model_Page_Asset_MinifyService
     */
    private $_assetMinifyService;

    /**
     * @var Magento_Page_Model_Asset_GroupedCollection
     */
    private $_pageAssets;

    /**
     * Core file storage database
     *
     * @var Magento_Core_Helper_File_Storage_Database
     */
    protected $_fileStorageDatabase = null;

    /**
     * @param Magento_Core_Helper_File_Storage_Database $fileStorageDatabase
     * @param Magento_Core_Helper_Data $coreData
     * @param \Magento_Core_Block_Template_Context $context
     * @param \Magento_ObjectManager $objectManager
     * @param \Magento_Core_Model_Page $page
     * @param \Magento_Core_Model_Page_Asset_MergeService $assetMergeService
     * @param \Magento_Core_Model_Page_Asset_MinifyService $assetMinifyService
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_File_Storage_Database $fileStorageDatabase,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Magento_ObjectManager $objectManager,
        Magento_Core_Model_Page $page,
        Magento_Core_Model_Page_Asset_MergeService $assetMergeService,
        Magento_Core_Model_Page_Asset_MinifyService $assetMinifyService,
        array $data = array()
    ) {
        $this->_fileStorageDatabase = $fileStorageDatabase;
        parent::__construct($coreData, $context, $data);
        $this->_objectManager = $objectManager;
        $this->_assetMergeService = $assetMergeService;
        $this->_assetMinifyService = $assetMinifyService;
        $this->_pageAssets = $page->getAssets();
    }

    /**
     * Add RSS element to HEAD entity
     *
     * @param string $title
     * @param string $href
     * @return Magento_Page_Block_Html_Head
     */
    public function addRss($title, $href)
    {
        $attributes = 'rel="alternate" type="application/rss+xml" title="' . $title . '"';
        $asset = $this->_objectManager->create(
            'Magento_Core_Model_Page_Asset_Remote', array('url' => (string)$href)
        );
        $this->_pageAssets->add("link/$href", $asset, array('attributes' => $attributes));
        return $this;
    }

    /**
     * Render HTML for the added head items
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $block) {
            /** @var $block Magento_Core_Block_Abstract */
            if ($block instanceof Magento_Page_Block_Html_Head_AssetBlock) {
                /** @var Magento_Core_Model_Page_Asset_AssetInterface $asset */
                $asset = $block->getAsset();
                $this->_pageAssets->add(
                    $block->getNameInLayout(),
                    $asset,
                    (array)$block->getProperties()
                );
            }
        }

        $result = '';
        /** @var $group Magento_Page_Model_Asset_PropertyGroup */
        foreach ($this->_pageAssets->getGroups() as $group) {
            $contentType = $group->getProperty(Magento_Page_Model_Asset_GroupedCollection::PROPERTY_CONTENT_TYPE);
            $canMerge = $group->getProperty(Magento_Page_Model_Asset_GroupedCollection::PROPERTY_CAN_MERGE);
            $attributes = $group->getProperty('attributes');
            $ieCondition = $group->getProperty('ie_condition');
            $flagName = $group->getProperty('flag_name');

            if ($flagName && !$this->getData($flagName)) {
                continue;
            }

            $groupAssets = $group->getAll();
            $groupAssets = $this->_assetMinifyService->getAssets($groupAssets);
            if ($canMerge && count($groupAssets) > 1) {
                $groupAssets = $this->_assetMergeService->getMergedAssets($groupAssets, $contentType);
            }

            if (!empty($attributes)) {
                if (is_array($attributes)) {
                    $attributesString = '';
                    foreach ($attributes as $name => $value) {
                        $attributesString .= ' ' . $name . '="' . $this->escapeHtml($value) . '"';
                    }
                    $attributes = $attributesString;
                } else {
                    $attributes = ' ' . $attributes;
                }
            }

            if ($contentType == Magento_Core_Model_View_Publisher::CONTENT_TYPE_JS ) {
                $groupTemplate = '<script' . $attributes . ' type="text/javascript" src="%s"></script>' . "\n";
            } else {
                if ($contentType == Magento_Core_Model_View_Publisher::CONTENT_TYPE_CSS) {
                    $attributes = ' rel="stylesheet" type="text/css"' . ($attributes ?: ' media="all"');
                }
                $groupTemplate = '<link' . $attributes . ' href="%s" />' . "\n";
            }

            $groupHtml = $this->_renderHtml($groupTemplate, $groupAssets);

            if (!empty($ieCondition)) {
                $groupHtml = '<!--[if ' . $ieCondition . ']>' . "\n" . $groupHtml . '<![endif]-->' . "\n";
            }

            $result .= $groupHtml;
        }
        return $result;
    }

    /**
     * Render HTML tags referencing corresponding URLs
     *
     * @param string $template
     * @param array|Iterator $assets
     * @return string
     */
    protected function _renderHtml($template, $assets)
    {
        $result = '';
        try {
            /** @var $asset Magento_Core_Model_Page_Asset_AssetInterface */
            foreach ($assets as $asset) {
                $result .= sprintf($template, $asset->getUrl());
            }
        } catch (Magento_Exception $e) {
            $result .= sprintf($template, $this->_getNotFoundUrl());
        }
        return $result;
    }

    /**
     * Retrieve Content Type
     *
     * @return string
     */
    public function getContentType()
    {
        if (empty($this->_data['content_type'])) {
            $this->_data['content_type'] = $this->getMediaType() . '; charset=' . $this->getCharset();
        }
        return $this->_data['content_type'];
    }

    /**
     * Retrieve Media Type
     *
     * @return string
     */
    public function getMediaType()
    {
        if (empty($this->_data['media_type'])) {
            $this->_data['media_type'] = $this->_storeConfig->getConfig('design/head/default_media_type');
        }
        return $this->_data['media_type'];
    }

    /**
     * Retrieve Charset
     *
     * @return string
     */
    public function getCharset()
    {
        if (empty($this->_data['charset'])) {
            $this->_data['charset'] = $this->_storeConfig->getConfig('design/head/default_charset');
        }
        return $this->_data['charset'];
    }

    /**
     * Set title element text
     *
     * @param string|array $title
     * @return Magento_Page_Block_Html_Head
     */
    public function setTitle($title)
    {
        if (is_array($title)) {
            $this->_titleChunks = $title;
            $title = implode(' / ', $title);
        } else {
            $this->_pureTitle = $title;
        }
        $this->_data['title'] = $this->_storeConfig->getConfig('design/head/title_prefix') . ' ' . $title
            . ' ' . $this->_storeConfig->getConfig('design/head/title_suffix');
        return $this;
    }

    /**
     * Retrieve title element text (encoded)
     *
     * @return string
     */
    public function getTitle()
    {
        if (empty($this->_data['title'])) {
            $this->_data['title'] = $this->getDefaultTitle();
        }
        return htmlspecialchars(html_entity_decode(trim($this->_data['title']), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Same as getTitle(), but return only first item from chunk for backend pages
     *
     * @return mixed|string
     */
    public function getShortTitle()
    {
        if (!empty($this->_titleChunks)) {
            return reset($this->_titleChunks);
        } else {
            return $this->_pureTitle;
        }
    }

    /**
     * Retrieve default title text
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return $this->_storeConfig->getConfig('design/head/default_title');
    }

    /**
     * Retrieve content for description tag
     *
     * @return string
     */
    public function getDescription()
    {
        if (empty($this->_data['description'])) {
            $this->_data['description'] = $this->_storeConfig->getConfig('design/head/default_description');
        }
        return $this->_data['description'];
    }

    /**
     * Retrieve content for keywords tag
     *
     * @return string
     */
    public function getKeywords()
    {
        if (empty($this->_data['keywords'])) {
            $this->_data['keywords'] = $this->_storeConfig->getConfig('design/head/default_keywords');
        }
        return $this->_data['keywords'];
    }

    /**
     * Retrieve URL to robots file
     *
     * @return string
     */
    public function getRobots()
    {
        if (empty($this->_data['robots'])) {
            $this->_data['robots'] = $this->_storeConfig->getConfig('design/search_engine_robots/default_robots');
        }
        return $this->_data['robots'];
    }

    /**
     * Get miscellaneous scripts/styles to be included in head before head closing tag
     *
     * @return string
     */
    public function getIncludes()
    {
        if (empty($this->_data['includes'])) {
            $this->_data['includes'] = $this->_storeConfig->getConfig('design/head/includes');
        }
        return $this->_data['includes'];
    }

    /**
     * Getter for path to Favicon
     *
     * @return string
     */
    public function getFaviconFile()
    {
        if (empty($this->_data['favicon_file'])) {
            $this->_data['favicon_file'] = $this->_getFaviconFile();
        }
        return $this->_data['favicon_file'];
    }

    /**
     * Retrieve path to Favicon
     *
     * @return string
     */
    protected function _getFaviconFile()
    {
        $folderName = Magento_Backend_Model_Config_Backend_Image_Favicon::UPLOAD_DIR;
        $storeConfig = $this->_storeConfig->getConfig('design/head/shortcut_icon');
        $faviconFile = Mage::getBaseUrl('media') . $folderName . '/' . $storeConfig;
        $absolutePath = Mage::getBaseDir('media') . '/' . $folderName . '/' . $storeConfig;

        if (!is_null($storeConfig) && $this->_isFile($absolutePath)) {
            $url = $faviconFile;
        } else {
            $url = $this->getViewFileUrl('Magento_Page::favicon.ico');
        }
        return $url;
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename
     * @return bool
     */
    protected function _isFile($filename)
    {
        if ($this->_fileStorageDatabase->checkDbUsage() && !is_file($filename)) {
            $this->_fileStorageDatabase->saveFileToFilesystem($filename);
        }
        return is_file($filename);
    }

    /**
     * Retrieve locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
    }
}
