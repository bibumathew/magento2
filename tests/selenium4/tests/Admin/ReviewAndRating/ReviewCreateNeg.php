<?php

class Admin_ReviewAndRating_ReviewCreateNeg extends TestCaseAbstract {

    /**
     * Setup procedure.
     * Initializes model and loads configuration
     */
    function setUp()
    {
        $this->model = $this->getModel('admin/reviewandrating');
        $this->setUiNamespace();
    }

    /**
     * Test Review creating
     */
    function testReviewCreateNeg()
    {
        $reviewData = array(
                        'status'                    => 'Pending',
                        'nickname'                  => 'Test user',
                        'summary_of_review'         => 'Test review',
                        'review_text'               => 'Test review text',
                        'search_product_sku'        => 'SP-01',
                        'search_product_name'       => 'Simple Product 01.Required Fields',
        );
        if ($this->model->doLogin()) {
            $this->model->doCreateReview($reviewData);
        }
    }

}