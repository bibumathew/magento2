<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @copyright   {copyright}
 * @license     {license_link}
 */
$installFile = __DIR__ . '/upgrade-1.6.0.0.1-1.6.0.0.2.php';
if (file_exists($installFile)) {
    include $installFile;
}

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;
/** @var $connection \Magento\DB\Adapter\Pdo\Mysql */
$connection = $installer->getConnection();
$connection->changeTableEngine(
    $installer->getTable('catalog_product_index_price_downlod_tmp'),
    \Magento\DB\Adapter\Pdo\Mysql::ENGINE_MEMORY
);
