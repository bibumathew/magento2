<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_CatalogEvent
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog Event homepage block
 *
 * @category   Enterprise
 * @package    Enterprise_CatalogEvent
 */
class Enterprise_CatalogEvent_Block_Event_Lister extends Enterprise_CatalogEvent_Block_Event_Abstract
{
    /**
     * Events list
     *
     * @var array
     */
    protected $_events = null;

    /**
     * Catalog event data
     *
     * @var Enterprise_CatalogEvent_Helper_Data
     */
    protected $_catalogEventData = null;

    /**
     * Catalog category
     *
     * @var Magento_Catalog_Helper_Category
     */
    protected $_catalogCategory = null;

    /**
     * @param Magento_Catalog_Helper_Category $catalogCategory
     * @param Enterprise_CatalogEvent_Helper_Data $catalogEventData
     * @param Magento_Core_Block_Template_Context $coreData
     * @param array $context
     * @param  $data
     */
    public function __construct(
        Magento_Catalog_Helper_Category $catalogCategory,
        Enterprise_CatalogEvent_Helper_Data $catalogEventData,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogEventData = $catalogEventData;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Retrieve html id
     *
     * @return string
     */
    public function getHtmlId()
    {
        if (!$this->hasData('html_id')) {
            $this->setData('html_id', 'id_' . md5(uniqid('catalogevent', true)));
        }

        return $this->getData('html_id');
    }

    /**
     * Check whether the block can be displayed
     *
     * @return bool
     */
    public function canDisplay()
    {
        return $this->_catalogEventData->isEnabled()
            && Mage::getStoreConfigFlag('catalog/enterprise_catalogevent/lister_output')
            && (count($this->getEvents()) > 0);
    }

    /**
     * Retrieve categories with events
     *
     * @return array
     */
    public function getEvents()
    {
        if ($this->_events === null) {
            $this->_events = array();
            $categories = $this->_catalogCategory->getStoreCategories('position', true, false);
            if (($categories instanceof Magento_Eav_Model_Entity_Collection_Abstract) ||
                ($categories instanceof Magento_Core_Model_Resource_Db_Collection_Abstract)) {
                $allIds = $categories->getAllIds();
            } else {
                $allIds = array();
            }

            if (!empty($allIds)) {
                $eventCollection = Mage::getModel('Enterprise_CatalogEvent_Model_Event')
                    ->getCollection();
                $eventCollection->addFieldToFilter('category_id', array('in' => $allIds))
                    ->addVisibilityFilter()
                    ->addImageData()
                    ->addSortByStatus()
                ;

                $categories->addIdFilter(
                    $eventCollection->getColumnValues('category_id')
                );

                foreach ($categories as $category) {
                    $event = $eventCollection->getItemByColumnValue('category_id', $category->getId());
                    if ($category->getIsActive()) {
                        $event->setCategory($category);
                    } else {
                        $eventCollection->removeItemByKey($event->getId());
                    }
                }

                foreach ($eventCollection as $event) {
                    $this->_events[] = $event;
                }


            }
        }

        return $this->_events;
    }

    /**
     * Retreive category url
     *
     * @param Magento_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        return $this->_catalogCategory->getCategoryUrl($category);
    }

    /**
     * Retrieve catalog category image url
     *
     * @param Enterprise_CatalogEvent_Model_Event $event
     * @return string
     */
    public function getEventImageUrl($event)
    {
        return $this->_catalogEventData->getEventImageUrl($event);
    }

    /**
     * Get items number to show per page
     *
     * @return int
     */
    public function getPageSize()
    {
        if ($this->hasData('limit') && is_numeric($this->getData('limit'))) {
            $pageSize = (int) $this->_getData('limit');
        }
        else {
            $pageSize = (int)Mage::getStoreConfig('catalog/enterprise_catalogevent/lister_widget_limit');
        }
        return max($pageSize, 1);
    }

    /**
     * Get items number to scroll
     *
     * @return int
     */
    public function getScrollSize()
    {
        if ($this->hasData('scroll') && is_numeric($this->getData('scroll'))) {
            $scrollSize = (int) $this->_getData('scroll');
        }
        else {
            $scrollSize = (int)Mage::getStoreConfig('catalog/enterprise_catalogevent/lister_widget_scroll');
        }
        return  min(max($scrollSize, 1), $this->getPageSize());
    }

    /**
     * Output content, if allowed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->canDisplay()) {
            return '';
        }
        return parent::_toHtml();
    }
}
