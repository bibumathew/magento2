<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
Mage::init('base', 'website');
//Set up customer fixture
//Set up customer address fixture
require 'customer.php';
/** @var $customer Mage_Customer_Model_Customer */
$customer = PHPUnit_Framework_TestCase::getFixture('customer');
/** @var $customerAddress Mage_Customer_Model_Address */
$customerAddress = PHPUnit_Framework_TestCase::getFixture('customer_address');
/*//$customerAddress->addShippingRate($rate);
$customerAddress->setShippingMethod('freeshipping_freeshipping');
$customerAddress->addShippingRate($method);   //$rate
$customerAddress->save();*/

//Set up simple product fixture
require 'product_simple.php';
/** @var $product Mage_Catalog_Model_Product */
$product = PHPUnit_Framework_TestCase::getFixture('product_simple');


//Create quote
$quote = Mage::getModel('Mage_Sales_Model_Quote');
$quote->setStoreId(1)
    ->setIsActive(false)
    ->setIsMultiShipping(false)
    ->assignCustomerWithAddressChange($customer)
    ->setCheckoutMethod($customer->getMode())
    ->setPasswordHash($customer->encryptPassword($customer->getPassword()))
    ->addProduct($product->load($product->getId()), 2);

/** @var $rate Mage_Sales_Model_Quote_Address_Rate */
$rate = Mage::getModel('Mage_Sales_Model_Quote_Address_Rate');
$rate->setCode('freeshipping_freeshipping');
$rate->getPrice(1);

$quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
$quote->getShippingAddress()->addShippingRate($rate);

$quote->collectTotals();
$quote->save();
PHPUnit_Framework_TestCase::setFixture(
    'quote',
    $quote,
    PHPUnit_Framework_TestCase::AUTO_TEAR_DOWN_AFTER_CLASS
);

//Create order
$quoteService = new Mage_Sales_Model_Service_Quote($quote);
//Set payment method to check/money order
$quoteService->getQuote()->getPayment()->setMethod('checkmo');
$order = $quoteService->submitOrder();
$order->place();
$order->save();
PHPUnit_Framework_TestCase::setFixture(
    'order',
    $order,
    PHPUnit_Framework_TestCase::AUTO_TEAR_DOWN_AFTER_CLASS
);
