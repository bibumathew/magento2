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

if (!Magento_Test_Webservice::getFixture('guest_acl_is_prepared')) {
    // Prepare global acl
    /* @var $rule Mage_Webapi_Model_Acl_Global_Rule */
    $rule = Mage::getModel('Mage_Webapi_Model_Acl_Global_Rule');
    $count = $rule->getCollection()
        ->addFieldToFilter('role_id', array(
            'eq' => Mage_Webapi_Model_Acl_Global_Role::ROLE_GUEST_ID))
        ->addFieldToFilter('resource_id', array(
            'eq' => Mage_Webapi_Model_Acl_Global_Rule::RESOURCE_ALL))
        ->count();
    if (!$count) {
        $rule->setRoleId(Mage_Webapi_Model_Acl_Global_Role::ROLE_GUEST_ID)
            ->setResourceId(Mage_Webapi_Model_Acl_Global_Rule::RESOURCE_ALL)
            ->save();
        Magento_Test_Webservice::setFixture('rule', $rule, Magento_Test_Webservice::AUTO_TEAR_DOWN_AFTER_CLASS);
    }

    // Prepare local filters
    /* @var $attribute Mage_Webapi_Model_Acl_Filter_Attribute */
    $attribute = Mage::getModel('Mage_Webapi_Model_Acl_Filter_Attribute');
    $count = $attribute->getCollection()
        ->addFieldToFilter('user_type', array(
            'eq' => Mage_Webapi_Model_Auth_User_Guest::USER_TYPE))
        ->addFieldToFilter('resource_id', array(
            'eq' => Mage_Webapi_Model_Acl_Global_Rule::RESOURCE_ALL))
        ->count();
    if (!$count) {
        $attribute->setUserType(Mage_Webapi_Model_Auth_User_Guest::USER_TYPE)
            ->setResourceId(Mage_Webapi_Model_Acl_Global_Rule::RESOURCE_ALL)
            ->save();
        Magento_Test_Webservice::setFixture('attribute', $attribute,
            Magento_Test_Webservice::AUTO_TEAR_DOWN_AFTER_CLASS);
    }

    Magento_Test_Webservice::setFixture('guest_acl_is_prepared', true);
}
