<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Magento_Test_Bootstrap.
 */
class Magento_Test_BootstrapTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_magentoDir;
    protected static $_localXmlFile;
    protected static $_tmpDir;
    protected static $_globalEtcFiles;
    protected static $_moduleEtcFiles;

    /**
     * @var Magento_Test_Db_DbAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_db;

    /**
     * @var Magento_Test_Bootstrap|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_bootstrap;

    /**
     * Calculate directories
     */
    public static function setUpBeforeClass()
    {
        self::$_magentoDir     = realpath(__DIR__ . '/../../../../../../../../..');
        self::$_localXmlFile   = realpath(__DIR__ . '/../../../../../../etc/local-mysql.xml.dist');
        self::$_globalEtcFiles = realpath(__DIR__ . '/../../../../../../../../../app/etc/*.xml');
        self::$_moduleEtcFiles = realpath(__DIR__ . '/../../../../../../../../../app/etc/modules/*.xml');
        self::$_tmpDir         = realpath(__DIR__ . '/../../../../../../tmp');
    }

    public static function tearDownAfterClass()
    {
        Magento_Test_Bootstrap::resetShutdownAction();
    }

    protected function setUp()
    {
        $this->_db = $this->getMock(
            'Magento_Test_Db_DbAbstract',
            array(
                'verifyEmptyDatabase',
                'cleanup',
                'createBackup',
                'restoreBackup',
            ),
            array('host', 'user', 'password', 'schema', self::$_tmpDir)
        );
        /* Suppress calling the constructor at this step */
        $this->_bootstrap = $this->getMock(
            'Magento_Test_Bootstrap',
            array(
                'initialize',
                '_verifyDirectories',
                '_instantiateDb',
                '_isInstalled',
                '_emulateEnvironment',
                '_install',
                '_cleanupFilesystem',
            ),
            array(),
            '',
            false
        );
        /* Setup expectations for methods that are being called within the constructor */
        $this->_bootstrap
            ->expects($this->any())
            ->method('_instantiateDb')
            ->will($this->returnValue($this->_db))
        ;
        /* Call constructor explicitly */
        $this->_callBootstrapConstructor();
    }

    /**
     * Explicitly call the constructor method of the underlying bootstrap object
     *
     * @param string|null $localXmlFile
     */
    protected function _callBootstrapConstructor($localXmlFile = null)
    {
        $this->_bootstrap->__construct(
            self::$_magentoDir,
            ($localXmlFile ? $localXmlFile : self::$_localXmlFile),
            self::$_globalEtcFiles,
            self::$_moduleEtcFiles,
            self::$_tmpDir
        );
    }

    public function testGetSetInstance()
    {
        $exceptionOccurred = false;
        try {
            Magento_Test_Bootstrap::getInstance();
        } catch (Exception $e) {
            $exceptionOccurred = true;
        }
        $this->assertTrue($exceptionOccurred, 'Expected occurrence of exception.');

        Magento_Test_Bootstrap::setInstance($this->_bootstrap);
        $this->assertSame($this->_bootstrap, Magento_Test_Bootstrap::getInstance());
    }

    public function testCanTestHeaders()
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->assertFalse(Magento_Test_Bootstrap::canTestHeaders(), 'Expected inability to test headers.');
            return;
        }
        $expectedHeader = 'SomeHeader: header-value';
        $expectedCookie = 'Set-Cookie: SomeCookie=cookie-value';

        /* Make sure that chosen reference samples are unique enough to rely on them */
        $actualHeaders = xdebug_get_headers();
        $this->assertNotContains($expectedHeader, $actualHeaders);
        $this->assertNotContains($expectedCookie, $actualHeaders);

        /* Determine whether header-related functions can be in fact called with no error */
        $expectedCanTestHeaders = true;
        set_error_handler(function() use (&$expectedCanTestHeaders) {
            $expectedCanTestHeaders = false;
        });
        header($expectedHeader);
        setcookie('SomeCookie', 'cookie-value');
        restore_error_handler();

        $this->assertEquals($expectedCanTestHeaders, Magento_Test_Bootstrap::canTestHeaders());

        if ($expectedCanTestHeaders) {
            $actualHeaders = xdebug_get_headers();
            $this->assertContains($expectedHeader, $actualHeaders);
            $this->assertContains($expectedCookie, $actualHeaders);
        }
    }

    public function testConstructorInstallation()
    {
        $this->_bootstrap
            ->expects($this->atLeastOnce())
            ->method('_isInstalled')
            ->will($this->returnValue(false))
        ;
        $this->_db
            ->expects($this->once())
            ->method('verifyEmptyDatabase')
        ;
        $this->_bootstrap
            ->expects($this->once())
            ->method('_install')
        ;
        $this->_callBootstrapConstructor();
    }

    public function testConstructorInitialization()
    {
        $this->_bootstrap
            ->expects($this->atLeastOnce())
            ->method('_isInstalled')
            ->will($this->returnValue(true))
        ;
        $this->_bootstrap
            ->expects($this->once())
            ->method('initialize')
        ;
        $this->_callBootstrapConstructor();
    }

    /**
     * @dataProvider constructorExceptionDataProvider
     * @expectedException Exception
     */
    public function testConstructorException($localXmlFile)
    {
        $this->_callBootstrapConstructor($localXmlFile);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'non existing local.xml' => array('local-non-existing.xml'),
            'invalid local.xml'      => array(__DIR__ . '/Bootstrap/_files/local-invalid.xml'),
        );
    }

    /**
     * @dataProvider getDbVendorNameDataProvider
     */
    public function testGetDbVendorName($localXmlFile, $expectedDbVendorName)
    {
        $this->_callBootstrapConstructor($localXmlFile);
        $this->assertEquals($expectedDbVendorName, $this->_bootstrap->getDbVendorName());
    }

    public function getDbVendorNameDataProvider()
    {
        return array(
            'mysql'  => array(self::$_localXmlFile, 'mysql'),
            'mssql'  => array(realpath(__DIR__ . '/../../../../../../etc/local-mssql.xml.dist'),  'mssql'),
            'oracle' => array(realpath(__DIR__ . '/../../../../../../etc/local-oracle.xml.dist'), 'oracle'),
        );
    }

    /**
     * @expectedException Exception
     */
    public function testCleanupDirException($optionCode)
    {
        $this->_bootstrap->cleanupDir($optionCode);
    }

    /**
     * @return array
     */
    public function cleanupDirExceptionDataProvider()
    {
        return array(
            array('etc_dir'),
            array('var_dir'),
            array('media_dir'),
            array('static_dir')
        );
    }

    public function testSetShutdownActionUninstall()
    {
        $this->_db
            ->expects($this->once())
            ->method('cleanup')
        ;
        $this->_bootstrap
            ->expects($this->once())
            ->method('_cleanupFilesystem')
        ;
        $this->_bootstrap->setShutdownAction('uninstall');
        $this->_bootstrap->__destruct();
    }

    public function testSetShutdownActionRestoreDatabase()
    {
        $this->_db
            ->expects($this->once())
            ->method('restoreBackup')
            ->with(Magento_Test_Bootstrap::DB_BACKUP_NAME)
        ;
        $this->_bootstrap->setShutdownAction('restoreDatabase');
        $this->_bootstrap->__destruct();
    }

    /**
     * @expectedException Exception
     */
    public function testSetShutdownActionException()
    {
        $this->_bootstrap->setShutdownAction('someInvalidAction');
    }

    public function testResetShutdownAction()
    {
        $this->_db
            ->expects($this->never())
            ->method('restoreBackup');
        $this->_bootstrap->setShutdownAction('restoreDatabase');
        $this->_bootstrap->resetShutdownAction();
        $this->_bootstrap->__destruct();
    }
}
