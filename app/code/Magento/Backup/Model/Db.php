<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     \Magento\Backup
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Database backup model
 *
 * @category    Magento
 * @package     \Magento\Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Model;

class Db
{

    /**
     * Buffer length for multi rows
     * default 100 Kb
     *
     */
    const BUFFER_LENGTH = 102400;

    /**
     * List of tables which data should not be backed up
     *
     * @var array
     */
    protected $_ignoreDataTablesList = array(
        'importexport/importdata'
    );

    /**
     * Retrieve resource model
     *
     * @return \Magento\Backup\Model\Resource\Db
     */
    public function getResource()
    {
        return \Mage::getResourceSingleton('Magento\Backup\Model\Resource\Db');
    }

    public function getTables()
    {
        return $this->getResource()->getTables();
    }

    public function getTableCreateScript($tableName, $addDropIfExists=false)
    {
        return $this->getResource()->getTableCreateScript($tableName, $addDropIfExists);
    }

    public function getTableDataDump($tableName)
    {
        return $this->getResource()->getTableDataDump($tableName);
    }

    public function getHeader()
    {
        return $this->getResource()->getHeader();
    }

    public function getFooter()
    {
        return $this->getResource()->getFooter();
    }

    public function renderSql()
    {
        ini_set('max_execution_time', 0);
        $sql = $this->getHeader();

        $tables = $this->getTables();
        foreach ($tables as $tableName) {
            $sql.= $this->getTableCreateScript($tableName, true);
            $sql.= $this->getTableDataDump($tableName);
        }

        $sql.= $this->getFooter();
        return $sql;
    }

    /**
     * Create backup and stream write to adapter
     *
     * @param \Magento\Backup\Model\Backup $backup
     * @return \Magento\Backup\Model\Db
     */
    public function createBackup(\Magento\Backup\Model\Backup $backup)
    {
        $backup->open(true);

        $this->getResource()->beginTransaction();

        $tables = $this->getResource()->getTables();

        $backup->write($this->getResource()->getHeader());

        $ignoreDataTablesList = $this->getIgnoreDataTablesList();

        foreach ($tables as $table) {
            $backup->write($this->getResource()->getTableHeader($table)
                . $this->getResource()->getTableDropSql($table) . "\n");
            $backup->write($this->getResource()->getTableCreateSql($table, false) . "\n");

            $tableStatus = $this->getResource()->getTableStatus($table);

            if ($tableStatus->getRows() && !in_array($table, $ignoreDataTablesList)) {
                $backup->write($this->getResource()->getTableDataBeforeSql($table));

                if ($tableStatus->getDataLength() > self::BUFFER_LENGTH) {
                    if ($tableStatus->getAvgRowLength() < self::BUFFER_LENGTH) {
                        $limit = floor(self::BUFFER_LENGTH / $tableStatus->getAvgRowLength());
                        $multiRowsLength = ceil($tableStatus->getRows() / $limit);
                    }
                    else {
                        $limit = 1;
                        $multiRowsLength = $tableStatus->getRows();
                    }
                }
                else {
                    $limit = $tableStatus->getRows();
                    $multiRowsLength = 1;
                }

                for ($i = 0; $i < $multiRowsLength; $i ++) {
                    $backup->write($this->getResource()->getTableDataSql($table, $limit, $i*$limit));
                }

                $backup->write($this->getResource()->getTableDataAfterSql($table));
            }
        }
        $backup->write($this->getResource()->getTableForeignKeysSql());
        $backup->write($this->getResource()->getFooter());

        $this->getResource()->commitTransaction();

        $backup->close();

        return $this;
    }

    /**.
     * Returns the list of tables which data should not be backed up
     *
     * @return array
     */
    public function getIgnoreDataTablesList()
    {
        $result = array();
        $resource = \Mage::getSingleton('Magento\Core\Model\Resource');

        foreach ($this->_ignoreDataTablesList as $table) {
            $result[] = $resource->getTableName($table);
        }

        return $result;
    }
}
