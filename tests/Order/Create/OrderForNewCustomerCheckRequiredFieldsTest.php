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
 * Creating order for new customer with one required field empty
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrderForNewCustomerCheckRequiredFields_Test extends Mage_Selenium_TestCase
{

    /**
     * Preconditions:
     *
     * Log in to Backend.
     * 
     */    
    
    public function setUpBeforeTests()
    {
        $this->windowMaximize();
        $this->loginAdminUser();
    }
    
    /**
     * 
     * Creating products for testing.
     * 
     * Navigate to Sales-Orders page.
     * 
     */    
    protected function assertPreConditions()
    {
        $this->orderHelper()->createProducts('product_to_order1');
        $this->orderHelper()->createProducts('product_to_order2');     
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), 'Wrong page is opened');
        $this->addParameter('id', '0');
    }
    
    /**
     * Create customer via 'Create order' form (required fields are not filled).
     * 
     *
     * Steps:
     *
     * 1.Go to Sales-Orders.
     *
     * 2.Press "Create New Order" button.
     * 
     * 3.Press "Create New Customer" button.
     *
     * 4.Choose 'Main Store' (First from the list of radiobuttons) if exists.
     *
     * 5.Fill all fields except one required.
     * 
     * 6.Press 'Add Products' button.
     * 
     * 7.Add first two products.
     * 
     * 8.Choose shipping address the same as billing.
     * 
     * 9.Check payment method 'Check / Money order'
     * 
     * 10.Choose first from 'Get shipping methods and rates'.
     * 
     * 11.Submit order.
     *
     * Expected result:
     *
     * New customer is not created. Order is not created for the new customer. Message with "Empty required field" appears.
     *
     * @dataProvider data_emptyFields
     *
     * @param array $emptyField
     *
     */    
    public function testOrderWithoutRequiredFieldsFilled($emptyField)
    {
                
        //Data
        $data = $this->loadData(
                        'new_customer_order_billing_address_reqfields',                
                        $emptyField
                );
        //Filling customer's information, address
        $this->orderHelper()->fillNewBillForm($data);
        $email = array('email' =>  $this->generate('email', 32, 'valid'));
        $this->assertTrue($this->fillForm($email, 'order_form_account'));
        //Add products to order
        $this->clickButton('add_products', FALSE);
        //getting products name from dataset. Adding them to the order
        $fieldsetName = 'select_products_to_add';
        $products = $this->loadData('products');
        foreach ($products as $key => $value){
            $prodToAdd = array($key => $value);
            $this->searchAndChoose($prodToAdd, $fieldsetName);
        }
        $this->clickButton('add_selected_products_to_order', FALSE);
        $this->pleaseWait();
        $this->clickControl('radiobutton', 'check_money_order', FALSE);
        $this->pleaseWait();
        $this->clickControl('link', 'get_shipping_methods_and_rates', FALSE);
        $this->pleaseWait();
        $this->clickControl('radiobutton', 'ship_radio1', FALSE);
        $this->pleaseWait();
        $this->clickButton('submit_order', FALSE);
        $this->waitForAjax();
        
        
        $page = $this->getUimapPage('admin', 'new_order_for_new_customer');
        $fieldSet = $page->findFieldset('order_billing_address');
        foreach ($emptyField as $key => $value) {
            if ($value == '%noValue%' || !$fieldSet) {
                continue;
            }
            if ($fieldSet->findField($key) != Null) {
                $fieldXpath = $fieldSet->findField($key);
                
            } else {
                $fieldXpath = $fieldSet->findDropdown($key);
                
            }
            if (preg_match('/street_address/', $key)) {
                $fieldXpath .= "/ancestor::div[@class='multi-input']";
            }
            $this->addParameter('fieldXpath', $fieldXpath);
            
        }   $this->addParameter('fieldXpath', $fieldXpath);
         
        //Check if message appears.
        $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);

      
    }
    
    public function data_emptyFields()
    {
        return array(
            array(array(    'order_first_name'     => ''
                            )),
            array(array(
                            'order_last_name'      => ''
                            )),
            array(array(
                            'order_street_address_first_line'   => ''
                            )),
            array(array(
                            'order_city'    =>  ''
                            )),
            array(array(
                            'order_zip_postal_code' =>  ''
                            )),
            array(array(
                            'order_phone'   =>  ''
                            ))
        );
    }
    
}
