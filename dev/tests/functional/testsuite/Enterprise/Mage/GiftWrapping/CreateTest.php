<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_GiftWrapping
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Creation of gift wrapping
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Enterprise_Mage_GiftWrapping_CreateTest extends Mage_Selenium_TestCase
{
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_gift_wrapping');
    }

    /**
     * <p>Test Case TL-MAGE-836: Adding and configuring new Gift Wrapping</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Gift Wrapping" page;</p>
     * <p>2. Click button "Add Gift Wrapping";</p>
     * <p>3. Fill all required fields with correct data;</p>
     * <p>4. Press button "Save and Continue";</p>
     * <p>5. Save gift wrapping</p>
     * <p>Expected Results:</p>
     * <p>1. Gift wrapping is created;</p>
     *
     * @return string
     * @test
     */
    public function createWrapping()
    {
        //Data
        $giftWrapping = $this->loadDataSet('GiftWrapping', 'gift_wrapping_without_image');
        $search = $this->loadDataSet('GiftWrapping', 'search_gift_wrapping',
            array('filter_gift_wrapping_design' => $giftWrapping['gift_wrapping_design']));
        $edit = $this->loadDataSet('GiftWrapping', 'edit_gift_wrapping_without_image');
        $searchEdit = $this->loadDataSet('GiftWrapping', 'search_gift_wrapping',
            array('filter_gift_wrapping_design' => $edit['gift_wrapping_design']));
        //Steps and Verification
        $this->navigate('manage_gift_wrapping');
        $this->giftWrappingHelper()->createGiftWrapping($giftWrapping);
        $this->assertMessagePresent('success', 'success_saved_gift_wrapping');
        $this->giftWrappingHelper()->openGiftWrapping($search);
        $this->giftWrappingHelper()->verifyGiftWrapping($giftWrapping);
        $this->giftWrappingHelper()->fillGiftWrappingForm($edit);
        $this->assertMessagePresent('success', 'success_saved_gift_wrapping');
        $this->giftWrappingHelper()->openGiftWrapping($searchEdit);
        $this->giftWrappingHelper()->verifyGiftWrapping($edit);

        return $edit['gift_wrapping_design'];
    }

    /**
     * <p>Test Case Test Case TL-MAGE-840: Editing/reconfiguring existing Gift Wrapping</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Gift Wrapping" page;</p>
     * <p>2. Open previously created gift wrapping;</p>
     * <p>3. Change gift wrapping configuration to new data;</p>
     * <p>4. Save gift wrapping</p>
     * <p>Expected Results:</p>
     * <p>1. Gift wrapping is saved;</p>
     *
     * @param string $wrappingDesign
     *
     * @test
     * @depends createWrapping
     */
    public function editWrapping($wrappingDesign)
    {
        //Data
        $giftWrapping = $this->loadDataSet('GiftWrapping', 'gift_wrapping_without_image');
        $editGiftWrapping = $this->loadDataSet('GiftWrapping', 'edit_gift_wrapping_without_image');
        $search = $this->loadDataSet('GiftWrapping', 'search_gift_wrapping',
            array('filter_gift_wrapping_design' => $giftWrapping['gift_wrapping_design']));
        $searchBefore = $this->loadDataSet('GiftWrapping', 'search_gift_wrapping',
            array('filter_gift_wrapping_design' => $wrappingDesign));
        $searchAfter = $this->loadDataSet('GiftWrapping', 'search_gift_wrapping',
            array('filter_gift_wrapping_design' => $editGiftWrapping['gift_wrapping_design']));
        //Steps
        $this->giftWrappingHelper()->openGiftWrapping($searchBefore);
        $this->giftWrappingHelper()->fillGiftWrappingForm($giftWrapping);
        $this->assertMessagePresent('success', 'success_saved_gift_wrapping');
        $this->giftWrappingHelper()->openGiftWrapping($search);
        $this->giftWrappingHelper()->verifyGiftWrapping($giftWrapping);
        $this->giftWrappingHelper()->fillGiftWrappingForm($editGiftWrapping);
        $this->assertMessagePresent('success', 'success_saved_gift_wrapping');
        $this->giftWrappingHelper()->openGiftWrapping($searchAfter);
        $this->giftWrappingHelper()->verifyGiftWrapping($editGiftWrapping);
    }

    /**
     * <p>Test Case TL-MAGE-873: Mass actions with Gift Wrappings (update statuses)</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Gift Wrapping" page;</p>
     * <p>2. Select previously created gift wrapping by checking checkbox;</p>
     * <p>3. Choose massaction action "Change status";</p>
     * <p>4. Choose massaction status "Disable";</p>
     * <p>5. Submit action.</p>
     * <p>6. Choose massaction action "Change status";</p>
     * <p>7. Choose massaction status "Enable";</p>
     * <p>8. Submit action.</p>
     * <p>Expected Results:</p>
     * <p>1. Gift wrapping is updated;</p>
     *
     * @test
     */
    public function massactionEditWrapping()
    {
        //Data
        $giftWrapping = $this->loadDataSet('GiftWrapping', 'gift_wrapping_without_image');
        $search = $this->loadDataSet('GiftWrapping', 'search_gift_wrapping',
            array('filter_gift_wrapping_design' => $giftWrapping['gift_wrapping_design']));
        $this->addParameter('itemCount', '1');
        //Steps
        $this->giftWrappingHelper()->createGiftWrapping($giftWrapping);
        $this->assertMessagePresent('success', 'success_saved_gift_wrapping');
        $this->searchAndChoose($search, 'gift_wrapping_grid');
        $this->fillDropdown('massaction_action', 'Change status');
        $this->fillDropdown('massaction_status', 'Disabled');
        $this->saveForm('submit');
        //Verification
        $this->assertMessagePresent('success', 'success_massaction_update');
        //Steps
        $this->navigate('manage_gift_wrapping');
        $this->searchAndChoose($search, 'gift_wrapping_grid');
        $this->fillDropdown('massaction_action', 'Change status');
        $this->fillDropdown('massaction_status', 'Enabled');
        $this->saveForm('submit');
        //Verification
        $this->assertMessagePresent('success', 'success_massaction_update');
    }

    /**
     * <p>Test Case:</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Gift Wrapping" page;</p>
     * <p>2. Click button "Add Gift Wrapping";</p>
     * <p>3. Fill all required fields except one (from data provider);</p>
     * <p>4. Save gift wrapping</p>
     * <p>Expected Results:</p>
     * <p>1. Gift wrapping is not created;</p>
     * <p>2. Message "This is a required field." for required field appears.</p>
     *
     * @param string $fieldName
     *
     * @test
     * @dataProvider createWrappingWithEmptyFieldsDataProvider
     */
    public function createWrappingWithEmptyFields($fieldName)
    {
        //Data
        $giftWrappingData = $this->loadDataSet('GiftWrapping', 'gift_wrapping_without_image', array($fieldName => ''));
        //Steps
        $this->navigate('manage_gift_wrapping');
        $this->giftWrappingHelper()->createGiftWrapping($giftWrappingData);
        //Verification
        $this->addFieldIdToMessage('field', $fieldName);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function createWrappingWithEmptyFieldsDataProvider()
    {
        return array(
            array('gift_wrapping_design'),
            array('gift_wrapping_price')
        );
    }

    /**
     * <p>Test Case:</p>
     * <p>Steps:</p>
     * <p>1. Navigate to "Manage Gift Wrapping" page;</p>
     * <p>2. Click button "Add Gift Wrapping";</p>
     * <p>3. Fill all required fields with correct data except price (enter "-10");</p>
     * <p>4. Save gift wrapping</p>
     * <p>Expected Results:</p>
     * <p>1. Gift wrapping is not created;</p>
     * <p>2. Message "Please enter a valid number in this field." for price field appears.</p>
     *
     * @param string $fieldData
     * @param string $messageName
     *
     * @test
     * @dataProvider incorrectPriceDataProvider
     */
    public function createWrappingWithIncorrectPrice($fieldData, $messageName)
    {
        //Data
        $giftWrapping = $this->loadDataSet('GiftWrapping', 'gift_wrapping_without_image',
            array('gift_wrapping_price' => $fieldData));
        //Steps
        $this->giftWrappingHelper()->createGiftWrapping($giftWrapping);
        //Verification
        $this->addFieldIdToMessage('field', 'gift_wrapping_price');
        $this->assertMessagePresent('validation', $messageName);
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function incorrectPriceDataProvider()
    {
        return array(
            array('-10', 'enter_not_negative_number'),
            array('abc', 'enter_greater_zero')
        );
    }
}
