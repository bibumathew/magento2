<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Cms
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Cms Static Block Widget
 *
 * @category   Mage
 * @package    Mage_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Cms_Block_Widget_Block extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    /**
     * Storage for used widgets
     *
     * @var array
     */
    static protected $_widgetUsageMap = array();

    /**
     * Prepare block text and determine whether block output enabled or not
     * Prevent blocks recursion if needed
     *
     * @return Mage_Cms_Block_Widget_Block
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $blockId = $this->getData('block_id');
        $blockHash = get_class($this) . $blockId;

        if (isset(self::$_widgetUsageMap[$blockHash])) {
            return $this;
        }
        self::$_widgetUsageMap[$blockHash] = true;

        if ($blockId) {
            $storeId = Mage::app()->getStore()->getId();
            $block = Mage::getModel('Mage_Cms_Model_Block')
                ->setStoreId($storeId)
                ->load($blockId);
            if ($block->getIsActive()) {
                /* @var $helper Mage_Cms_Helper_Data */
                $helper = Mage::helper('Mage_Cms_Helper_Data');
                $processor = $helper->getBlockTemplateProcessor();
                $this->setText($processor->setStoreId($storeId)->filter($block->getContent()));
            }
        }

        unset(self::$_widgetUsageMap[$blockHash]);
        return $this;
    }
}
