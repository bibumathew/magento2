<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

$model = new Mage_Catalog_Model_Resource_Eav_Attribute(
    Mage::getModel('Mage_Core_Model_Event_Manager'),
    Mage::getModel('Mage_Core_Model_Cache')
);
$model->setName('system_attribute')
    ->setId(2)
    ->setEntityTypeId(4)
    ->setIsUserDefined(0);
$model->save();
