<?php
/**
 * Magento
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Store
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 *
 */

class Community2_Mage_Store_MultiStoreModeSystemConfigurationTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->admin('manage_stores');
        $this->addParameter('tableHeadXpath', $this->_getControlXpath('pageelement', 'stores_table'));
        if ($this->getControlCount('pageelement', 'table_line') == 1) {
            $this->storeHelper()->createStore('StoreView/generic_store_view', 'store_view');
        }
    }

    /**
     * <p>Scope Selector is enabled.</p>
     * <p>Steps:</p>
     * <p>1.Login to backend.</p>
     * <p>2.Go to System - Manage Stores</p>
     * <p>3.If there only one Store View - create one more (for enabling Multi Store Mode functionality).</p>
     * <p>4.Go to System - Configuration.</p>
     * <p>Expected result: </p>
     * <p>Scope Selector is displayed.</p>
     *
     * @test
     * @TestlinkId TL-MAGE-6186
     */
    function verificationScopeSelector()
    {
        $this->admin('system_configuration');
        $this->assertTrue($this->controlIsPresent('fieldset', 'current_configuration_scope'),
            "There is no Scope Selector");
    }

    /**
     * <p>"Export Table Rates" functionality is enabled only on Website scope.</p>
     * <p>Steps:</p>
     * <p>1.Login to backend.</p>
     * <p>2.Go to System - Manage Stores</p>
     * <p>3.If there only one Store View - create one more (for enabling Multi Store Mode functionality.</p>
     * <p>4.Go to System - Configuration and select "Main Website" on the scope switcher</p>
     * <p>5.Go to Sales - Shipping Methods.</p>
     * <p>6.Check for "Table Rates" fieldset.</p>
     * <p>Expected result: </p>
     * <p>"Export CSV" button is displayed.</p>
     * <p>7.Change the scope to "Default Store View" or "Default Config".</p>
     * <p>Expected result: </p>
     * <p>"Export CSV" button is not displayed.</p>
     *
     * @dataProvider diffConfigScopeDataProvider
     *
     * @test
     * @TestlinkId TL-MAGE-6187
     */
    function verificationTableRatesExport($diffScope)
    {
        $this->admin('system_configuration');
        $this->selectStoreScope('dropdown', 'current_configuration_scope', $diffScope);
        $this->systemConfigurationHelper()->openConfigurationTab('sales_shipping_methods');
        $button = 'table_rates_export_csv';
        if ($diffScope == 'Main Website') {
            if (!$this->buttonIsPresent($button)) {
                $this->addVerificationMessage("Button $button is not present on the page on $diffScope");
            }
        } else {
            if ($this->buttonIsPresent($button)) {
                $this->addVerificationMessage("Button $button is present on the page on $diffScope");
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    public function diffConfigScopeDataProvider()
    {
        return array(
            array('Main Website'),
            array('Default Store View'),
            array('Default Config')
        );
    }

    /**
     * <p>"Account Sharing Options" functionality is enabled only on Default Config scope.</p>
     * <p>Steps:</p>
     * <p>1.Login to backend.</p>
     * <p>2.Go to System - Manage Stores</p>
     * <p>3.If there only one Store View - create one more (for enabling Multi Store Mode functionality.</p>
     * <p>4.Go to System - Configuration and select "Default Config" on the scope switcher</p>
     * <p>5.Go to Customer - Customer Configuration.</p>
     * <p>6.Check for "Account Sharing Options" fieldset.</p>
     * <p>Expected result: </p>
     * <p>"Account Sharing Options" fieldset is displayed.</p>
     * <p>7.Change the scope to "Main Website" or "Default Store View".</p>
     * <p>Expected result: </p>
     * <p>"Account Sharing Options" fieldset is not displayed.</p>
     *
     * @dataProvider diffConfigScopeDataProvider
     *
     * @test
     * @TestlinkId TL-MAGE-6188
     */
    function verificationAccountSharingOptions($diffScope)
    {
        $this->admin('system_configuration');
        $this->selectStoreScope('dropdown', 'current_configuration_scope', $diffScope);
        $this->systemConfigurationHelper()->openConfigurationTab('customers_customer_configuration');
        $fieldset = 'account_sharing_options';
        if ($diffScope == 'Default Config') {
            if (!$this->controlIsPresent('fieldset', $fieldset)) {
                $this->addVerificationMessage("Fieldset $fieldset is not present on the page on $diffScope");
            }
        } else {
            if ($this->controlIsPresent('fieldset', $fieldset)) {
                $this->addVerificationMessage("Fieldset $fieldset is present on the page on $diffScope");
            }
        }
        $this->assertEmptyVerificationErrors();
    }


    /**
     * <p>"Price" fieldset is displayed only on Default Config scope.</p>
     * <p>Steps:</p>
     * <p>1.Login to backend.</p>
     * <p>2.Go to System - Manage Stores</p>
     * <p>3.If there only one Store View - create one more (for enabling Multi Store Mode functionality.</p>
     * <p>4.Go to System - Configuration and select "Default Config" on the scope switcher</p>
     * <p>5.Go to Catalog - Catalog.</p>
     * <p>6.Check for "Price" fieldset.</p>
     * <p>Expected result: </p>
     * <p>Price" fieldset is displayed.</p>
     * <p>7.Change the scope to "Main Website" or "Default Store View".</p>
     * <p>Expected result: </p>
     * <p>"Price" fieldset is not displayed.</p>
     *
     * @dataProvider diffConfigScopeDataProvider
     *
     * @test
     * @TestlinkId TL-MAGE-6189
     */
    function verificationCatalogPrice($diffScope)
    {
        $this->admin('system_configuration');
        $this->selectStoreScope('dropdown', 'current_configuration_scope', $diffScope);
        $this->systemConfigurationHelper()->openConfigurationTab('catalog_catalog');
        $fieldset = 'price';
        if ($diffScope == 'Default Config') {
            if (!$this->controlIsPresent('fieldset', $fieldset)) {
                $this->addVerificationMessage("Fieldset $fieldset is not present on the page on $diffScope");
            }
        } else {
            if ($this->controlIsPresent('fieldset', $fieldset)) {
                $this->addVerificationMessage("Fieldset $fieldset is present on the page on $diffScope");
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * <p>Debug" fieldset is displayed only on Main Website and Default Store View scopes.</p>
     * <p>Steps:</p>
     * <p>1.Login to backend.</p>
     * <p>2.Go to System - Manage Stores</p>
     * <p>3.If there only one Store View - create one more (for enabling Multi Store Mode functionality.</p>
     * <p>4.Go to System - Configuration and select "Main Website" on the scope switcher</p>
     * <p>5.Go to Advanced - Developer.</p>
     * <p>6.Check for "Debug" fieldset.</p>
     * <p>Expected result: </p>
     * <p>"Debug" fieldset is displayed.</p>
     * <p>7.Change the scope to "Default Store View" </p>
     * <p>Expected result: </p>
     * <p>"Debug" fieldset is displayed.</p>
     * <p>8.Change the scope to "Default Config" </p>
     * <p>Expected result: </p>
     * <p>"Debug" fieldset is not displayed.</p>
     *
     * @dataProvider diffConfigScopeDataProvider
     *
     * @test
     * @TestlinkId TL-MAGE-6190
     */
    function verificationDebugOptions($diffScope)
    {
        $this->admin('system_configuration');
        $this->selectStoreScope('dropdown', 'current_configuration_scope', $diffScope);
        $this->systemConfigurationHelper()->openConfigurationTab('advanced_developer');
        $fieldset = 'debug';
        if (($diffScope == 'Main Website') || ($diffScope == 'Default Store View')) {
            if (!$this->controlIsPresent('fieldset', $fieldset)) {
                $this->addVerificationMessage("Fieldset $fieldset is not present on the page on $diffScope");
            }
        } else {
            if ($this->controlIsPresent('fieldset', $fieldset)) {
                $this->addVerificationMessage("Fieldset $fieldset is present on the page on $diffScope");
            }
        }
        $this->assertEmptyVerificationErrors();
    }

    /**
     * <p>Hints for fields are enabled.</p>
     * <p>Steps:</p>
     * <p>1.Login to backend.</p>
     * <p>2.Go to System - Manage Stores</p>
     * <p>3.If there only one Store View - create one more (for enabling Multi Store Mode functionality.</p>
     * <p>4.Go to System - Configuration</p>
     * <p>5.Open required tab and fieldset and check hints  </p>
     * <p>Expected result: </p>
     * <p>Hints are displayed</p>
     *
     * @test
     * @TestlinkId TL-MAGE-6201
     */
    function verificationHints()
    {
        $this->admin('system_configuration');
        $locatorParts = array($this->_getControlXpath('pageelement', 'store_view_hint'),
                              $this->_getControlXpath('pageelement', 'global_view_hint'),
                              $this->_getControlXpath('pageelement', 'website_view_hint'));
        $needTypes = array(self::FIELD_TYPE_MULTISELECT, self::FIELD_TYPE_DROPDOWN, self::FIELD_TYPE_INPUT);

        $tabs = $this->getCurrentUimapPage()->getMainForm()->getAllTabs();
        /**
         * @var Mage_Selenium_Uimap_Tab $tabUimap
         */
        foreach ($tabs as $tabName => $tabUimap) {
            $this->openTab($tabName);
            $uimapFields = $tabUimap->getTabElements($this->getParamsHelper());
            foreach ($needTypes as $fieldType) {
                if (!isset($uimapFields[$fieldType])) {
                    continue;
                }
                foreach ($uimapFields[$fieldType] as $fieldName => $fieldLocator) {
                    foreach ($locatorParts as $part) {
                        if (!$this->elementIsPresent($fieldLocator . $part)) {
                            $this->addVerificationMessage(
                                "Element '" . $fieldName . "' is not on the page. Locator: " . $fieldLocator . $part);
                        }
                    }
                }
            }
        }
        $this->assertEmptyVerificationErrors();
    }
}