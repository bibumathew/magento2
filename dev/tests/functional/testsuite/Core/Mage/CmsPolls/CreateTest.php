<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_CmsPolls
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Poll creation tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsPolls_CreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->clearInvalidedCache();
    }

    /**
     * <p>Preconditions:</p>
     * <p>Navigate to CMS -> Polls</p>
     * <p>Close all opened Polls</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('poll_manager');
        $this->cmsPollsHelper()->closeAllPolls();
    }

    /**
     * <p>Creating a new Poll</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields</p>
     * <p>3. Click button "Save Poll"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the Poll has been saved.</p>
     * <p>Poll is displayed on About Us page</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3217
     */
    public function createNew()
    {
        //Data
        $pollData = $this->loadDataSet('CmsPoll', 'poll_open');
        $name = $pollData['poll_question'];
        $searchPollData = $this->loadDataSet('CmsPoll', 'search_poll', array('filter_question' => $name));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->cmsPollsHelper()->openPoll($searchPollData);
        $this->cmsPollsHelper()->checkPollData($pollData);
        $this->clearInvalidedCache();
        $this->frontend('about_us');
        $this->assertTrue($this->cmsPollsHelper()->frontCheckPoll($name), 'There is no ' . $name . ' poll on the page');
    }

    /**
     * <p>Creating a new poll with empty required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields, but leave one required field empty;</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>Expected result:</p>
     * <p>Received error message "This is a required field."</p>
     *
     * @param string $emptyField
     * @param string $fieldType
     *
     * @test
     * @dataProvider withEmptyRequiredFieldsDataProvider
     * @TestlinkId    TL-MAGE-3220
     */
    public function withEmptyRequiredFields($emptyField, $fieldType)
    {
        //Data
        $pollData = $this->loadDataSet('CmsPoll', 'poll_empty_required', array($emptyField => ''));
        if ($emptyField == 'visible_in') {
            $this->navigate('manage_stores');
            $this->storeHelper()->createStore('StoreView/generic_store_view', 'store_view');
            $this->assertMessagePresent('success', 'success_saved_store_view');
            $this->navigate('poll_manager');
        }
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->addFieldIdToMessage($fieldType, $emptyField);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('poll_question', 'field'),
            array('visible_in', 'multiselect'),
            array('answer_title', 'field'),
            array('votes_count', 'field')
        );
    }

    /**
     * <p>Creating a new poll without answers</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields, but don't add any answer;</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>Expected result:</p>
     * <p>Received error message "Please, add some answers to this poll first."</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3221
     */
    public function withoutAnswer()
    {
        //Data
        $pollData = $this->loadDataSet('CmsPoll', 'poll_open', array('assigned_answers_set' => '%noValue%'));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('error', 'add_answers');
    }

    /**
     * <p>Creating a new poll with few identical answers</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields, but add few identical answers;</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>Expected result:</p>
     * <p>Received error message "Your answers contain duplicates."</p>
     *
     * @test
     * @depends createNew
     * @TestlinkId TL-MAGE-3218
     */
    public function identicalAnswer()
    {
        //Data
        $pollData = $this->loadDataSet('CmsPoll', 'poll_open', array('answer_title' => 'duplicate'));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('validation', 'duplicate_poll_answer');
    }

    /**
     * <p>Closed poll is not displayed</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll".</p>
     * <p>2. Fill in the fields, set state to "Close".</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>4. Check poll on About Us page.</p>
     * <p>Expected result:</p>
     * <p>Poll should not be displayed.</p>
     *
     * @test
     * @depends createNew
     * @TestlinkId TL-MAGE-3216
     */
    public function closedIsNotDisplayed()
    {
        //Data
        $pollData = $this->loadDataSet('CmsPoll', 'poll_open');
        $name = $pollData['poll_question'];
        $searchPollData = $this->loadDataSet('CmsPoll', 'search_poll', array('filter_question' => $name));
        //Steps
        $this->cmsPollsHelper()->createPoll($pollData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->clearInvalidedCache();
        $this->frontend('about_us');
        $this->assertTrue($this->cmsPollsHelper()->frontCheckPoll($name), 'There is no ' . $name . ' poll on the page');
        //Steps
        $this->admin();
        $this->navigate('poll_manager');
        $this->cmsPollsHelper()->setPollState($searchPollData, 'Closed');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->clearInvalidedCache();
        $this->frontend('about_us');
        $this->assertFalse($this->cmsPollsHelper()->frontCheckPoll($name), 'There is ' . $name . ' poll on the page');
    }

    /**
     * <p>Vote a poll</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Poll"</p>
     * <p>2. Fill in the fields.</p>
     * <p>3. Click button "Save Poll".</p>
     * <p>4. Vote a poll on About Us page.</p>
     * <p>5. Re-open About Us page.</p>
     * <p>Expected result:</p>
     * <p>Poll should not be available for vote</p>
     *
     * @test
     * @depends createNew
     * @TestlinkId TL-MAGE-3219
     */
    public function votePoll()
    {
        //Data
        $pollData = $this->loadDataSet('CmsPoll', 'poll_open');
        $name = $pollData['poll_question'];
        //Steps and Verifying
        $this->cmsPollsHelper()->createPoll($pollData);
        $this->assertMessagePresent('success', 'success_saved_poll');
        $this->clearInvalidedCache();
        $this->frontend();
        $this->navigate('about_us');
        $this->assertTrue($this->cmsPollsHelper()->frontCheckPoll($name), 'There is no ' . $name . ' poll on the page');
        $this->cmsPollsHelper()->vote($name, $pollData['assigned_answers_set']['answer_1']['answer_title']);
        //Verifying
        $this->navigate('about_us');
        $this->assertFalse($this->cmsPollsHelper()->frontCheckPoll($name), 'There is ' . $name . ' poll on the page');
    }
}