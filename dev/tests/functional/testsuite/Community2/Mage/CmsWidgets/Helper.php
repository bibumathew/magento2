<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_CmsWidgets
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Community2_Mage_CmsWidgets_Helper extends Core_Mage_CmsWidgets_Helper
{
    /**
     * Fills settings for creating widget
     *
     * @param array $settings
     */
    public function fillWidgetSettings(array $settings)
    {
        if ($settings) {
            $this->fillDropdown('type', $settings['type']);
            $type = $this->getControlAttribute('dropdown', 'type', 'selectedValue');
            $this->addParameter('type', $type);

            list($package, $theme) = array_map('trim', (explode('/', $settings['design_package_theme'])));
            $this->addParameter('dropdownXpath', $this->_getControlXpath('dropdown', 'design_package_theme'));
            $this->addParameter('optionGroup', ucfirst(strtolower($package)));
            $this->addParameter('optionText', ucfirst(strtolower($theme)));
            $value = $this->getControlAttribute('pageelement', 'dropdown_group_option_text', 'value');
            $this->addParameter('package_theme', str_replace('/', '-', $value));
            $this->fillDropdown('design_package_theme', $value);
        }
        $waitCondition = array($this->_getMessageXpath('general_validation'),
                               $this->_getControlXpath('fieldset', 'layout_updates_header',
                                   $this->getUimapPage('admin', 'add_widget_options')));
        $this->clickButton('continue', false);
        $this->waitForElement($waitCondition);
        $this->validatePage('add_widget_options');
    }
}