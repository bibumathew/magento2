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

$connection->addColumn($installer->getTable('core_theme_files'), 'is_temporary', array(
    'type'     => Magento_DB_Ddl_Table::TYPE_BOOLEAN,
    'nullable' => false,
    'default'  => 0,
    'comment'  => 'Is Temporary File'
));

$connection->changeColumn($installer->getTable('core_theme_files'), 'file_name', 'file_path', array(
    'type'     => Magento_DB_Ddl_Table::TYPE_TEXT,
    'length'   => 255,
    'nullable' => true,
    'comment'  => 'Relative path to file'
));

$connection->changeColumn($installer->getTable('core_theme_files'), 'order', 'sort_order', array(
    'type'     => Magento_DB_Ddl_Table::TYPE_SMALLINT,
));

/**
 * Create table 'core_theme_files_link'
 */
$table = $connection->newTable($installer->getTable('core_theme_files_link'))
    ->addColumn('files_link_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'unsigned' => true,
        'primary'  => true
    ), 'Customization link id')
    ->addColumn('theme_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true,
    ), 'Theme Id')
    ->addColumn('layout_link_id', Magento_DB_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'unsigned' => true,
    ), 'Theme layout link id')
    ->addForeignKey(
        $installer->getFkName('core_theme_files_link', 'theme_id', 'core_theme', 'theme_id'),
        'theme_id',
        $installer->getTable('core_theme'),
        'theme_id',
        Magento_DB_Ddl_Table::ACTION_CASCADE,
        Magento_DB_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Core theme link on layout update');

$installer->getConnection()->createTable($table);

$installer->endSetup();
