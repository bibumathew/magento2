<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Store grid column filter
 */
class Magento_Backend_Block_Widget_Grid_Column_Filter_Store
    extends Magento_Backend_Block_Widget_Grid_Column_Filter_Abstract
{
    /**
     * @var Magento_Core_Model_System_Store
     */
    protected $_systemStore;

    /**
     * @param Magento_Backend_Block_Context $context
     * @param Magento_Core_Model_Resource_Helper_Mysql4 $resourceHelper
     * @param Magento_Core_Model_System_Store $systemStore
     * @param array $data
     */
    public function __construct(
        Magento_Backend_Block_Context $context,
        Magento_Core_Model_Resource_Helper_Mysql4 $resourceHelper,
        Magento_Core_Model_System_Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * Render HTML of the element
     *
     * @return string
     */
    public function getHtml()
    {
        $websiteCollection = $this->_systemStore->getWebsiteCollection();
        $groupCollection = $this->_systemStore->getGroupCollection();
        $storeCollection = $this->_systemStore->getStoreCollection();

        $allShow = $this->getColumn()->getStoreAll();

        $html  = '<select name="' . $this->escapeHtml($this->_getHtmlName()) . '" '
               . $this->getColumn()->getValidateClass()
               . $this->getUiId('filter', $this->_getHtmlName())
               . '>';
        $value = $this->getColumn()->getValue();
        if ($allShow) {
            $html .= '<option value="0"' . ($value == 0 ? ' selected="selected"' : '') . '>'
                  . __('All Store Views') . '</option>';
        } else {
            $html .= '<option value=""' . (!$value ? ' selected="selected"' : '') . '></option>';
        }
        foreach ($websiteCollection as $website) {
            $websiteShow = false;
            foreach ($groupCollection as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeCollection as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $html .= '<optgroup label="' . $this->escapeHtml($website->getName()) . '"></optgroup>';
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;'
                              . $this->escapeHtml($group->getName()) . '">';
                    }
                    $value = $this->getValue();
                    $selected = $value == $store->getId() ? ' selected="selected"' : '';
                    $html .= '<option value="' . $store->getId() . '"' . $selected . '>&nbsp;&nbsp;&nbsp;&nbsp;'
                          . $this->escapeHtml($store->getName()) . '</option>';
                }
                if ($groupShow) {
                    $html .= '</optgroup>';
                }
            }
        }
        if ($this->getColumn()->getDisplayDeleted()) {
            $selected = ($this->getValue() == '_deleted_') ? ' selected' : '';
            $html.= '<option value="_deleted_"'.$selected.'>'.__('[ deleted ]').'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Form condition from element's value
     *
     * @return array|null
     */
    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }
        if ($this->getValue() == '_deleted_') {
            return array('null' => true);
        } else {
            return array('eq' => $this->getValue());
        }
    }

}
