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
 */

class Community2_Mage_Store_MultiStoreEditCustomerTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    protected function tearDownAfterTestClass()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('SingleStoreMode/disable_single_store_mode');
    }

    /**
     * Create customer
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $storeViewData = $this->loadDataSet('StoreView', 'generic_store_view');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore($storeViewData, 'store_view');
        $this->assertMessagePresent('success', 'success_saved_store_view');

        return $userData;
    }

    /**
     * <p>Create Customer Page</p>
     * <p>Preconditions</p>
     * <p>Magento contain only one store view</p>
     * <p>Enable single store mode System->Configuration->General->General->Single-Store Mode</p>
     * <p>Steps</p>
     * <p>1. Login to Backend</p>
     * <p>2. Go to Customer->Manege Customer</p>
     * <p>3. Click "Add new customer" button</p>
     * <p>4. Verify fields in account information tab</p>
     * <p>Expected Result</p>
     * <p>1. Dropdowns "Associate to Website" and "Send From" are present</p>
     *
     * @param array $mode
     * @dataProvider newCustomerDataProvider
     * @test
     * @TestlinkId TL-MAGE-6231
     * @author Maksym_Iakusha
     */
    public function newCustomer($mode)
    {
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure("SingleStoreMode/$mode");
        $this->admin('manage_customers');
        $this->clickButton('add_new_customer');
        //Validation
        $this->assertTrue($this->controlIsPresent('dropdown', 'associate_to_website'),
            "Dropdown associate_to_website absent on page");
        $this->assertTrue($this->controlIsPresent('dropdown', 'send_from'), "Dropdown send_from absent on page");
    }

    public function newCustomerDataProvider()
    {
        return array(
            array ('enable_single_store_mode'),
            array ('disable_single_store_mode')
        );
    }

    /**
     * <p>Edit Customer Page</p>
     * <p>Preconditions</p>
     * <p>Magento contain only one store view</p>
     * <p>Customer is created</p>
     * <p>Single store mode (System->Configuration->General->General->Single-Store Mode) is enabled</p>
     * <p>Steps</p>
     * <p>1. Login to Backend</p>
     * <p>2. Go to Customer->Manege Customer</p>
     * <p>3. Open customer profile</p>
     * <p>4. Verify that:</p>
     * <p>Sales statistic grid not contain "Website", "Store", "Store View" columns</p>
     * <p>Account information tab contain "Associate to Website" dropdown</p>
     * <p>Table on Orders tab is contain "Bought From" column</p>
     * <p>Table on Recurring Profile is contain "Store" column</p>
     * <p>Table on Wishlist tab is contain "Added From" column</p>
     * <p>Table on Product Review tab is contain "Visible In" Column</p>
     * <p>Expected Result</p>
     * <p>1. All of the above elements are present</p>
     *
     * @param array $mode
     * @param array $userData
     *
     * @depends preconditionsForTests
     * @dataProvider newCustomerDataProvider
     * @test
     * @TestlinkId TL-MAGE-6232
     * @author Maksym_Iakusha
     */
    public function editCustomer($mode, $userData)
    {
        //Data
        $param = $userData['first_name'] . ' ' . $userData['last_name'];
        $this->addParameter('customer_first_last_name', $param);
        //Preconditions
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure("SingleStoreMode/$mode");
        $this->navigate('manage_customers');
        $this->customerHelper()->openCustomer(array('email' => $userData['email']));
        $columnsName = $this->shoppingCartHelper()->getColumnNamesAndNumbers('sales_statistics_head');
        $this->assertTrue((isset($columnsName['website']) && isset($columnsName['store'])
                           && isset($columnsName['store_view'])),
            "Sales Statistics table not contain unnecessary column");
        $this->openTab('account_information');
        $this->assertTrue($this->controlIsPresent('dropdown', 'associate_to_website'),
            "Dropdown associate_to_website absent on page");
        $this->openTab('orders');
        $this->assertTrue($this->controlIsPresent('dropdown', 'filter_bought_from'),
            "Table not contain 'bought_from' column");
        $this->openTab('recuring_profiles');
        $this->assertTrue($this->controlIsPresent('dropdown', 'filter_store'), "Table not contain 'store' column");
        $this->openTab('wishlist');
        $this->assertTrue($this->controlIsPresent('dropdown', 'filter_added_from'),
            "Table not contain 'added_from' column");
        $this->openTab('product_review');
        $this->assertTrue($this->controlIsPresent('dropdown', 'filter_visible_in'),
            "Table not contain 'visible_in' column");
    }
}
