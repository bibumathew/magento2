<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Install
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Mysql resource data model
 */
namespace Magento\Install\Model\Installer\Db;

class Mysql4 extends \Magento\Install\Model\Installer\Db\AbstractDb
{
    /**
     * Retrieve DB server version
     *
     * @return string (string version number | 'undefined')
     */
    public function getVersion()
    {
        $version  = $this->_getConnection()->fetchOne('SELECT VERSION()');
        $version    = $version ? $version : 'undefined';
        $match = array();
        if (preg_match("#^([0-9\.]+)#", $version, $match)) {
            $version = $match[0];
        }
        return $version;
    }

    /**
     * Check InnoDB support
     *
     * @return bool
     */
    public function supportEngine()
    {
        $variables  = $this->_getConnection()->fetchPairs('SHOW ENGINES');
        return isset($variables['InnoDB']) && ($variables['InnoDB'] == 'DEFAULT' || $variables['InnoDB'] == 'YES');
    }

    /**
     * Clean database
     *
     * @return \Magento\Install\Model\Installer\Db\AbstractDb
     */
    public function cleanUpDatabase()
    {
        /** @var $resourceModel \Magento\Core\Model\Resource */
        $resourceModel = \Mage::getModel('Magento\Core\Model\Resource');
        $connection = $resourceModel->getConnection(\Magento\Core\Model\Config\Resource::DEFAULT_SETUP_CONNECTION);
        $connectionConfig = $connection->getConfig();
        $connection->query('DROP DATABASE IF EXISTS ' . $connectionConfig['dbname']);
        $connection->query('CREATE DATABASE ' . $connectionConfig['dbname']);

        return $this;
    }
}
