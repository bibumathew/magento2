<?php
/**
 * Magento
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Store_EnableSingleStoreMode
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 *
 */

class Enterprise2_Mage_Store_EnableSingleStoreMode_PromotionsTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Precondition for test:</p>
     * <p>1. Login to backend.</p>
     * <p>2. Navigate to System -> Manage Store.</p>
     * <p>3. Verify that one store-view is created.<p>
     * <p>4. Go to System - Configuration - General and enable Single-Store Mode.</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->admin('manage_stores');
        $this->storeHelper()->deleteStoreViewsExceptSpecified(array('Default Store View'));
        $config = $this->loadDataSet('SingleStoreMode', 'enable_single_store_mode');
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
    }

    protected function tearDownAfterTest()
    {
        $config = $this->loadDataSet('SingleStoreMode', 'disable_single_store_mode');
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($config);
    }

    /**
     * <p>Catalog Price Rules page does not contain websites columns and multiselects if Single Store Mode is enabled.</p>
     * <p>Steps:</p>
     * <p>1.Go to Promotions - Catalog Price Rules
     * <p>2.Check for Website column on the Grid.
     * <p>Expected result: </p>
     * <p>Website column is not displayed.</p>
     * <p>3.Click on the Add New Rule button.</p>
     * <p>4.Check for Websites multiselect</p>
     * <p>Expected result: </p>
     * <p>Websites multiselect is not displayed.</p>
     *  <p>5.Click on the Related Banners tab and check "Visible In" column</p>
     * <p>Expected result: </p>
     * <p>"Visible In" column is not displayed</p>
     *
     * @test
     * @TestlinkId TL-MAGE-6266
     * @author Tatyana_Gonchar
     */
    public function verificationCatalogPriceRule()
    {
        $this->admin('manage_catalog_price_rules');
        $this->assertFalse($this->controlIsPresent('dropdown', 'filter_website'),
            'There is "Website" column on the page');
        $this->assertTrue($this->controlIsPresent('button', 'add_new_rule'),
            'There is no "Add New Rule" button on the page');
        $this->clickButton('add_new_rule');
        $this->assertFalse($this->controlIsPresent('multiselect', 'websites'),
            'There is "Store View" selector on the page');
        $this->assertTrue($this->controlIsPresent('tab', 'rule_related_banners'),
            'There is no Relates Banners tab on the page');
        $this->openTab('rule_related_banners');
        $this->assertFalse($this->controlIsPresent('dropdown', 'filter_banner_visible_in'),
            'There is "Visible In" column on the page');
    }

    /**
     * <p>Shopping Cart Price Rules page does not contain websites columns and multiselects</p>
     * <p>Steps:</p>
     * <p>1.Go to Promotions - Shopping Cart Price Rules</p>
     * <p>2.Check for Website column on the Grid.</p>
     * <p>Expected result: </p>
     * <p>Website column is not displayed.</p>
     * <p>3.Click on the Add New Rule button.</p>
     * <p>4.Check for Websites multiselect</p>
     * <p>Expected result: </p>
     * <p>Websites multiselect is not displayed.</p>
     * <p>5.Click on the Related Banners tab and check "Visible In" column</p>
     * <p>Expected result: </p>
     * <p>"Visible In" column is not displayed</p>
     *
     * @test
     * @TestlinkId TL-MAGE-6267
     * @author Tatyana_Gonchar
     */
    public function verificationShoppingCartPriceRule()
    {
        $this->admin('manage_shopping_cart_price_rules');
        $this->assertFalse($this->controlIsPresent('dropdown', 'filter_website'),
            'There is "Website" column on the page');
        $this->assertTrue($this->controlIsPresent('button', 'add_new_rule'),
            'There is "Add New Rule" button on the page');
        $this->clickButton('add_new_rule');
        $this->assertFalse($this->controlIsPresent('multiselect', 'websites'),
            'There is "Store View" selector on the page');
        $this->assertTrue($this->controlIsPresent('tab', 'rule_related_banners'),
            'There is no Relates Banners tab on the page');
        $this->openTab('rule_related_banners');
        $this->assertFalse($this->controlIsPresent('dropdown', 'filter_banner_visible_in'),
            'There is "Visible In" column on the page');
    }
}