<?php
/**
 * {license_notice}
 *
 * @category    Tools
 * @package     unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/Acl/Db/LoggerAbstract.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/Acl/Db/Logger/File.php';

class Tools_Migration_Acl_Db_Logger_FileTest extends PHPUnit_Framework_TestCase
{
    public function testConstructWithValidFile()
    {
        new Tools_Migration_Acl_Db_Logger_File(realpath(dirname(__FILE__) . '/../../../../../') . '/tmp/');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithInValidFile()
    {
        new Tools_Migration_Acl_Db_Logger_File(null);
    }
}
