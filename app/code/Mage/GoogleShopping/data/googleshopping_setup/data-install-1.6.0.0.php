<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_GoogleShopping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/** @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;

if (Mage::helper('Mage_GoogleShopping_Helper_Data')->isModuleEnabled('Mage_GoogleBase')) {
    $typesInsert = $installer->getConnection()
        ->select()
        ->from(
            $installer->getTable('googlebase_types'),
            array(
                'type_id',
                'attribute_set_id',
                'target_country',
                'category' => new Zend_Db_Expr('NULL')
            )
        )
        ->insertFromSelect($installer->getTable('googleshopping_types'));

    $itemsInsert = $installer->getConnection()
        ->select()
        ->from(
            $installer->getTable('googlebase_items'),
            array(
                'item_id',
                'type_id',
                'product_id',
                'gbase_item_id',
                'store_id',
                'published',
                'expires'
            )
        )
        ->insertFromSelect($installer->getTable('googleshopping_items'));

    $attributes = '';
    foreach (Mage::getModel('Mage_GoogleShopping_Model_Config')->getAttributes() as $destAttribtues) {
        foreach ($destAttribtues as $code => $info) {
            $attributes .= "'$code',";
        }
    }
    $attributes = rtrim($attributes, ',');
    $attributesInsert = $installer->getConnection()
        ->select()
        ->from(
            $installer->getTable('googlebase_attributes'),
            array(
                'id',
                'attribute_id',
                'gbase_attribute' => new Zend_Db_Expr("IF(gbase_attribute IN ($attributes), gbase_attribute, '')"),
                'type_id',
            )
        )
        ->insertFromSelect($installer->getTable('googleshopping_attributes'));

    $installer->run($typesInsert);
    $installer->run($attributesInsert);
    $installer->run($itemsInsert);
}