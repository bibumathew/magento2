<?php
/**
 * {license_notice}
 *
 * @category    Paas
 * @package     tests
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

//Add customer
$customer = new Mage_Customer_Model_Customer();
$customer->setStoreId(1)
    ->setCreatedIn('Default Store View')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setEmail('mr.test'.uniqid().'@test.com')
    ->setFirstname('Test')
    ->setLastname('Test')
    ->setMiddlename('Test')
    ->setGroupId(1)
    ->setRewardUpdateNotification(1)
    ->setRewardWarningNotification(1)
    ->save();

//Change customer balance several times to create balance with history
$customerBalance = new Enterprise_CustomerBalance_Model_Balance();
$customerBalance->setCustomerId($customer->getId())
    ->setWebsiteId(1)
    ->setAmountDelta(1000)
    ->save();

//Save customer fixture
Api_CustomerBalance_QuoteTest::$customer = $customer;

//Create new simple product to add it to shopping cart
$product = new Mage_Catalog_Model_Product();
$product->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple'.uniqid())
    ->setPrice(10)
    ->setTaxClassId(0)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    ->setWeight(12)
    ->setStockData(
        array(
            'use_config_manage_stock'   => 1,
            'qty'                       => 100,
            'is_qty_decimal'            => 0,
            'is_in_stock'               => 1,
        )
    )
    ->save();

//Save product fixture
Api_CustomerBalance_QuoteTest::$product = $product;

//Create shopping cart
$quote = new Mage_Sales_Model_Quote();
$quote->setStoreId(1)
        ->setIsActive(false)
        ->setIsMultiShipping(false);

//Add product to cart
//To fill stock item for product, which is not valid at this time
$product->load($product->getId());
$quote->addProduct($product);

//Assign customer to cart
$quote->setCustomer($customer)
    ->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER)
    ->setPasswordHash($customer->encryptPassword($customer->getPassword()));

//Create billing/shipping address
$address = new Mage_Sales_Model_Quote_Address();
$address->setData(array(
    'city'                => 'New York',
    'country_id'          => 'US',
    'fax'                 => '56-987-987',
    'firstname'           => 'Jacklin',
    'lastname'            => 'Sparrow',
    'middlename'          => 'John',
    'postcode'            => '10012',
    'region'              => 'New York',
    'region_id'           => '43',
    'street'              => 'Main Street',
    'telephone'           => '718-452-9207',
    'is_default_billing'  => true,
    'is_default_shipping' => true,
    'use_for_shipping'    => true
));
//Implode street address (this method is overridden)
$address->setStreet($address->getData('street'));

//Assign address to cart
$quote->setBillingAddress($address);
$quote->getShippingAddress()->setSameAsBilling(0);
$quote->collectTotals()
    ->save();

//Save shopping cart
Api_CustomerBalance_QuoteTest::$quote = $quote;

//Create shopping cart by guest
$guestQuote = new Mage_Sales_Model_Quote();
$guestQuote->setStoreId(1)
    ->setIsActive(false)
    ->setIsMultiShipping(false)
    ->addProduct($product);

$guestQuote->setBillingAddress($address);
$guestQuote->getShippingAddress()->setSameAsBilling(0);
$guestQuote->collectTotals()
    ->save();

//Save shopping cart created by guest
Api_CustomerBalance_QuoteTest::$guestQuote = $guestQuote;