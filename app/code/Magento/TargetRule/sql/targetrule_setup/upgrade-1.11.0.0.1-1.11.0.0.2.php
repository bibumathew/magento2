<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_TargetRule
 * @copyright   {copyright}
 * @license     {license_link}
 */

/* @var $installer \Magento\TargetRule\Model\Resource\Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$connection->modifyColumn(
        $installer->getTable('magento_targetrule'),
        'use_customer_segment',
        array(
            'type'     => \Magento\DB\Ddl\Table::TYPE_SMALLINT,
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            'comment'  => 'Deprecated after 1.11.2.0'
        )
);

$connection->modifyColumn(
        $installer->getTable('magento_targetrule_product'),
        'store_id',
        array(
            'type'      => \Magento\DB\Ddl\Table::TYPE_SMALLINT,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'comment'   => 'Deprecated after 1.11.2.0'
        )
);

$installer->endSetup();
