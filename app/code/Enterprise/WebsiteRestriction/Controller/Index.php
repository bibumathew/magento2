<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_WebsiteRestriction
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Website stub controller
 *
 * @category    Enterprise
 * @package     Enterprise_WebsiteRestriction
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_WebsiteRestriction_Controller_Index extends Mage_Core_Controller_Front_Action
{
    protected $_stubPageIdentifier = Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_LANDING_PAGE;

    /**
     * @var Mage_Core_Model_Cache_Type_Config
     */
    protected $_configCacheType;

    protected $_cacheKey;

    /**
     * Prefix for cache id
     */
    protected $_cacheKeyPrefix = 'RESTRICTION_LANGING_PAGE_';

    /**
     * @param Mage_Core_Controller_Varien_Action_Context $context
     * @param Mage_Core_Model_Cache_Type_Config $configCacheType
     * @param string $areaCode
     */
    public function __construct(
        Mage_Core_Controller_Varien_Action_Context $context,
        Mage_Core_Model_Cache_Type_Config $configCacheType,
        $areaCode = null
    ) {
        parent::__construct($context, $areaCode);
        $this->_configCacheType = $configCacheType;
    }

    protected function _construct()
    {
        $this->_cacheKey = $this->_cacheKeyPrefix . Mage::app()->getWebsite()->getId();
    }

    /**
     * Display a pre-cached CMS-page if we have such or generate new one
     *
     */
    public function stubAction()
    {
        $cachedData = $this->_configCacheType->load($this->_cacheKey);
        if ($cachedData) {
            $this->getResponse()->setBody($cachedData);
        } else {
            /**
             * Generating page and save it to cache
             */
            $page = Mage::getModel('Mage_Cms_Model_Page')
                ->load(Mage::getStoreConfig($this->_stubPageIdentifier), 'identifier');

            Mage::register('restriction_landing_page', $page);

            if ($page->getCustomTheme()) {
                if (Mage::app()->getLocale()
                    ->isStoreDateInInterval(null, $page->getCustomThemeFrom(), $page->getCustomThemeTo())
                ) {
                    Mage::getDesign()->setDesignTheme($page->getCustomTheme());
                }
            }

            $this->addActionLayoutHandles();

            if ($page->getRootTemplate()) {
                $this->getLayout()->helper('Mage_Page_Helper_Layout')
                    ->applyHandle($page->getRootTemplate());
            }

            $this->loadLayoutUpdates();

            $this->getLayout()->getUpdate()->addUpdate($page->getLayoutUpdateXml());
            $this->generateLayoutXml()->generateLayoutBlocks();

            if ($page->getRootTemplate()) {
                $this->getLayout()->helper('Mage_Page_Helper_Layout')
                    ->applyTemplate($page->getRootTemplate());
            }

            $this->renderLayout();

            $this->_configCacheType->save(
                $this->getResponse()->getBody(), $this->_cacheKey, array(Mage_Core_Model_Website::CACHE_TAG)
            );
        }
    }
}
