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
class Tax_Helper extends Mage_Selenium_TestCase
{

    /**
     * Create new Tax rate
     *
     * @param array|string $taxRateData
     */
    public function createTaxRate(array $taxRateData)
    {
        if (is_string($taxRateData)) {
            $taxRateData = $this->loadData($taxRateData);
        }
        $taxRateData = $this->arrayEmptyClear($taxRateData);
        $taxTitles = (isset($taxRateData['tax_titles'])) ? $taxRateData['tax_titles'] : NULL;
        $this->clickButton('add_new_tax_rate');
        //$this->fillForm($taxRateData, 'tax_rate_info');
        $this->fillForm($taxRateData);
        $xpath = $this->_getControlXpath('fieldset', 'tax_titles');
        if ($taxTitles && $this->isElementPresent($xpath)) {
            foreach ($taxTitles as $key => $value) {
                $this->addParameter('storeNumber', $this->findTaxTitleByName($key));
                $this->fillForm(array('tax_title' => $value));
            }
        }
        $this->saveForm('save_rate');
    }

    /**
     * Create new Tax rule
     *
     * @param array|string $taxRuleData
     */
    public function createTaxRule(array $taxRuleData)
    {
        if (is_string($taxRuleData)) {
            $taxRuleData = $this->loadData($taxRuleData);
        }
        $taxRuleData = $this->arrayEmptyClear($taxRuleData);
        $this->clickButton('add_new_tax_rule');
        //$this->fillForm($taxRuleData, 'tax_rule_info');
        $this->fillForm($taxRuleData);
        $this->saveForm('save_rule');
    }

    /**
     * Create Customer Tax class
     *
     * @param array|string $customerTaxClassData
     */
    public function createCustomerTaxClass($customerTaxClassData)
    {
        if (is_string($customerTaxClassData)) {
            $customerTaxClassData = $this->loadData($customerTaxClassData);
        }
        $this->clickButton('add_new');
        //$this->fillForm($customerTaxClassData, 'create_customer_tax_class');
        $this->fillForm($customerTaxClassData);
        $this->saveForm('save_class');
    }

    /**
     * Create Product Tax class
     *
     * @param array|string $productTaxClassData
     */
    public function createProductTaxClass($productTaxClassData)
    {
        if (is_string($productTaxClassData)) {
            $productTaxClassData = $this->loadData($productTaxClassData);
        }
        $this->clickButton('add_new');
        $this->fillForm($productTaxClassData, 'create_product_tax_class');
        $this->saveForm('save_class');
    }

    /**
     * Search and Open Product rule
     *
     * @param string $taxTitleData
     * @return int
     */
    public function findTaxTitleByName($taxTitleData)
    {
        $taxTitleXpath = $this->_getControlXpath('pageelement', 'tax_title_header');
        $taxTitleQty = $this->getXpathCount($taxTitleXpath);
        for ($i = 1; $i <= $taxTitleQty; $i++) {
            $text = $this->getText($taxTitleXpath . "[$i]");
            if ($text == $taxTitleData) {
                return $i;
            }
        }
        return 0;
    }

    /**
     * Opens (Product\Customer)Tax Class\Rate\Rule
     *
     * @param array $taxClassSearch Data for search
     * @param string $type search type (customer_tax_class|product_tax_class|tax_rates|tax_rules)
     */
    public function openTaxItem(array $taxClassSearch,$type)
    {
        $taxClassSearch = $this->arrayEmptyClear($taxClassSearch);
        $gridName = 'manage_' . $type;
        $xpathTR = $this->search($taxClassSearch,$gridName);
        $this->assertNotEquals(null, $xpathTR, 'Search item is not found');
        $elementTitle = $this->getText($xpathTR . '//td[1]');
        $this->addParameter('elementTitle', $elementTitle);
        $this->addParameter('id', $this->defineIdFromTitle($xpathTR));
        $this->click($xpathTR);
        $this->waitForPageToLoad($this->_browserTimeoutPeriod);
        $this->validatePage($this->_findCurrentPageFromUrl($this->getLocation()));
    }

}
