<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Inline Translations PHP part
 */
class Mage_Core_Model_Translate_Inline implements Mage_Core_Model_Translate_InlineInterface
{
    /**
     * Indicator to hold state of whether inline translation is allowed
     *
     * @var bool
     */
    protected $_isAllowed;

    /**
     * @var Mage_Core_Model_Translate_InlineParser
     */
    protected $_parser;

    /**
     * Initialize inline translation model
     *
     * @param Mage_Core_Model_Translate_InlineParser $parser
     */
    public function __construct(
        Mage_Core_Model_Translate_InlineParser $parser
    ) {
        $this->_parser = $parser;
    }

    /**
     * Is enabled and allowed Inline Translates
     *
     * @param mixed $store
     * @return bool
     */
    public function isAllowed($store = null)
    {
        if (is_null($this->_isAllowed)) {
            if (is_null($store)) {
                $store = $this->_parser->getStoreManager()->getStore();
            }
            if (!$store instanceof Mage_Core_Model_Store) {
                $store = $this->_parser->getStoreManager()->getStore($store);
            }

            if ($this->_parser->getDesignPackage()->getArea() == 'adminhtml') {
                $active = Mage::getStoreConfigFlag('dev/translate_inline/active_admin', $store);
            } else {
                $active = Mage::getStoreConfigFlag('dev/translate_inline/active', $store);
            }
            $this->_isAllowed = $active && $this->_parser->getHelper()->isDevAllowed($store);
        }
        return $this->_parser->getHelper()->getTranslator()->getTranslateInline() && $this->_isAllowed;
    }

    /**
     * Replace translation templates with HTML fragments
     *
     * @param array|string $body
     * @param bool $isJson
     * @return Mage_Core_Model_Translate_Inline
     */
    public function processResponseBody(&$body, $isJson)
    {
        $this->_parser->setIsJson($isJson);
        if (!$this->isAllowed()) {
            if ($this->_parser->getDesignPackage()->getArea() == Mage_Backend_Helper_Data::BACKEND_AREA_CODE) {
                $this->_stripInlineTranslations($body);
            }
            return $this;
        }

        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->processResponseBody($part, $isJson);
            }
        } elseif (is_string($body)) {
            $content = $this->_parser->processResponseBodyString($body, $this);
            $this->_insertInlineScriptsHtml($content);
            $body = $this->_parser->getContent();
        }
        $this->_parser->setIsJson(Mage_Core_Model_Translate_InlineParser::JSON_FLAG_DEFAULT_STATE);
        return $this;
    }

    /**
     * Format translation for special tags
     *
     * @param string $tagHtml
     * @param string $tagName
     * @param array $trArr
     * @return string
     */
    public function applySpecialTagsFormat($tagHtml, $tagName, $trArr)
    {
        return $tagHtml . '<span class="translate-inline-' . $tagName . '" '
            . $this->_parser->getHtmlAttribute(Mage_Core_Model_Translate_InlineParser::DATA_TRANSLATE,
                htmlspecialchars('['
            . join(',', $trArr) . ']'))
            . '>' . strtoupper($tagName) . '</span>';
    }

    /**
     * Format translation for simple tags
     *
     * @param string $tagHtml
     * @param string  $tagName
     * @param array $trArr
     * @return string
     */
    public function applySimpleTagsFormat($tagHtml, $tagName, $trArr)
    {
        return substr($tagHtml, 0, strlen($tagName) + 1) . ' '
            . $this->_parser->getHtmlAttribute(Mage_Core_Model_Translate_InlineParser::DATA_TRANSLATE,
                htmlspecialchars('['
            . join(',', $trArr) . ']'))
            . substr($tagHtml, strlen($tagName) + 1);
    }

    /**
     * Add data-translate-mode attribute
     *
     * @param string $trAttr
     * @return string
     */
    public function addTranslateAttribute($trAttr)
    {
        return $trAttr;
    }

    /**
     * Returns the html span that contains the data translate attribute
     *
     * @param string $data
     * @param string $text
     * @return string
     */
    public function getDataTranslateSpan($data, $text)
    {
        return '<span ' . $this->_parser->getHtmlAttribute(Mage_Core_Model_Translate_InlineParser::DATA_TRANSLATE,
                $data)
            . '>' . $text . '</span>';
    }

    /**
     * Create block to render script and html with added inline translation content.
     */
    private function _insertInlineScriptsHtml($content)
    {
        if ($this->_isScriptInserted || stripos($content, '</body>') === false) {
            return;
        }

        $store = $this->_parser->getStoreManager()->getStore();
        if ($store->isAdmin()) {
            $urlPrefix = Mage_Backend_Helper_Data::BACKEND_AREA_CODE;
            $urlModel = Mage::getObjectManager()->get('Mage_Backend_Model_Url');
        } else {
            $urlPrefix = 'core';
            $urlModel = Mage::getObjectManager()->get('Mage_Core_Model_Url');
        }
        $ajaxUrl = $urlModel->getUrl($urlPrefix . '/ajax/translate',
            array('_secure' => $store->isCurrentlySecure()));

        /** @var $block Mage_Core_Block_Template */
        $block = Mage::getObjectManager()->create('Mage_Core_Block_Template');

        $block->setAjaxUrl($ajaxUrl);

        $block->setTemplate('Mage_Core::translate_inline.phtml');

        $html = $block->toHtml();

        //$this->_content = str_ireplace('</body>', $html . '</body>', $this->_content);
        $this->_parser->setContent(str_ireplace('</body>', $html . '</body>', $content));

        $this->_isScriptInserted = true;
    }

    /**
     * Strip inline translations from text
     *
     * @param array|string $body
     * @return Mage_Core_Model_Translate_Inline
     */
    private function _stripInlineTranslations(&$body)
    {
        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->_stripInlineTranslations($part);
            }
        } else if (is_string($body)) {
            $body = preg_replace('#' . $this->_tokenRegex . '#', '$1', $body);
        }
        return $this;
    }
}
