<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

include "attribute_set_with_one_attribute.php";

/** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
$attributeSet = PHPUnit_Framework_TestCase::getFixture('attribute_set_with_one_attribute');
/** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
$attribute = PHPUnit_Framework_TestCase::getFixture('eav_configurable_attribute');
$attributeSourceOptions = $attribute->getSource()->getAllOptions(false);
/** @var $simpleProduct Mage_Catalog_Model_Product */
$simpleProduct = require '_fixture/_block/Catalog/Product.php';
$simpleProduct->setAttributeSetId($attributeSet->getId())
    ->setData($attribute->getAttributeCode(), $attributeSourceOptions[0]['value'])
    ->save();
PHPUnit_Framework_TestCase::setFixture('simple_product_for_configurable', $simpleProduct);
