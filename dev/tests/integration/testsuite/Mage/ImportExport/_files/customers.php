<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

$customer = new Mage_Customer_Model_Customer();

$customer
    ->setWebsiteId(1)
    ->setEntityId(1)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Firstname')
    ->setLastname('Lastname')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
;
$customer->isObjectNew(true);
$customer->save();

$customer = new Mage_Customer_Model_Customer();
$customer
    ->setWebsiteId(1)
    ->setEntityId(2)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('julie.worrell@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('Julie')
    ->setLastname('Worrell')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
;
$customer->isObjectNew(true);
$customer->save();

$customer = new Mage_Customer_Model_Customer();
$customer
    ->setWebsiteId(1)
    ->setEntityId(3)
    ->setEntityTypeId(1)
    ->setAttributeSetId(0)
    ->setEmail('david.lamar@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('David')
    ->setLastname('Lamar')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
;
$customer->isObjectNew(true);
$customer->save();