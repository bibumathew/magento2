<?php
/**
 * {license_notice}
 *
 * @license     {license_link}
 */

$attrSetApi = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Set_Api');
PHPUnit_Framework_TestCase::setFixture(
    'testAttributeSetId',
    $attrSetApi->create('Test Attribute Set Fixture ' . mt_rand(1000, 9999), 4)
);

$attributeSetFixture = simplexml_load_file(__DIR__ . '/_data/xml/AttributeSet.xml');
$data = Magento_Test_Helper_Api::simpleXmlToObject($attributeSetFixture->AttributeEntityToCreate);
$data['attribute_code'] = $data['attribute_code'] . '_' . mt_rand(1000, 9999);

$testAttributeSetAttrIdsArray = array();

$attrApi = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Api');
$testAttributeSetAttrIdsArray[0] = $attrApi->create($data);
PHPUnit_Framework_TestCase::setFixture('testAttributeSetAttrIdsArray', $testAttributeSetAttrIdsArray);
