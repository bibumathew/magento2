<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   {copyright}
 * @license     {license_link}
 */

/* @var $installer Magento_Customer_Model_Resource_Setup */
$installer = $this;

$disableAGCAttribute = $installer->getEavConfig()->getAttribute('customer', 'disable_auto_group_change');
$disableAGCAttribute->setData('used_in_forms', array(
    'adminhtml_customer'
));
$disableAGCAttribute->save();

$vatAttribute = $installer->getEavConfig()->getAttribute('customer_address', 'vat_id');
$vatAttribute->setData('used_in_forms', array(
     'adminhtml_customer_address',
     'customer_address_edit',
     'customer_register_address'
));
$vatAttribute->save();
