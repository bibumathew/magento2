<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog Event on category page
 */
namespace Magento\CatalogEvent\Block\Catalog\Product;

class Event extends \Magento\CatalogEvent\Block\Event\AbstractEvent
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry;

    /**
     * Catalog event data
     *
     * @var \Magento\CatalogEvent\Helper\Data
     */
    protected $_catalogEventData;

    /**
     * Construct
     *
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param Magento_Core_Model_LocaleInterface $locale
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_CatalogEvent_Helper_Data $catalogEventData
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Magento_Core_Model_LocaleInterface $locale,
        Magento_Core_Model_Registry $registry,
        Magento_CatalogEvent_Helper_Data $catalogEventData,
        array $data = array()
    ) {
        parent::__construct($coreData, $context, $locale, $data);
        
        $this->_coreRegistry = $registry;
        $this->_catalogEventData = $catalogEventData;
    }

    /**
     * Return current category event
     *
     * @return Magento_CategoryEvent_Model_Event
     */
    public function getEvent()
    {
        if ($this->getProduct()) {
            return $this->getProduct()->getEvent();
        }

        return false;
    }

    /**
     * Return current category
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Check availability to display event block
     *
     * @return boolean
     */
    public function canDisplay()
    {
        return $this->_catalogEventData->isEnabled()
            && $this->getProduct()
            && $this->getEvent()
            && $this->getEvent()->canDisplayProductPage()
            && !$this->getProduct()->getEventNoTicker();
    }
}
