<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright  {copyright}
 * @license    {license_link}
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * Add column 'updated_at' to 'core_layout_update'
 */
$connection->addColumn($installer->getTable('core_layout_update'), 'updated_at', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'comment'  => 'Last Update Timestamp'
));

$installer->endSetup();