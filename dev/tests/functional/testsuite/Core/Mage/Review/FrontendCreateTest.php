<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Review
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Reviews Validation on the frontend
 *
 * @package selenium
 * @subpackage tests
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Review_FrontendCreateTest extends Mage_Selenium_TestCase
{
    protected function tearDownAfterTest()
    {
        $this->frontend();
        $this->selectFrontStoreView();
    }

    /**
     * <p>Preconditions</p>
     *
     * @test
     * @return array
     * @skipTearDown
     */
    public function preconditionsForTests()
    {
        //Data
        $userData = $this->loadDataSet('Customers', 'generic_customer_account');
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        $storeView = $this->loadDataSet('StoreView', 'generic_store_view');
        $rating = $this->loadDataSet('ReviewAndRating', 'default_rating',
            array('visible_in' => $storeView['store_view_name'], 'is_active' => 'Yes'));
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_customer');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->navigate('manage_stores');
        $this->storeHelper()->createStore($storeView, 'store_view');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_store_view');
        //Steps
        $this->navigate('manage_ratings');
        $this->ratingHelper()->createRating($rating);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_rating');
        $this->reindexInvalidedData();
        return array(
            'login'      => array('email'    => $userData['email'],
                                  'password' => $userData['password']),
            'sku'        => $simple['general_sku'],
            'name'       => $simple['general_name'],
            'store'      => $storeView['store_view_name'],
            'withRating' => array('filter_sku'  => $simple['general_sku'],
                                  'rating_name' => $rating['default_value']));
    }

    /**
     * <p>Adding Review to product with Not Logged Customer<p>
     *
     * <p>1. Goto Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Review to product</p>
     * <p>4. Submit review</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "Your review has been accepted for moderation."</p>
     *
     * <p>Verification:</p>
     * <p>1. Login to backend;</p>
     * <p>2. Navigate to Catalog -> Reviews and Ratings -> Customer Reviews -> Pending Reviews;</p>
     * <p>Expected result:</p>
     * <p>Review is present into the list and has type - "Guest";</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-440
     * @skipTearDown
     */
    public function addReviewByGuest($data)
    {
        //Data
        $reviewData = $this->loadDataSet('ReviewAndRating', 'frontend_review');
        $searchData = $this->loadDataSet('ReviewAndRating', 'search_review_guest',
                                         array('filter_nickname'   => $reviewData['nickname'],
                                              'filter_product_sku' => $data['name']));
        //Steps
        $this->logoutCustomer();
        $this->productHelper()->frontOpenProduct($data['name']);
        $this->reviewHelper()->frontendAddReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'accepted_review');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->openReview($searchData);
        //Verification
        $this->reviewHelper()->verifyReviewData($reviewData);
    }

    /**
     * <p>Adding Review with raring to product with Not Logged Customer<p>
     *
     * <p>1. Goto Frontend</p>
     * <p>2. Select Store View</p>
     * <p>2. Open created product</p>
     * <p>3. Add Review with rating to product</p>
     * <p>4. Submit review</p>
     * <p>Expected result:</p>
     * <p>Success message appears - "Your review has been accepted for moderation."</p>
     *
     * <p>Verification:</p>
     * <p>1. Login to backend;</p>
     * <p>2. Navigate to Catalog -> Reviews and Ratings -> Customer Reviews -> Pending Reviews;</p>
     * <p>Expected result:</p>
     * <p>Review is present into the list and has type - "Guest";</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-457
     */
    public function addReviewByGuestWithRating($data)
    {
        //Data
        $reviewData = $this->loadDataSet('ReviewAndRating', 'review_with_rating',
                                         array('rating_name' => $data['withRating']['rating_name']));
        $searchData = $this->loadDataSet('ReviewAndRating', 'search_review_guest',
                                         array('filter_nickname'   => $reviewData['nickname'],
                                              'filter_product_sku' => $data['name']));
        //Steps
        $this->logoutCustomer();
        $this->selectFrontStoreView($data['store']);
        $this->productHelper()->frontOpenProduct($data['name']);
        $this->reviewHelper()->frontendAddReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'accepted_review');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->openReview($searchData);
        //Verification
        $this->reviewHelper()->verifyReviewData($reviewData);
    }

    /**
     * <p>Review creating with Logged Customer</p>
     *
     * <p>1. Login to Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Review to product</p>
     * <p>4. Check confirmation message</p>
     * <p>5. Goto "My Account"</p>
     * <p>6. Check tag displaying in "My Recent Reviews"</p>
     * <p>7. Goto "My Product Reviews" tab</p>
     * <p>8. Check review displaying on the page</p>
     * <p>9. Open current review - page with assigned product opens</p>
     * <p>Expected result:</p>
     * <p>Review is assigned to correct product</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-456
     * @skipTearDown
     */
    public function addReviewByLoggedCustomer($data)
    {
        //Data
        $simple = $data['name'];
        $reviewData = $this->loadDataSet('ReviewAndRating', 'frontend_review');
        $searchData = $this->loadDataSet('ReviewAndRating', 'search_review_customer',
                                         array('filter_nickname'   => $reviewData['nickname'],
                                              'filter_product_sku' => $simple));
        //Steps
        $this->customerHelper()->frontLoginCustomer($data['login']);
        $this->productHelper()->frontOpenProduct($simple);
        $this->reviewHelper()->frontendAddReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'accepted_review');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->editReview(array('status' => 'Approved'), $searchData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_review');
        $this->clearInvalidedCache();
        //Steps
        $this->productHelper()->frontOpenProduct($simple);
        //Verification
        $this->reviewHelper()->frontVerifyReviewDisplaying($reviewData, $simple);
        $this->reviewHelper()->frontVerifyReviewDisplayingInMyAccount($reviewData, $simple);
    }

    /**
     * <p>Review creating empty fields</p>
     *
     * <p>1. Open Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Information to the Review of the product, but with one empty field (via data provider)</p>
     * <p>Expected result:</p>
     * <p>Review is not created. Empty Required Field message appears.</p>
     *
     * @param string $emptyFieldName
     * @param array $data
     *
     * @test
     * @dataProvider withEmptyRequiredFieldsDataProvider
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-3568
     * @skipTearDown
     */
    public function withEmptyRequiredFields($emptyFieldName, $data)
    {
        //Data
        $reviewData = $this->loadDataSet('ReviewAndRating', 'frontend_review', array($emptyFieldName => ''));
        //Steps
        $this->customerHelper()->logoutCustomer();
        $this->productHelper()->frontOpenProduct($data['name']);
        $this->reviewHelper()->frontendAddReview($reviewData);
        //Verification
        $this->addFieldIdToMessage('field', $emptyFieldName);
        $this->assertMessagePresent('validation', 'empty_required_field');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function withEmptyRequiredFieldsDataProvider()
    {
        return array(
            array('nickname'),
            array('summary_of_review'),
            array('review')
        );
    }

    /**
     * <p>Review creating with Logged Customer with special characters in fields</p>
     *
     * <p>1. Login to Frontend</p>
     * <p>2. Open created product</p>
     * <p>3. Add Information to the Review of the product use special(long) values</p>
     * <p>Expected result:</p>
     * <p>Review is created. Review can be opened on the backend.</p>
     *
     * @param string $reviewData
     * @param array $data
     *
     * @test
     * @dataProvider frontendReviewSpecialCharactersDataProvider
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-3569
     * @skipTearDown
     */
    public function frontendReviewSpecialCharacters($reviewData, $data)
    {
        //Data
        $reviewData = $this->loadDataSet('ReviewAndRating', $reviewData);
        $searchData = $this->loadDataSet('ReviewAndRating', 'search_review_guest',
                                         array('filter_nickname'   => $reviewData['nickname'],
                                              'filter_product_sku' => $data['name']));
        //Steps
        $this->logoutCustomer();
        $this->productHelper()->frontOpenProduct($data['name']);
        $this->reviewHelper()->frontendAddReview($reviewData);
        //Verification
        $this->assertMessagePresent('success', 'accepted_review');
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_all_reviews');
        $this->reviewHelper()->openReview($searchData);
        //Verification
        $this->reviewHelper()->verifyReviewData($reviewData);
    }

    public function frontendReviewSpecialCharactersDataProvider()
    {
        return array(
            array('review_long_values'),
            array('review_special_symbols'),
        );
    }
}