<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CmsWidgets_Helper extends Mage_Selenium_TestCase
{

    /**
     * Creates widget
     *
     * @param string|array $widgetData
     */
    public function createWidget($widgetData)
    {
        if (is_string($widgetData)) {
            $widgetData = $this->loadData($widgetData);
        }
        $widgetData = $this->arrayEmptyClear($widgetData);
        $settings = (isset($widgetData['settings'])) ? $widgetData['settings'] : NULL;
        $frontProperties = (isset($widgetData['frontend_properties'])) ? $widgetData['frontend_properties'] : NULL;
        $layoutUpdates = (isset($widgetData['layout_updates'])) ? $widgetData['layout_updates'] : NULL;
        $widgetOptions = (isset($widgetData['widget_options'])) ? $widgetData['widget_options'] : NULL;

        if ($settings) {
            $this->clickButton('add_new_widget_instance');
            $this->fillSettings($settings);
        }
        if ($frontProperties) {
            $this->fillProperties($frontProperties);
        }
        if ($layoutUpdates) {
            $this->fillLayoutUpdates($layoutUpdates);
        }
        if ($widgetOptions) {
            $this->fillWidgetOptions($widgetOptions);
        }
        $this->saveForm('save');
    }

    /**
     * Fills frontend properties
     *
     * @param array $frontProperties
     */
    public function fillProperties(array $frontProperties, $validate = FALSE)
    {
        $xpath = $this->_getControlXpath('multiselect', 'assign_to_store_views');
        if ($this->isElementPresent($xpath) && $validate == FALSE) {
            if (!array_key_exists('assign_to_store_views', $frontProperties)) {
                $frontProperties['assign_to_store_views'] = 'All Store Views';
            }
        } elseif (!$this->isElementPresent($xpath) && $validate == FALSE) {
            if (array_key_exists('assign_to_store_views', $frontProperties)) {
                unset($frontProperties['assign_to_store_views']);
            }
        }
        $this->fillForm($frontProperties);
    }

    /**
     * Fills settings for creating widget
     *
     * @param string|array $settings
     */
    public function fillSettings($settings)
    {
        if (is_string($settings)) {
            $settings = $this->loadData($settings);
            $settings = $this->arrayEmptyClear($settings);
        }
        $xpath = $this->_getControlXpath('dropdown', 'type');
        $type = $this->getValue($xpath . '/option[text()="' . $settings['type'] . '"]');
        $type = str_replace('/', '-', $type);
        $this->addParameter('type', $type);
        $packageTheme = array_map('trim', (explode('/', $settings['design_package_theme'])));
        $this->addParameter('package', $packageTheme[0]);
        $this->addParameter('theme', $packageTheme[1]);
        $this->fillForm($settings);
        $this->clickButton('continue');
    }

    /**
     * Fills data for layout updates
     *
     * @param string|array $layoutData
     */
    public function fillLayoutUpdates($layoutData)
    {
        if (is_string($layoutData)) {
            $layoutData = $this->loadData($layoutData);
            $layoutData = $this->arrayEmptyClear($layoutData);
        }
        $count = 0;
        foreach ($layoutData as $key => $value) {
            $this->clickButton('add_layout_update', FALSE);
            $this->addParameter('index', $count);
            $xpath = $this->_getControlXpath('dropdown', 'select_display_on');
            $layoutName = $this->getValue($xpath . '//option[text()="' . $value['select_display_on'] . '"]');
            $this->addParameter('layout', $layoutName);
            $this->addParameter('param', "//div[@id='" . $layoutName . '_ids_' . $count++ . "']");
            $this->fillForm($value);
            $xpathOptionsAll = $this->_getControlXpath('radiobutton', 'all_categories_products_radio');
            if (array_key_exists('choose_options', $value)) {
                if (preg_match('/anchor_categories/', $layoutName)) {
                    $this->chooseLayoutOptions($value['choose_options'], 'categories');
                } else {
                    $this->chooseLayoutOptions($value['choose_options']);
                }
            } else {
                if ($this->isElementPresent($xpathOptionsAll)) {
                    $this->check($xpathOptionsAll);
                }
            }
        }
    }

    /**
     * Fills options for layout updates
     *
     * @param array $layoutOptions
     * @param string $layoutName
     */
    public function chooseLayoutOptions(array $layoutOptions, $layoutName = 'products')
    {
        $this->clickControl('radiobutton', 'specific_categories_products_radio', FALSE);
        $this->clickControl('link', 'open_chooser', FALSE);
        $this->pleaseWait();
        if ($layoutName == 'categories') {
            foreach ($layoutOptions as $key => $value) {
                $this->categoryHelper()->selectCategory($value);
            }
        } elseif ($layoutName == 'products') {
            foreach ($layoutOptions as $key => $value) {
                $this->searchAndChoose(array('filter_sku' => $value), 'layout_products_fieldset');
            }
        } else {
            return;
        }
        $this->clickControl('link', 'apply', FALSE);
    }

    /**
     * Fills "Widget Options" tab
     *
     * @param string|array $widgetOptions
     */
    public function fillWidgetOptions($widgetOptions)
    {
        if (is_string($widgetOptions)) {
            $widgetOptions = $this->loadData($widgetOptions);
            $widgetOptions = $this->arrayEmptyClear($widgetOptions);
        }
        $this->clickControl('tab', 'widgets_options', FALSE);
        $this->fillForm($widgetOptions);
        $type = explode('/', $this->getCurrentLocationUimapPage()->getMca());
        if (array_key_exists('chosen_option', $widgetOptions)) {
            $options = $widgetOptions['chosen_option'];
            switch ($type[3]) {
                case 'cms-widget_page_link':
                    $this->clickButton('select_page', FALSE);
                    $this->pleaseWait();
                    $this->searchAndOpen(array('filter_url_key' => $options['filter_url_key']), FALSE);
                    $this->checkChosenOption($options['title']);
                    break;
                case 'cms-widget_block':
                    $this->clickButton('select_block', FALSE);
                    $this->pleaseWait();
                    $this->searchAndOpen(array('filter_identifier' => $options['filter_identifier']), FALSE);
                    $this->checkChosenOption($options['title']);
                    break;
                case 'catalog-category_widget_link':
                    $this->clickButton('select_category', FALSE);
                    $this->pleaseWait();
                    $this->addParameter('param', "//div[@id='widget-chooser_content']");
                    foreach ($options as $key => $value) {
                        if (preg_match('/category_path/', $key)) {
                            $this->categoryHelper()->selectCategory($value);
                        }
                    }
                    $this->checkChosenOption($options['title']);
                    break;
                case 'catalog-product_widget_link':
                    $this->clickButton('select_product', FALSE);
                    $this->pleaseWait();
                    foreach ($options as $key => $value) {
                        if (preg_match('/category_path/', $key)) {
                            $this->addParameter('param', "//div[@id='widget-chooser_content']");
                            $this->categoryHelper()->selectCategory($value);
                            $this->waitForAjax();
                        }
                    }
                    foreach ($options as $key => $value) {
                        if (preg_match('/filter_sku/', $key)) {
                            $this->searchAndOpen(array('filter_sku' => $value), FALSE);
                        }
                    }
                    $this->checkChosenOption($options['title']);
                    break;
            }
        }
    }

    /**
     * Checks if the inserted item is correct
     *
     * @param string $option
     */
    public function checkChosenOption($option)
    {
        $this->addParameter('elementName', $option);
        $xpathOption = $this->_getControlXpath('pageelement', 'chosen_option');
        if (!$this->isElementPresent($xpathOption)) {
            $this->fail('The element ' . $option . ' was not selected');
        }
    }

    /**
     * Opens widget
     *
     * @param array $searchWidget
     */
    public function openWidget(array $searchWidget)
    {
        $this->_prepareDataForSearch($searchWidget);
        $xpathTR = $this->search($searchWidget, 'cms_widgets_grid');
        $this->assertNotEquals(NULL, $xpathTR, 'Widget is not found');
        $names = $this->shoppingCartHelper()->getColumnNamesAndNumbers('widget_grid_head', FALSE);
        if (array_key_exists('Widget Instance Title', $names)) {
            $text = $this->getText($xpathTR . '//td[' . $names['Widget Instance Title'] . ']');
            $this->addParameter('widgetName', $text);
        }
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage($this->_findCurrentPageFromUrl($this->getLocation()));
    }

    /**
     * Deletes widget
     *
     * @param array $searchWidget
     */
    public function deleteWidget(array $searchWidget)
    {
        $searchWidget = $this->arrayEmptyClear($searchWidget);
        if (!empty($searchWidget)) {
            $this->openWidget($searchWidget);
            $this->answerOnNextPrompt('OK');
            $this->clickButton('delete');
            $this->assertTrue($this->checkMessage('successfully_deleted_widget'), 'The widget has not been deleted');
        }
    }

}
