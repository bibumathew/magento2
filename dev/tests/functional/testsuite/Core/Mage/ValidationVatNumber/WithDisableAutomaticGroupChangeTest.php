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
class Core_Mage_ValidationVatNumber_WithDisableAutomaticGroupChangeTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        //Steps
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('VatID/store_information_data');
        $this->systemConfigurationHelper()->expandFieldSet('store_information');
        $this->clickControl('button', 'validate_vat_number', false);
        $this->pleaseWait();
        //Verifying
        $this->assertTrue($this->controlIsPresent('button', 'vat_number_is_valid'), 'VAT Number is not valid');
    }

    protected function tearDownAfterTestClass()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('VatID/create_new_account_options_disable');
    }

    /**
     * <p>Backend customer registration. With checkbox "Disable Automatic Group Change Based on VAT ID"</p>
     *
     * @param array $customerData
     * @test
     *
     * @dataProvider creatingCustomerWithDisableAutomaticGroupDataProvider
     * @TestlinkId TL-MAGE-6261
     */
    public function creatingCustomerWithDisableAutomaticGroup($customerData)
    {
        //Data
        $userRegisterData = $this->loadDataSet('Customers', 'generic_customer_account',
            array('disable_automatic_group_change' => 'Yes'));
        $addressData = $this->loadDataSet('Customers', 'generic_address', $customerData);
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userRegisterData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->customerHelper()->openCustomer(array('email' => $userRegisterData['email']));
        $this->customerHelper()->addAddress($addressData);
        $this->saveForm('save_customer');
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Verifying
        $this->customerHelper()->openCustomer(array('email' => $userRegisterData['email']));
        $this->openTab('account_information');
        $this->verifyForm(array('group' => 'General'), 'account_information');
    }

    public function creatingCustomerWithDisableAutomaticGroupDataProvider()
    {
        return array(
            array(array('country' => 'Germany',        'state' => 'Berlin')),
            array(array('country' => 'Germany',        'state' => 'Berlin',    'billing_vat_number' => '111607872')),
            array(array('country' => 'Germany',        'state' => 'Berlin',    'billing_vat_number' => '111111111')),
            array(array('country' => 'United Kingdom', 'state' => '%noValue%', 'billing_vat_number' => '584451913')),
        );
    }
}