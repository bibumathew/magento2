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
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Page\Block;

class Html extends \Magento\View\Block\Template
{
    protected $_urls = array();
    protected $_title = '';

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\View\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Core\Helper\Data $coreData,
        \Magento\View\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        parent::__construct($coreData, $context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->_urls = array(
            'base'      => $this->_storeManager->getStore()->getBaseUrl('web'),
            'baseSecure'=> $this->_storeManager->getStore()->getBaseUrl('web', true),
            'current'   => $this->_request->getRequestUri()
        );

        $action = $this->_frontController->getAction();
        if ($action) {
            $this->addBodyClass($action->getFullActionName('-'));
        }

        $this->_beforeCacheUrl();
    }

    public function getBaseUrl()
    {
        return $this->_urls['base'];
    }

    public function getBaseSecureUrl()
    {
        return $this->_urls['baseSecure'];
    }

    public function getCurrentUrl()
    {
        return $this->_urls['current'];
    }

    /**
     *  Print Logo URL (Conf -> Sales -> Invoice and Packing Slip Design)
     *
     *  @return string
     */
    public function getPrintLogoUrl ()
    {
        // load html logo
        $logo = $this->_storeConfig->getConfig('sales/identity/logo_html');
        if (!empty($logo)) {
            $logo = 'sales/store/logo_html/' . $logo;
        }

        // load default logo
        if (empty($logo)) {
            $logo = $this->_storeConfig->getConfig('sales/identity/logo');
            if (!empty($logo)) {
                // prevent tiff format displaying in html
                if (strtolower(substr($logo, -5)) === '.tiff' || strtolower(substr($logo, -4)) === '.tif') {
                    $logo = '';
                }
                else {
                    $logo = 'sales/store/logo/' . $logo;
                }
            }
        }

        // buld url
        if (!empty($logo)) {
            $logo = $this->_urlBuilder->getBaseUrl(array('_type' => \Magento\Core\Model\Store::URL_TYPE_MEDIA)) . $logo;
        }
        else {
            $logo = '';
        }

        return $logo;
    }

    public function getPrintLogoText()
    {
        return $this->_storeConfig->getConfig('sales/identity/address');
    }

    public function setHeaderTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function getHeaderTitle()
    {
        return $this->_title;
    }

    /**
     * Add CSS class to page body tag
     *
     * @param string $className
     * @return \Magento\Page\Block\Html
     */
    public function addBodyClass($className)
    {
        $className = preg_replace('#[^a-z0-9]+#', '-', strtolower($className));
        $this->setBodyClass($this->getBodyClass() . ' ' . $className);
        return $this;
    }

    public function getLang()
    {
        if (!$this->hasData('lang')) {
            $this->setData('lang', substr($this->_locale->getLocaleCode(), 0, 2));
        }
        return $this->getData('lang');
    }

    public function getBodyClass()
    {
        return $this->_getData('body_class');
    }

    public function getAbsoluteFooter()
    {
        return $this->_storeConfig->getConfig('design/footer/absolute_footer');
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        return $this->_afterCacheUrl($html);
    }
}
