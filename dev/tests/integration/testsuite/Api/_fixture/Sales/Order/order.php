<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/* @var $productFixture Mage_Catalog_Model_Product */
$productFixture = require '_fixture/_block/Catalog/Product.php';

/* @var $quoteFixture Mage_Sales_Model_Quote */
$quoteFixture = require '_fixture/_block/Sales/Quote/Quote.php';

/* @var $rateFixture Mage_Sales_Model_Quote_Address_Rate */
$rateFixture = require '_fixture/_block/Sales/Quote/Rate.php';

// Create products
$product1 = clone $productFixture;
$product1->save();
$product2 = clone $productFixture;
$product2->save();

// Create quote
$quoteFixture->addProduct($product1, 1);
$quoteFixture->addProduct($product2, 2);
$quoteFixture->getShippingAddress()->addShippingRate($rateFixture);
$quoteFixture->collectTotals()
    ->save();

//Create order
$quoteService = new Mage_Sales_Model_Service_Quote($quoteFixture);
$order = $quoteService->submitOrder()
    ->place()
    ->save();

PHPUnit_Framework_TestCase::setFixture('products', array($product1, $product2));
PHPUnit_Framework_TestCase::setFixture('quote', $quoteFixture);
PHPUnit_Framework_TestCase::setFixture('order', Mage::getModel('Mage_Sales_Model_Order')->load($order->getId()));
