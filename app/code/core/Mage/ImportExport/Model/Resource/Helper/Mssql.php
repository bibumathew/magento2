<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ImportExport Sql Server resource helper model
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Resource_Helper_Mssql extends Mage_Core_Model_Resource_Helper_Mssql
{
    /**
     * Constants to be used for DB
     */
    const DB_MAX_PACKET_SIZE        = 268435456; // Maximal packet length by default in Sql Server
    const DB_MAX_PACKET_COEFFICIENT = 0.9; // The coefficient of useful data from maximum packet length

    /**
     * Returns maximum size of packet, that we can send to DB
     *
     * @return int
     */
    public function getMaxDataSize()
    {
        return floor(self::DB_MAX_PACKET_SIZE * self::DB_MAX_PACKET_COEFFICIENT);
    }

    /**
     * Returns next autoincrement value for a table
     *
     * @param string $table Real table name in DB
     * @return int
     */
    public function getNextAutoincrement($table)
    {
        $adapter = $this->_getReadAdapter();
        $row = $adapter->fetchRow('SELECT IDENT_CURRENT(' . $adapter->quote($table) . ') AS current_id');
        return $row['current_id'] + 1;
    }
}
