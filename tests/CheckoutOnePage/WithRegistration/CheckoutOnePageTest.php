<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page Checkout test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckoutOnePage_WithRegistration_CheckoutOnePageTest extends Mage_Selenium_TestCase
{

    /**
     *
     * <p>Creating products for testing.</p>
     *
     * <p>Navigate to Sales-Orders page.</p>
     *
     */
    protected function assertPreConditions()
    {
        $this->frontend();
        $this->assertTrue($this->checkCurrentPage('home'), $this->messages);
        $this->addParameter('id', '0');
    }

    /**
     * <p>Creating Simple product with required fields only</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @test
     */
    public function createSimple()
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        //Data
        $productData = $this->loadData('simple_product_for_order', null, array('general_name', 'general_sku'));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);

        return $productData['general_name'];
    }

    /**
     * <p>Checkout with required fields filling</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to this address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Select Shipping Method option</p>
     * <p>7. Click 'Continue' button.</p>
     * <p>8. Select Payment Method option</p>
     * <p>9. Click 'Continue' button.</p>
     * <p>Verify information into "Order Review" tab</p>
     * <p>Expected result:</p>
     * <p>Information window appears "Please specify payment method."</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontCheckoutRequiredFields($productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_register', array('general_name' => $productData),
                array('billing_email'));
        //Steps
        $this->assertTrue($this->logoutCustomer());
        $this->assertTrue($this->frontend('home'));
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
        //Postconditions
        $this->logoutCustomer();
    }

    /**
     * <p>Using long values for fill billing information form (except email field)</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill required fields by long values data.</p>
     * <p>4. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Error message appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontBillingWithLongValues($productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_billing_empty_fields',
                array('general_name' => $productData,
            'billing_first_name' => $this->generate('string', 256, ':punct:'),
            'billing_last_name' => $this->generate('string', 256, ':punct:'),
            'billing_street_address_1' => $this->generate('string', 256, ':punct:'),
            'billing_city' => $this->generate('string', 256, ':punct:'),
            'billing_zip_code' => $this->generate('string', 256, ':punct:'),
            'billing_telephone' => $this->generate('string', 256, ':punct:')), array('billing_email'));
        //Steps
        $this->assertTrue($this->logoutCustomer());
        $this->assertTrue($this->frontend('home'));
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData, FALSE);
        //Verification
        $text = $this->_getControlXpath('message', 'billing_long_values_data_alert');
        $this->waitForAjax();
        $alert = (!$this->isAlertPresent($text)) ? FALSE : TRUE;
        if ($alert == TRUE) {
            $this->getAlert();
            $this->assertTrue($alert);
        } else {
            $this->fail('Alert is not appeared');
        }
    }

    /**
     * <p>Verifying "Use Billing Address" checkbox functionality</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to different address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Check "Use Billing Address" checkbox</p>
     * <p>7. Verify data used for filling form</p>
     * <p>8. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Data must be the same as billing address</p>
     * <p>Customer successfully redirected to the next page, no error massages appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontShippingAddressUseBillingAddress($productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_register', array('general_name' => $productData),
                array('billing_email'));
        //Steps
        $this->assertTrue($this->logoutCustomer());
        $this->assertTrue($this->frontend('home'));
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
        //Postconditions
        $this->logoutCustomer();
    }

    /**
     * <p>Using special characters for fill shipping information form</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Fill in Billing Information tab by regular data</p>
     * <p>3. Click 'Continue' button.</p>
     * <p>4. Fill required fields by long values data.</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Error message appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontShippingWithLongValues($productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_billing_empty_fields',
                array('general_name' => $productData,
            'shipping_first_name' => $this->generate('string', 256, ':punct:'),
            'shipping_last_name' => $this->generate('string', 256, ':punct:'),
            'shipping_street_address_1' => $this->generate('string', 256, ':punct:'),
            'shipping_city' => $this->generate('string', 256, ':punct:'),
            'shipping_zip_code' => $this->generate('string', 256, ':punct:'),
            'shipping_telephone' => $this->generate('string', 256, ':punct:')), array('billing_email'));
        //Steps
        $this->assertTrue($this->logoutCustomer());
        $this->assertTrue($this->frontend('home'));
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData, FALSE);
        //Verification
        $text = $this->_getControlXpath('message', 'billing_long_values_data_alert');
        $this->waitForAjax();
        $alert = (!$this->isAlertPresent($text)) ? FALSE : TRUE;
        if ($alert == TRUE) {
            $this->getAlert();
            $this->assertTrue($alert);
        } else {
            $this->fail('Alert is not appeared');
        }
    }

    /**
     * <p>Using special characters for fill shipping information form</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Fill in Billing Information tab by regular data</p>
     * <p>3. Click 'Continue' button.</p>
     * <p>4. Fill required fields by long values data.</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Error message appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontShippingWithSpecialCharacters($productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_shipping_empty_fields',
                array('general_name' => $productData,
            'shipping_first_name' => $this->generate('string', 255, ':punct:'),
            'shipping_last_name' => $this->generate('string', 255, ':punct:'),
            'shipping_street_address_1' => $this->generate('string', 255, ':punct:'),
            'shipping_city' => $this->generate('string', 255, ':punct:'),
            'shipping_zip_code' => $this->generate('string', 255, ':punct:'),
            'shipping_telephone' => $this->generate('string', 255, ':punct:')), array('billing_email'));
        //Steps
        $this->assertTrue($this->logoutCustomer());
        $this->assertTrue($this->frontend('home'));
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
        //Postconditions
        $this->logoutCustomer();
    }

}

?>
