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
class Tags_Helper extends Mage_Selenium_TestCase
{

    /**
     * <p>Create Tag</p>
     *
     * @param string $tagName
     */
    public function frontendAddTag($tagName)
    {
        if (is_array($tagName) && array_key_exists('new_tag_names', $tagName)) {
            $tagName = $tagName['new_tag_names'];
        } else {
            $this->fail('Array key is absent in array');
        }
        $tagQty = count(explode(' ', $tagName));
        $this->addParameter('tagQty', $tagQty);
        $tagXpath = $this->_getControlXpath('field', 'input_new_tags');
        if (!$this->isElementPresent($tagXpath)) {
            $this->fail('Element is absent on the page');
        }
        $this->type($tagXpath, $tagName);
        $this->clickButton('add_tags');
    }

    /**
     * Verification tags on frontend
     *
     * @param array $verificationData
     */
    public function frontendTagVerification($verificationData)
    {
        if (is_array($verificationData) && array_key_exists('new_tag_names', $verificationData)) {
            $tagName = $verificationData['new_tag_names'];
        } else {
            $this->fail('Array key is absent in array');
        }
        if (array_key_exists('product_name', $verificationData)) {
            $productName = $verificationData['product_name'];
        } else {
            $this->fail('Array key is absent in array');
        }
        $this->navigate('customer_account');
        $this->addParameter('productName', $productName);


        $tagNameArray = explode(' ', $tagName);
        foreach ($tagNameArray as $value) {
            $this->addParameter('tagName', $value);
            $xpath = $this->_getControlXpath('link', 'product_info');
            $this->assertTrue($this->isElementPresent($xpath), "Cannot find tag with name: $value");
        }
        $this->navigate('my_account_my_tags');
        foreach ($tagNameArray as $value) {
            $this->addParameter('tagName', $value);
            $xpath = $this->_getControlXpath('link', 'tag_name');
            $this->assertTrue($this->isElementPresent($xpath), "Cannot find tag with name: $value");

            $this->clickControl('link', 'tag_name');
            $xpath = $this->_getControlXpath('link', 'product_name');
            $this->assertTrue($this->isElementPresent($xpath), "Cannot find tag with name: $value");
            $xpath = $this->_getControlXpath('pageelement', 'tag_name_box');
            $this->assertTrue($this->isElementPresent($xpath), "Cannot find tag with name: $value");
            $this->clickControl('link', 'back_to_tags_list');
        }
    }

    /**
     * Select store view on Create/Edit tag page
     *
     * @param string $store_view Name of the store
     */
    protected function selectStoreView($store_view)
    {
        if (!$store_view) {
            return true;
        }
        $xpath = $this->_getControlXpath('dropdown', 'switch_store');
        $toSelect = $xpath . "//option[contains(.,'" . $store_view . "')]";
        $isSelected = $toSelect . '[@selected]';
        if (!$this->isElementPresent($isSelected)) {
            $storeId = $this->getAttribute($toSelect . '/@value');
            $this->addParameter('storeId', $storeId);
            $this->fillForm(array('switch_store' => $store_view));
            $this->waitForPageToLoad();
        }
    }

    /**
     * Adds a new tag in backend
     *
     * @param string|array $tagData
     */
    public function addTag($tagData)
    {
        if (is_string($tagData))
            $tagData = $this->loadData($tagData);
        $tagData = $this->arrayEmptyClear($tagData);
        $this->clickButton('add_new_tag');
        // Select store view if available
        if (array_key_exists('switch_store', $tagData)) {
            if ($this->controlIsPresent('dropdown', 'switch_store')) {
                $store_view = (isset($tagData['switch_store'])) ? $tagData['switch_store'] : NULL;
                $this->selectStoreView($store_view);
            } else {
                unset($tagData['switch_store']);
            }
        }
        // Fill general options
        $this->fillForm($tagData, 'general_info');
        $this->addParameter('tagName', $tagData['tag_name']);
        $this->clickButton('save_and_continue_edit');
        //Fill additional options
        if (!$this->controlIsPresent('field', 'prod_tag_admin_name')) {
            $this->clickControl('link', 'prod_tag_admin_expand', false);
            $this->waitForAjax();
        }
        $prod_tag_admin = (isset($tagData['products_tagged_by_admins'])) ? $tagData['products_tagged_by_admins'] : null;
        if ($prod_tag_admin) {
            $this->searchAndChoose($prod_tag_admin, 'products_tagged_by_admins');
        };
        $this->clickButton('save_tag');
    }

    /**
     * Opens a tag in backend
     *
     * @param string|array $tagData
     */
    public function openTag($tagData)
    {
        if (is_string($tagData))
            $tagData = $this->loadData($tagData);
        $tagData = $this->arrayEmptyClear($tagData);
        // TODO: Open
//        $this->searchAndOpen($tagData);
    }

}
