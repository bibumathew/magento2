<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ValidationVatNumber
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ValidationVatNumber_WithTaxCalculationBasedOnBillingTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        //Data
        $storeInfo = $this->loadDataSet('VatID', 'store_information_data');
        //Filling "Store Information" data and Validation VAT Number
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($storeInfo);
        if (!$this->controlIsPresent('link', 'store_information_link')) {
            $this->clickControl('link', 'store_information_link', false);
        }
        $this->clickControl('button', 'validate_vat_number', false);
        $this->pleaseWait();
        //Verification
        $this->assertTrue($this->controlIsPresent('button', 'vat_number_is_valid'), 'VAT Number is not valid');
    }

    /**
     * @test
     * @return array
     */
    public function preconditionsForTests()
    {
        //Data
        $names = array('Valid VAT Domestic_%randomize%', 'Valid VAT IntraUnion_%randomize%', 'Invalid VAT_%randomize%');
        $processedGroupNames = array();
        //Creating three Customer Groups
        $this->loginAdminUser();
        $this->navigate('manage_customer_groups');
        foreach ($names as $groupName) {
            $customerGroup = $this->loadDataSet('CustomerGroup', 'new_customer_group',
                array('group_name' => $groupName));
            $this->customerGroupsHelper()->createCustomerGroup($customerGroup);
            //Verifying
            $this->assertMessagePresent('success', 'success_saved_customer_group');
            $processedGroupNames[] = $customerGroup['group_name'];
        }
        //Configuring "Create New Account Options" tab
        $this->navigate('system_configuration');
        $accountOptions = $this->loadDataSet('VatID', 'create_new_account_options',
            array('group_valid_vat_domestic'   => $processedGroupNames[0],
                'group_valid_vat_intraunion' => $processedGroupNames[1],
                'group_invalid_vat'          => $processedGroupNames[2],
                'tax_calculated_based_on'    => 'Billing Address'));
        $this->systemConfigurationHelper()->configure($accountOptions);

        return $processedGroupNames;
    }

    protected function tearDownAfterTestClass()
    {
        $accountOptions = $this->loadDataSet('VatID', 'create_new_account_options_disable');
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure($accountOptions);
    }

    /**
     * <p>Backend customer registration. With "Tax Calculation Based On" - Billing Address</p>
     *
     * @param array $customerData
     * @test
     *
     * @dataProvider withBillingTaxCalculationSettingTestDataProvider
     * @TestlinkId TL-MAGE-6224
     */
    public function withBillingTaxCalculationSettingTest($customerData)
    {
        //Data
        $userRegisterData = $this->loadDataSet('Customers', 'generic_customer_account');
        $addressData = $this->loadDataSet('Customers', 'generic_address', $customerData);
        $userDataParam = $userRegisterData['first_name'] . ' ' . $userRegisterData['last_name'];
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userRegisterData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer(array('email' => $userRegisterData['email']));
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        //Verifying
        $this->validationVatNumberHelper()->verifyCustomerGroup($userDataParam, $userRegisterData);
        $this->verifyForm(array('group' => 'General'), 'account_information');
    }

    public function withBillingTaxCalculationSettingTestDataProvider()
    {
        return array(
            array(array('country' => 'Germany',        'state' => 'Berlin',
                'default_shipping_address' => 'Yes')),
            array(array('country' => 'Germany',        'state' => 'Berlin',    'vat_number' => '111607872',
                'default_shipping_address' => 'Yes')),
            array(array('country' => 'Germany',        'state' => 'Berlin',    'vat_number' => 'invalid_vat',
                'default_shipping_address' => 'Yes')),
            array(array('country' => 'United Kingdom', 'state' => '%noValue%', 'vat_number' => '584451913',
                'default_shipping_address' => 'Yes' )),
        );
    }
}