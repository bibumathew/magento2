<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Product form image field helper
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Catalog\Product\Helper\Form;

class BaseImage extends \Magento\Data\Form\Element\AbstractElement
{
    /**
     * Model Url instance
     *
     * @var \Magento\Backend\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelperData;

    /**
     * @var \Magento\File\Size
     */
    protected $_fileConfig;

    /**
     * @var \Magento\Core\Model\View\Url
     */
    protected $_viewUrl;

    /**
     * @param \Magento\Escaper $escaper
     * @param \Magento\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Core\Model\View\UrlFactory $coreViewUrlFactory
     * @param \Magento\Backend\Model\UrlFactory $backendUrlFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\File\Size $fileConfig
     * @param array $attributes
     */
    public function __construct(
        \Magento\Escaper $escaper,
        \Magento\Data\Form\Element\Factory $factoryElement,
        \Magento\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Core\Model\View\UrlFactory $coreViewUrlFactory,
        \Magento\Backend\Model\UrlFactory $backendUrlFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\File\Size $fileConfig,
        array $attributes = array()
    ) {
        parent::__construct($escaper, $factoryElement, $factoryCollection, $attributes);

        $this->_viewUrl = $coreViewUrlFactory->create();
        $this->_url = $backendUrlFactory->create();
        $this->_catalogHelperData = $catalogData;
        $this->_fileConfig = $fileConfig;
        $this->_maxFileSize = $this->_getFileMaxSize();
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Images');
    }

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $htmlId = $this->_escaper->escapeHtml($this->getHtmlId());
        $uploadUrl = $this->_escaper->escapeHtml($this->_getUploadUrl());
        $spacerImage = $this->_viewUrl->getViewFileUrl('images/spacer.gif');
        $imagePlaceholderText = __('Click here or drag and drop to add images');
        $deleteImageText = __('Delete image');
        $makeBaseText = __('Make Base');
        $hiddenText = __('Hidden');
        $imageManagementText = __('Image Management');
        /** @var $product \Magento\Catalog\Model\Product */
        $html = <<<HTML
<div id="{$htmlId}-container" class="images"
    data-mage-init="{baseImage:{}}"
    data-max-file-size="{$this->_getFileMaxSize()}"
    >
    <div class="image image-placeholder">
        <input type="file" name="image" data-url="{$uploadUrl}" multiple="multiple" />
        <img class="spacer" src="{$spacerImage}"/>
        <p class="image-placeholder-text">{$imagePlaceholderText}</p>
    </div>
    <script id="{$htmlId}-template" class="image-template" type="text/x-jquery-tmpl">
        <div class="image">
            <img class="spacer" src="{$spacerImage}"/>
            <img class="product-image" src="\${url}" data-position="\${position}" alt="\${label}" />
            <div class="actions">
                <button class="action-delete" data-role="delete-button" title="{$deleteImageText}">
                    <span>{$deleteImageText}</span>
                </button>
                <button class="action-make-base" data-role="make-base-button" title="{$makeBaseText}">
                    <span>{$makeBaseText}</span>
                </button>
                <div class="draggable-handle"></div>
            </div>
            <div class="image-label"></div>
            <div class="image-fade"><span>{$hiddenText}</span></div>
        </div>
    </script>
</div>
<span class="action-manage-images" data-activate-tab="image-management">
    <span>{$imageManagementText}</span>
</span>
<script>
    (function($) {
        'use strict';

        $('[data-activate-tab=image-management]')
            .on('click.toggleImageManagementTab', function() {
                $('#product_info_tabs_image-management').trigger('click');
            });
    })(window.jQuery);
</script>

HTML;
        return $html;
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    protected function _getUploadUrl()
    {
        return $this->_url->getUrl('adminhtml/catalog_product_gallery/upload');
    }

    /**
     * Get maximum file size to upload in bytes
     *
     * @return int
     */
    protected function _getFileMaxSize()
    {
        return $this->_fileConfig->getMaxFileSize();
    }
}
