<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_PriceRules
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog Price Rule creation
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_PriceRules_Catalog_CreateTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_catalog_price_rules');
    }

    protected function tearDownAfterTestClass()
    {
        $this->loginAdminUser();
        $this->navigate('manage_catalog_price_rules');
        $this->priceRulesHelper()->deleteAllRules();
        $this->clickButton('apply_rules', false);
        $this->waitForNewPage();
        $this->assertMessagePresent('success', 'success_applied_rule');
    }

    /**
     * <p>Create a new catalog price rule</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Rule"</p>
     * <p>2. Fill in only required fields in all tabs</p>
     * <p>3. Click "Save Rule" button</p>
     * <p>Expected result:</p>
     * <p>New rule is created. Success message appears.</p>
     *
     * @return array
     * @test
     * @TestlinkId TL-MAGE-3313
     */
    public function requiredFields()
    {
        //Data
        $priceRuleData = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule');
        //Steps
        $this->priceRulesHelper()->createRule($priceRuleData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->assertMessagePresent('success', 'notification_message');
        return $priceRuleData;
    }

    /**
     * <p>Validation of empty required fields</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Rule"</p>
     * <p>2. Leave required fields empty</p>
     * <p>3. Click "Save Rule" button</p>
     * <p>Expected result: Validation message appears</p>
     *
     * @param string $emptyField
     * @param string $fieldType
     *
     * @test
     * @dataProvider withRequiredFieldsEmptyDataProvider
     * @TestlinkId TL-MAGE-3309
     * @group skip_due_to_bug
     */
    public function withRequiredFieldsEmpty($emptyField, $fieldType)
    {
        //Data
        $priceRuleData = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule', array($emptyField => '%noValue%'));
        //Steps
        $this->priceRulesHelper()->createRule($priceRuleData);
        //Verification
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withRequiredFieldsEmptyDataProvider()
    {
        return array(
            array('rule_name', 'field'),
            array('customer_groups', 'multiselect'),
            array('discount_amount', 'field'), //MAGE-5623(reproduce in 1.6.2,but is not reproducible in nightly build)
            array('sub_discount_amount', 'field')
        );
    }

    /**
     * <p>Validation of Discount Amount field</p>
     * <p>Steps</p>
     * <p>1. Click "Add New Rule"</p>
     * <p>2. Fill in "General Information" tab</p>
     * <p>3. Specify "Conditions"</p>
     * <p>4. Enter invalid data into "Discount Amount" and "Sub Discount Amount" fields</p>
     * <p>Expected result: Validation messages appears</p>
     *
     * @param string $invalidDiscountData
     *
     * @test
     * @dataProvider invalidDiscountAmountDataProvider
     * @TestlinkId TL-MAGE-3311
     */
    public function invalidDiscountAmount($invalidDiscountData)
    {
        //Data
        $priceRuleData = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule',
                                            array('sub_discount_amount' => $invalidDiscountData,
                                                 'discount_amount'      => $invalidDiscountData));
        //Steps
        $this->priceRulesHelper()->createRule($priceRuleData);
        //Verification
        $this->assertMessagePresent('validation', 'invalid_discount_amount');
        $this->assertMessagePresent('validation', 'invalid_sub_discount_amount');
        $this->assertTrue($this->verifyMessagesCount(2), $this->getParsedMessages());
    }

    public function invalidDiscountAmountDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:')),
            array($this->generate('string', 9, ':alpha:')),
            array('g3648GJTest'),
            array('-128')
        );
    }

    /**
     * <p>Create Catalog price rule with long values into required fields.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Catalog Price Rules</p>
     * <p>2. Fill form for Catalog Price Rule, but one field should be filled with long Values</p>
     * <p>3. Click "Save Rule" button</p>
     * <p>Expected result:</p>
     * <p>Rule created, confirmation message appears</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3312
     * @group skip_due_to_bug1.12
     * @group skip_due_to_bug1.12.0.1
     */
    public function longValues()
    {
        $priceRuleData = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule',
                                            array('rule_name'          => $this->generate('string', 255, ':alnum:'),
                                                 'description'         => $this->generate('string', 255, ':alnum:'),
                                                 'discount_amount'     => '99999999.9999',
                                                 'sub_discount_amount' => '99999999.9999',
                                                 'priority'            => '4294967295'));
        $ruleSearch = $this->loadDataSet('CatalogPriceRule', 'search_catalog_rule',
                                         array('filter_rule_name' => $priceRuleData['info']['rule_name']));
        $this->priceRulesHelper()->createRule($priceRuleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->priceRulesHelper()->openRule($ruleSearch);
        $this->priceRulesHelper()->verifyRuleData($priceRuleData);
    }

    /**
     * <p>Create Catalog price rule with long values into required fields.</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Promotions - Catalog Price Rules</p>
     * <p>2. Fill form for Catalog Price Rule, but one field should be filled with long Values</p>
     * <p>3. Click "Save Rule" button</p>
     * <p>Expected result:</p>
     * <p>Rule created, confirmation message appears</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3310
     * @group skip_due_to_bug1.12
     * @group skip_due_to_bug1.12.0.1
     */
    public function incorrectLengthInDiscountAmount()
    {
        $priceRuleData = $this->loadDataSet('CatalogPriceRule', 'test_catalog_rule',
                                            array('discount_amount'    => '999999999',
                                                 'sub_discount_amount' => '999999999'));
        $ruleSearch = $this->loadDataSet('CatalogPriceRule', 'search_catalog_rule',
                                         array('filter_rule_name' => $priceRuleData['info']['rule_name']));
        $this->priceRulesHelper()->createRule($priceRuleData);
        $this->assertMessagePresent('success', 'success_saved_rule');
        $this->priceRulesHelper()->openRule($ruleSearch);
        $priceRuleData['actions']['discount_amount'] = '99999999.9999';
        $priceRuleData['actions']['sub_discount_amount'] = '99999999.9999';
        $this->priceRulesHelper()->verifyRuleData($priceRuleData);
    }
}