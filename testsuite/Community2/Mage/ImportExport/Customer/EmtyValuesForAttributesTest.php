<?php
/**
 * Created by JetBrains PhpStorm.
 * User: iglazunova
 * Date: 6/19/12
 * Time: 12:51 PM
 * To change this template use File | Settings | File Templates.
 */
class Community2_Mage_ImportExport_Customer extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Log in to Backend.</p>
     * <p>Navigate to System -> Export/p>
     */
    protected function assertPreConditions()
    {
        //logged in once for all tests
        $this->loginAdminUser();
        //Step 1
        $this->navigate('import');
    }
    /**
     * <p>Empty values for existing attributes in csv for Customers Main File</p>
     * <p>Preconditions:</p>
     * <p>1. Customer is created. Middle name attribute has some value</p>
     * <p>2. CSV file prepared that contains existing customer info where middle name attribute value is empty</p>
     * <p>Steps</p>
     * <p>1. In System -> Import/ Export -> Import in drop-down "Entity Type" select "Customers"</p>
     * <p>2. Select "Append Complex Data" in selector "Import Behavior"</p>
     * <p>3. Select "Magento 2.0 format"</p>
     * <p>4. Select "Customers Main File"</p>
     * <p>5. Choose file from precondition</p>
     * <p>6. Press "Check Data"</p>
     * <p>7. Press "Import" button</p>
     * <p>8. Open Customers-> Manage Customers</p>
     * <p>9. Open customer from precondition</p>
     * <p>Expected: Verify that customer middle name hasn't been changed or removed</p>
     *
     * @test
     * @dataProvider importData
     * @TestlinkId TL-MAGE-5639
     */
    public function emptyValuesForExistingAttributesMainFile($data)
    {
        //Precondition: create customer
        $this->navigate('manage_customers');
        $userData = $this->loadDataSet('ImportExport.yml', 'generic_customer_account');
        $this->customerHelper()->createCustomer($userData);
        $this->assertMessagePresent('success', 'success_saved_customer');

        $this->addParameter('customer_first_last_name', $userData['first_name'] . ' ' . $userData['last_name']);
        $this->customerHelper()->openCustomer(array('email' => $userData['email']));
        $this->customerHelper()->fillForm(array('middle_name' => 'Test Middle Name'), 'account_information');
        $this->saveForm('save_customer');

        $data[0]['email'] = $userData['email'];
        $data[0]['firstname'] = $userData['first_name'];
        $data[0]['lastname'] = $userData['last_name'];
        $data[0]['password'] = $userData['password'];
        //Step 1
        $this->admin('import');
        $this->fillDropdown('entity_type', 'Customers');
        $this->waitForElementVisible($this->_getControlXpath('dropdown', 'import_behavior'));
        //Step 2
        $this->fillDropdown('import_behavior', 'Append Complex Data');
        $this->waitForElementVisible($this->_getControlXpath('dropdown', 'import_file_version'));
        //Step 3
        $this->fillDropdown('import_file_version', 'Magento 2.0 format');
        $this->waitForElementVisible($this->_getControlXpath('dropdown', 'import_customer_entity'));
        $this->waitForElementVisible($this->_getControlXpath('field', 'file_to_import'));
        //Step 4
        $this->fillDropdown('import_customer_entity', 'Customers Main File');
        //Step 5, 6, 7
        $report = $this->importExportHelper()->import($data);
        //Check import
        $this->assertArrayHasKey('import', $report, 'Import has been finished with issues:');
        $this->assertArrayHasKey('success', $report['import'], 'Import has been finished with issues:');
        //Step 8
        $this->navigate('manage_customers');
        //Step 9
        $this->addParameter('customer_first_last_name', $userData['first_name'] . ' ' . $userData['last_name']);
        $this->customerHelper()->openCustomer(array('email' => $userData['email']));
        //Verify customer account
        $this->assertTrue($this->verifyForm(array('middle_name' => 'Test Middle Name'), 'account_information'),
            'Existent customer has been updated');
    }
    public function importData()
    {
        return array(
            array(array(array(
                '_website' => 'base',
                '_store' => 'default',
                'confirmation' => '',
                'created_at' => '19.06.2012 18:00',
                'created_in' => 'Admin',
                'default_billing' => '',
                'default_shipping' => '',
                'disable_auto_group_change' => '0',
                'dob' => '',
                'gender' => '',
                'group_id' => '1',
                'middlename' => '',
                'prefix' => '',
                'reward_update_notification' => '1',
                'reward_warning_notification' => '1',
                'rp_token' => '',
                'rp_token_created_at' => '',
                'password_hash' => '48927b9ee38afb672504488a45c0719140769c24c10e5ba34d203ce5a9c15b27:2y',
                'store_id' => '0',
                'website_id' => '0',
                'suffix' => '',
                'taxvat' => '',
            )))
        );
    }
}