<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Banner Widget Block
 *
 * @category   Enterprise
 * @package    Enterprise_Banner
 */
class Enterprise_Banner_Block_Widget_Banner
    extends Magento_Core_Block_Template
    implements Magento_Widget_Block_Interface
{
    /**
     * Display mode "fixed" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_FIXED = 'fixed';

    /**
     * Display mode "salesrule" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_SALESRULE = 'salesrule';

    /**
     * Display mode "catalogrule" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_CATALOGRULE = 'catalogrule';

    /**
     * Rotation mode "series" flag: output one of banners sequentially per visitor session
     *
     */
    const BANNER_WIDGET_RORATE_SERIES = 'series';

    /**
     * Rotation mode "random" flag: output one of banners randomly
     *
     */
    const BANNER_WIDGET_RORATE_RANDOM = 'random';

    /**
     * Rotation mode "shuffle" flag: same as "series" but firstly randomize banners scope
     *
     */
    const BANNER_WIDGET_RORATE_SHUFFLE = 'shuffle';

    /**
     * No rotation: show all banners at once
     *
     */
    const BANNER_WIDGET_RORATE_NONE = '';

    /**
     * Store Banner resource instance
     *
     * @var Enterprise_Banner_Model_Resource_Banner
     */
    protected $_bannerResource;

    /**
     * @var Magento_Core_Model_Session
     */
    protected $_coreSession;

    /**
     * @var Magento_Checkout_Model_Session
     */
    protected $_checkoutSession;

    /**
     * @var Magento_Customer_Model_Session
     */
    protected $_customerSession;

    /**
     * @var Magento_Cms_Helper_Data
     */
    protected $_cmsHelper;

    /**
     * @var int
     */
    protected $_currentStoreId;

    /**
     * @var int
     */
    protected $_currentWebsiteId;

    /**
     * Stores information about process of selecting banners to render
     * E.g. list of banner ids for this user, rendered banner id(s) and so on.
     */
    protected $_renderedParams = array();

    /**
     * @param array $data
     * @param Magento_Core_Helper_Data $coreData
     * @param  $context
     * @param  $resource
     * @param  $coreSession
     * @param  $checkoutSession
     * @param  $customerSession
     * @param  $cmsHelper
     * @param  $storeManager
     * @param  $data
     */
    public function __construct(
        array $data,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Enterprise_Banner_Model_Resource_Banner $resource,
        Magento_Core_Model_Session $coreSession,
        Magento_Checkout_Model_Session $checkoutSession,
        Magento_Customer_Model_Session $customerSession,
        Magento_Cms_Helper_Data $cmsHelper,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        parent::__construct($coreData, $data, $context, $data);
        $this->_bannerResource = $resource;
        $this->_coreSession = $coreSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_cmsHelper = $cmsHelper;
        $this->_currentStoreId  = $storeManager->getStore()->getId();
        $this->_currentWebsiteId  = $storeManager->getWebsite()->getId();
    }

    /**
     * Set default display mode if its not set
     *
     * @return string
     */
    public function getDisplayMode()
    {
        if (!$this->_getData('display_mode')) {
            $this->setData('display_mode', self::BANNER_WIDGET_DISPLAY_FIXED);
        }
        return $this->_getData('display_mode');
    }

    /**
     * Retrive converted to an array and filtered parameter "banner_ids"
     *
     * @return array
     */
    public function getBannerIds()
    {
        if (!$this->_getData('banner_ids')) {
            $this->setData('banner_ids', array(0));
        } elseif (is_string($this->_getData('banner_ids'))) {
            $bannerIds = explode(',', $this->_getData('banner_ids'));
            foreach ($bannerIds as $_key => $_id) {
                $bannerIds[$_key] = (int)trim($_id);
            }
            $bannerIds = $this->_bannerResource->getExistingBannerIdsBySpecifiedIds($bannerIds);
            $this->setData('banner_ids', $bannerIds);
        }

        return $this->_getData('banner_ids');
    }

    /**
     * Retrieve right rotation mode or return null
     *
     * @return string|null
     */
    public function getRotate()
    {
        if (!$this->_getData('rotate') || ($this->_getData('rotate') != self::BANNER_WIDGET_RORATE_RANDOM &&
                                           $this->_getData('rotate') != self::BANNER_WIDGET_RORATE_SERIES &&
                                           $this->_getData('rotate') != self::BANNER_WIDGET_RORATE_SHUFFLE
                                           )) {
            $this->setData('rotate', null);
        }
        return $this->_getData('rotate');
    }

    /**
     * Set unique id of widget instance if its not set
     *
     * @return string
     */
    public function getUniqueId()
    {
        if (!$this->_getData('unique_id')) {
            $this->setData('unique_id', md5(implode('-', $this->getBannerIds())));
        }
        return $this->_getData('unique_id');
    }

    /**
     * Get banner(s) content to display
     *
     * @return array
     */
    public function getBannersContent()
    {
        $this->_bannerResource->filterByTypes($this->getTypes());

        // Choose display mode
        switch ($this->getDisplayMode()) {

            case self::BANNER_WIDGET_DISPLAY_SALESRULE:
                $appliedRules = array();
                if ($this->_checkoutSession->getQuoteId()) {
                    $quote = $this->_checkoutSession->getQuote();
                    if ($quote && $quote->getAppliedRuleIds()) {
                        $appliedRules = explode(',', $quote->getAppliedRuleIds());
                    }
                }
                $bannerIds = $this->_bannerResource->getSalesRuleRelatedBannerIds($appliedRules);
                $bannersContent = $this->_getBannersContent($bannerIds);
                break;

            case self::BANNER_WIDGET_DISPLAY_CATALOGRULE :
                $bannerIds = $this->_bannerResource->getCatalogRuleRelatedBannerIds(
                    $this->_currentWebsiteId,
                    $this->_customerSession->getCustomerGroupId()
                );
                $bannersContent = $this->_getBannersContent($bannerIds);
                break;

            case self::BANNER_WIDGET_DISPLAY_FIXED :
            default :
                $bannersContent = $this->_getBannersContent($this->getBannerIds());
                break;
        }

        // Unset types filter from resource
        $this->_bannerResource->filterByTypes();

        // Filtering directives
        $processor = $this->_cmsHelper->getPageTemplateProcessor();
        foreach ($bannersContent as $bannerId => $content) {
            $bannersContent[$bannerId] = $processor->filter($content);
        }

        return $bannersContent;
    }

    /**
     * Retrieves suggested params for rendering the banner - array with following keys:
     * - 'bannersSelected' - array of banner ids suggested to render (null if not set)
     * - 'bannersSequence' - array of banner ids already shown to user (null if not set)
     * These parameters are set by cache when it needs to render some specific banners. However,
     * if parameters are not valid - they must be ignored, because block has fresh and up-to-date values
     * to check the banners that can be shown to user.
     *
     * @return array
     */
    public function getSuggestedParams()
    {
        $params = $this->getData('suggested_params');
        if (!$params) {
            $params = array();
        }

        // Ensure that option keys exist
        $keys = array('bannersSelected', 'bannersSequence');
        foreach ($keys as $key) {
            if (!isset($params[$key])) {
                $params[$key] = null;
            }
        }

        return $params;
    }

    /**
     * Get banners content by specified banners IDs depend on Rotation mode
     *
     * @param array $bannerIds
     * @return array
     */
    protected function _getBannersContent(array $bannerIds)
    {
        $this->_setRenderedParam('bannerIds', $bannerIds)
            ->_setRenderedParam('renderedBannerIds', array());

        $content = array();
        if (!empty($bannerIds)) {
            $bannerResource = $this->_bannerResource;

            // Process suggested params
            $suggestedParams = $this->getSuggestedParams();
            $suggBannersSelected = $suggestedParams['bannersSelected'];
            $suggBannersSequence = $suggestedParams['bannersSequence'];

            // Choose banner depending on rotation mode
            switch ($this->getRotate()) {
                case self::BANNER_WIDGET_RORATE_RANDOM:
                    // Choose banner either as suggested or randomly
                    $bannerId = null;
                    if ($suggBannersSelected && count($suggBannersSelected) == 1) {
                        $suggBannerId = $suggBannersSelected[0];
                        if (array_search($suggBannerId, $bannerIds) !== false) {
                            $bannerId = $suggBannerId;
                        }
                    }
                    if ($bannerId === null) {
                        $bannerId = $bannerIds[array_rand($bannerIds, 1)];
                    }

                    $_content = $bannerResource->getStoreContent($bannerId, $this->_currentStoreId);
                    if (!empty($_content)) {
                        $content[$bannerId] = $_content;
                    }
                    $this->_setRenderedParam('renderedBannerIds', array($bannerId));
                    break;

                case self::BANNER_WIDGET_RORATE_SHUFFLE:
                case self::BANNER_WIDGET_RORATE_SERIES:
                    $isShuffle = $this->getRotate() == self::BANNER_WIDGET_RORATE_SHUFFLE;
                    $bannerId = null;
                    $bannersSequence = null;

                    // Compose banner sequence either from suggested sequence or from user session data
                    if ($suggBannersSequence !== null) {
                        // Check that suggested sequence is valid - contains only banner ids from list
                        if (!array_diff($suggBannersSequence, $bannerIds)) {
                            $bannersSequence = $suggBannersSequence;
                        }
                    }
                    if ($bannersSequence === null) {
                        $bannersSequence = $this->_coreSession->_getData($this->getUniqueId());
                    }

                    // Check that we have suggested banner to render
                    $suggBannerId = null;
                    if ($suggBannersSelected && count($suggBannersSelected) == 1) {
                        $suggBannerId = $suggBannersSelected[0];
                    }

                    // If some banners were shown, get the list of unshown ones and choose banner to show
                    if ($bannersSequence) {
                        $canShowIds = array_merge(array_diff($bannerIds, $bannersSequence), array());
                        if (!empty($canShowIds)) {
                            // Stil not whole serie is shown, choose the banner to show
                            if ($suggBannerId && (array_search($suggBannerId, $canShowIds) !== false)) {
                                $bannerId = $suggBannerId;
                            } else {
                                $showKey = $isShuffle ? array_rand($canShowIds, 1) : 0;
                                $bannerId = $canShowIds[$showKey];
                            }
                            $bannersSequence[] = $bannerId;
                        }
                    }

                    // Start new serie (either no banners has been shown at all or whole serie has been shown)
                    if (!$bannerId) {
                        if ($suggBannerId && (array_search($suggBannerId, $bannerIds) !== false)) {
                            $bannerId = $suggBannerId;
                        } else {
                            $bannerKey = $isShuffle ? array_rand($bannerIds, 1) : 0;
                            $bannerId = $bannerIds[$bannerKey];
                        }
                        $bannersSequence = array($bannerId);
                    }

                    $this->_coreSession->setData($this->getUniqueId(), $bannersSequence);

                    $_content = $bannerResource->getStoreContent($bannerId, $this->_currentStoreId);
                    if (!empty($_content)) {
                        $content[$bannerId] = $_content;
                    }
                    $this->_setRenderedParam('renderedBannerIds', array($bannerId))
                        ->_setRenderedParam('bannersSequence', $bannersSequence);
                    break;

                default:
                    // We must always render all available banners - so suggested values are ignored
                    $content = $bannerResource->getBannersContent($bannerIds, $this->_currentStoreId);
                    $this->_setRenderedParam('renderedBannerIds', $bannerIds);
                    break;
            }
        }

        return $content;
    }

    /**
     * Get cache key informative items that must be preserved in cache placeholders
     * for block to be rerendered by placeholder
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $items = array(
            'name' => $this->getNameInLayout(),
            'types' => $this->getTypes(),
            'display_mode' => $this->getDisplayMode(),
            'rotate' => (string) $this->getRotate(),
            'banner_ids' => implode(',', $this->getBannerIds()),
            'unique_id' => $this->getUniqueId()
        );

        $items = parent::getCacheKeyInfo() + $items;

        return $items;
    }

    /**
     * Clears information about rendering process parameters.
     *
     * @return Enterprise_Banner_Block_Widget_Banner
     */
    protected function _clearRenderedParams()
    {
        $this->_renderedParams = array();
        return $this;
    }

    /**
     * Returns parameters about last banner rendering that this block has performed.
     * Used to know the information about process this block implemented to choose banners depending on
     * customer and select one/all of them to render.
     *
     * @return array
     */
    protected function _getRenderedParams()
    {
        return $this->_renderedParams;
    }

    /**
     * Sets rendered param information
     *
     * @return Enterprise_Banner_Block_Widget_Banner
     */
    protected function _setRenderedParam($key, $value)
    {
        $this->_renderedParams[$key] = $value;
        return $this;
    }

    /**
     * Clears information about rendering process parameters and renders block (new parameters are filled
     * during this process).
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_clearRenderedParams();
        return parent::_toHtml();
    }

    /**
     * Returns rendered html and information about data used to render the banners.
     * Used by cache placeholder to get html and additional data about it, so later cache placeholder
     * can make some actions (randomize banners) on its own.
     *
     * @return array
     */
    public function renderAndGetInfo()
    {
        $result = array(
            'html' => $this->toHtml(),
            'params' => $this->_getRenderedParams()
        );
        return $result;
    }

}
