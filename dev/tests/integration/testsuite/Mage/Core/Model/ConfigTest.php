<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * First part of Mage_Core_Model_Config testing:
 * - general behaviour is tested
 *
 * @see Mage_Core_Model_ConfigFactoryTest
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Mage_Core_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testGetResourceModel()
    {
        $this->assertInstanceOf('Mage_Core_Model_Resource_Config', $this->_createModel(true)->getResourceModel());
    }

    public function testInit()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->getNode());
        $model->init();
        $this->assertInstanceOf('Varien_Simplexml_Element', $model->getNode());
    }

    public function testLoadBase()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->getNode());
        $model->loadBase();
        $this->assertInstanceOf('Varien_Simplexml_Element', $model->getNode('global'));
    }

    /**
     * @param string $etcDir
     * @param array $configOptions
     * @param string $expectedNode
     * @param string $expectedValue
     * @dataProvider loadBaseLocalConfigDataProvider
     */
    public function testLoadBaseLocalConfig($etcDir, array $configOptions, $expectedNode, $expectedValue)
    {
        $configOptions['etc_dir'] = __DIR__ . "/_files/local_config/{$etcDir}";
        /** @var $model Mage_Core_Model_Config */
        $model = Mage::getModel('Mage_Core_Model_Config');
        $model->loadBase();
        $this->assertInstanceOf('Varien_Simplexml_Element', $model->getNode($expectedNode));
        $this->assertEquals($expectedValue, (string)$model->getNode($expectedNode));
    }

    /**
     * @return array
     */
    public function loadBaseLocalConfigDataProvider()
    {
        return array(
            'no local config file & no custom config file' => array(
                'no_local_config_no_custom_config',
                array(Mage_Core_Model_Config::INIT_OPTION_EXTRA_FILE => ''),
                'a/value',
                'b',
            ),
            'no local config file & custom config file' => array(
                'no_local_config_custom_config',
                array(Mage_Core_Model_Config::INIT_OPTION_EXTRA_FILE => 'custom/local.xml'),
                'a',
                '',
            ),
            'no local config file & custom config data' => array(
                'no_local_config_no_custom_config',
                array(
                    Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                        => '<root><a><value>overridden</value></a></root>'
                ),
                'a/value',
                'overridden',
            ),
            'local config file & no custom config file' => array(
                'local_config_no_custom_config',
                array(Mage_Core_Model_Config::INIT_OPTION_EXTRA_FILE => ''),
                'value',
                'local',
            ),
            'local config file & custom config file' => array(
                'local_config_custom_config',
                array(Mage_Core_Model_Config::INIT_OPTION_EXTRA_FILE => 'custom/local.xml'),
                'value',
                'custom',
            ),
            'local config file & invalid custom config file' => array(
                'local_config_custom_config',
                array(Mage_Core_Model_Config::INIT_OPTION_EXTRA_FILE => 'custom/invalid.pattern.xml'),
                'value',
                'local',
            ),
            'local config file & custom config data' => array(
                'local_config_custom_config',
                array(
                    Mage_Core_Model_Config::INIT_OPTION_EXTRA_FILE => 'custom/local.xml',
                    Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA => '<root><value>overridden</value></root>',
                ),
                'value',
                'overridden',
            ),
        );
    }

    public function testLoadBaseInstallDate()
    {
        if (date_default_timezone_get() != 'UTC') {
            $this->markTestSkipped('Test requires "UTC" to be the default timezone.');
        }
        /** @var $model Mage_Core_Model_Config */
        $model = Mage::getModel('Mage_Core_Model_Config');
        $model->setOptions(array(
            Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                => sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'Fri, 21 Dec 2012 00:00:00 +0000')
        ));
        $model->loadBase();
        $this->assertEquals(1356048000, $model->getInstallDate());
    }

    public function testLoadBaseInstallDateInvalid()
    {
        /** @var $model Mage_Core_Model_Config */
        $model = Mage::getModel('Mage_Core_Model_Config');
        $model->setOptions(array(
            Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                => sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'invalid')
        ));
        $model->loadBase();
        $this->assertEmpty($model->getInstallDate());
    }

    public function testLoadLocales()
    {
        $model = Mage::getModel('Mage_Core_Model_Config');
        $model->init(array(
            'locale_dir' => dirname(__FILE__) . '/_files/locale'
        ));
        $model->loadLocales();
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode('global/locale'));
    }

    public function testLoadModulesCache()
    {
        $model = $this->_createModel();
        $model->setOptions(array(
            Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                => sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'Wed, 21 Nov 2012 03:26:00 +0000')
        ));
        $model->loadBase();
        $this->assertTrue($model->loadModulesCache());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode());
    }

    public function testLoadModules()
    {
        $model = $this->_createModel();
        $model->setOptions(self::$_options);
        $model->loadBase();
        $this->assertFalse($model->getNode('modules'));
        $model->loadModules();
        $moduleNode = $model->getNode('modules/Mage_Core');
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $moduleNode);
        $this->assertTrue($moduleNode->is('active'));
    }

    public function testLoadModulesLocalConfigPrevails()
    {
        $model = $this->_createModel();
        $model->setOptions(array(
            Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                => '<config><modules><Mage_Core><active>false</active></Mage_Core></modules></config>'
        ));
        $model->loadBase();
        $model->loadModules();

        $moduleNode = $model->getNode('modules/Mage_Core');
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $moduleNode);
        $this->assertFalse($moduleNode->is('active'), 'Local configuration must prevail over modules configuration.');
    }

    public function testIsLocalConfigLoaded()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->isLocalConfigLoaded());
        $model->setOptions(self::$_options);
        $model->loadBase();
        $this->assertTrue($model->isLocalConfigLoaded());
    }

    public function testLoadDb()
    {
        $samplePath = 'general/locale/firstday';

        // emulate a system config value in database
        $configResource = Mage::getResourceModel('Mage_Core_Model_Resource_Config');
        $configResource->saveConfig($samplePath, 1, 'default', 0);

        try {
            $model = $this->_createModel();
            $model->setOptions(self::$_options);
            $model->loadBase();
            $model->loadModules();

            // load and assert value
            $model->loadDb();
            $this->assertEquals('1', (string)$model->getNode("default/{$samplePath}"));
            $configResource->deleteConfig($samplePath, 'default', 0);
        } catch (Exception $e) {
            $configResource->deleteConfig($samplePath, 'default', 0);
            throw $e;
        }
    }

    public function testReinitBaseConfig()
    {
        $model = $this->_createModel();
        $options[Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA] = '<config><test>old_value</test></config>';
        $model->setOptions($options);
        $model->loadBase();
        $this->assertEquals('old_value', $model->getNode('test'));

        $options[Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA] = '<config><test>new_value</test></config>';
        $model->setOptions($options);
        $model->reinit();
        $this->assertEquals('new_value', $model->getNode('test'));
    }

    public function testGetCache()
    {
        $this->assertInstanceOf('Varien_Cache_Core', $this->_createModel()->getCache());
    }

    public function testSaveCache()
    {
        $model = $this->_createModel(true);
        $model->removeCache();
        $this->assertFalse($model->loadCache());

        $model->saveCache(array(Mage_Core_Model_Cache::OPTIONS_CACHE_ID));
        $this->assertTrue($model->loadCache());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode());
    }

    public function testRemoveCache()
    {
        $model = $this->_createModel();
        $model->removeCache();
        $this->assertFalse($model->loadCache());
    }

    public function testGetSectionNode()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getSectionNode(array('admin'))
        );
    }

    public function testGetNode()
    {
        $model = $this->_createModel();
        $this->assertFalse($model->getNode());
        $model->init(self::$_options);
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode(null, 'store', 1));
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getNode(null, 'website', 1));
    }

    public function testSetNode()
    {
        $model = $this->_createModel();
        $model->init(self::$_options);
        /* some existing node should be used */
        $model->setNode('admin/routers/adminhtml/use', 'test');
        $this->assertEquals('test', (string) $model->getNode('admin/routers/adminhtml/use'));
    }

    public function testDetermineOmittedNamespace()
    {
        $model = $this->_createModel(true);
        $this->assertEquals('cms', $model->determineOmittedNamespace('cms'));
        $this->assertEquals('Mage_Cms', $model->determineOmittedNamespace('cms', true));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent'));
        $this->assertEquals('', $model->determineOmittedNamespace('nonexistent', true));
    }

    public function testGetModuleConfigurationFiles()
    {
        $files = $this->_createModel(true)->getModuleConfigurationFiles('config.xml');
        $this->assertInternalType('array', $files);
        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'config.xml', $file);
            $this->assertFileExists($file);
        }
    }

    public function testGetDistroBaseUrl()
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->assertEquals('http://example.com/', $this->_createModel()->testGetDistroBaseUrl());
    }

    public function testGetModuleConfig()
    {
        $model = $this->_createModel(true);
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getModuleConfig());
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $model->getModuleConfig('Mage_Core'));
    }

    public function testGetModuleDir()
    {
        $model = $this->_createModel(true);
        foreach (array('etc', 'controllers', 'sql', 'data', 'locale') as $type) {
            $dir = $model->getModuleDir($type, 'Mage_Core');
            $this->assertStringEndsWith($type, $dir);
            $this->assertContains('Mage' . DIRECTORY_SEPARATOR . 'Core', $dir);
        }
        $this->assertTrue(is_dir($this->_createModel(true)->getModuleDir('etc', 'Mage_Core')));
    }

    public function testLoadEventObservers()
    {
        $this->_createModel(true)->loadEventObservers('global');
        $this->assertArrayHasKey('log_log_clean_after', Mage::getEvents()->getAllEvents());
    }

    public function testGetPathVars()
    {
        $result = $this->_createModel()->getPathVars();
        $this->assertArrayHasKey('baseUrl', $result);
        $this->assertArrayHasKey('baseSecureUrl', $result);
    }

    public function testGetResourceConfig()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getResourceConfig('cms_setup')
        );
    }

    public function testGetResourceConnectionConfig()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getResourceConnectionConfig('core_read')
        );
    }

    public function testGetResourceTypeConfig()
    {
        $this->assertInstanceOf(
            'Mage_Core_Model_Config_Element', $this->_createModel(true)->getResourceTypeConfig('pdo_mysql')
        );
    }

    public function testGetStoresConfigByPath()
    {
        $model = $this->_createModel(true);

        // default
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url');
        $this->assertArrayHasKey(0, $baseUrl);
        $this->assertArrayHasKey(1, $baseUrl);

        // $allowValues
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(uniqid()));
        $this->assertEquals(array(), $baseUrl);

        // store code
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(), 'code');
        $this->assertArrayHasKey('default', $baseUrl);
        $this->assertArrayHasKey('admin', $baseUrl);

        // store name
        $baseUrl = $model->getStoresConfigByPath('web/unsecure/base_url', array(), 'name');
        $this->assertArrayHasKey('Default Store View', $baseUrl);
        $this->assertArrayHasKey('Admin', $baseUrl);
    }

    /**
     * Test shouldUrlBeSecure() function for "Use Secure URLs in Frontend" = Yes
     *
     * @magentoConfigFixture current_store web/secure/use_in_frontend 1
     */
    public function testShouldUrlBeSecureWhenSecureUsedInFrontend()
    {
        $model = $this->_createModel(true);
        $this->assertFalse($model->shouldUrlBeSecure('/'));
        $this->assertTrue($model->shouldUrlBeSecure('/checkout/onepage'));
    }

    /**
     * Test shouldUrlBeSecure() function for "Use Secure URLs in Frontend" = No
     *
     * @magentoConfigFixture current_store web/secure/use_in_frontend 0
     */
    public function testShouldUrlBeSecureWhenSecureNotUsedInFrontend()
    {
        $model = $this->_createModel(true);
        $this->assertFalse($model->shouldUrlBeSecure('/'));
        $this->assertFalse($model->shouldUrlBeSecure('/checkout/onepage'));
    }

    public function testGetTablePrefix()
    {
        $_prefix = 'prefix_';
        $_model = $this->_createModel(true);
        $_model->setNode('global/resources/db/table_prefix', $_prefix);
        $this->assertEquals($_prefix, (string)$_model->getTablePrefix());
    }

    public function testGetEventConfig()
    {
        $this->assertInstanceOf('Mage_Core_Model_Config_Element',
            $this->_createModel(true)->getEventConfig('global', 'controller_front_init_routers')
        );
    }

    public function testSaveDeleteConfig()
    {
        $model = $this->_createModel(true);
        $model->saveConfig('web/url/redirect_to_base', 0);
        try {
            $model->reinit();
            $this->assertEquals('0', (string)$model->getNode('default/web/url/redirect_to_base'));

            $model->deleteConfig('web/url/redirect_to_base');
            $model->reinit();
            $this->assertEquals('1', (string)$model->getNode('default/web/url/redirect_to_base'));
        } catch (Exception $e) {
            $model->deleteConfig('web/url/redirect_to_base');
            throw $e;
        }
    }

    public function testGetFieldset()
    {
        $fieldset = $this->_createModel(true)->getFieldset('customer_account');
        $this->assertObjectHasAttribute('prefix', $fieldset);
        $this->assertObjectHasAttribute('firstname', $fieldset);
        $this->assertObjectHasAttribute('middlename', $fieldset);
        $this->assertObjectHasAttribute('lastname', $fieldset);
        $this->assertObjectHasAttribute('suffix', $fieldset);
        $this->assertObjectHasAttribute('email', $fieldset);
        $this->assertObjectHasAttribute('password', $fieldset);
    }

    /**
     * Instantiate Mage_Core_Model_Config and initialize (load configuration) if needed
     *
     * @param bool $initialize
     * @return Mage_Core_Model_Config
     */
    protected function _createModel($initialize = false)
    {
        $model = Mage::getModel('Mage_Core_Model_Config');
        if ($initialize) {
            $model->init(self::$_options);
        }
        return $model;
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException InvalidArgumentException
     */
    public function testGetAreaConfigThrowsExceptionIfNonexistentAreaIsRequested()
    {
        Mage::app()->getConfig()->getAreaConfig('non_existent_area_code');
    }

    /**
     * Check if areas loaded correctly from configuration
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/load_configuration.php
     */
    public function testGetAreas()
    {
        $allowedAreas = Mage::app()->getConfig()->getAreas();
        $this->assertNotEmpty($allowedAreas, 'Areas are not initialized');

        $this->assertArrayHasKey('test_area1', $allowedAreas, 'Test area #1 is not loaded');

        $testAreaExpected = array(
            'base_controller' => 'Mage_Core_Controller_Varien_Action',
            'frontName' => 'TESTAREA',
            'routers'         => array(
                'test_router1' => array(
                    'class'   => 'Mage_Core_Controller_Varien_Router_Default'
                ),
                'test_router2' => array(
                    'class'   => 'Mage_Core_Controller_Varien_Router_Default'
                ),
            )
        );
        $this->assertEquals($testAreaExpected, $allowedAreas['test_area1'], 'Test area is not loaded correctly');

        $this->assertArrayNotHasKey('test_area2', $allowedAreas, 'Test area #2 is loaded by mistake');
        $this->assertArrayNotHasKey('test_area3', $allowedAreas, 'Test area #3 is loaded by mistake');
        $this->assertArrayNotHasKey('test_area4', $allowedAreas, 'Test area #4 is loaded by mistake');
        $this->assertArrayNotHasKey('test_area5', $allowedAreas, 'Test area #5 is loaded by mistake');
    }

    /**
     * Check if routers loaded correctly from configuration
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/load_configuration.php
     */
    public function testGetRouters()
    {
        $loadedRouters = Mage::app()->getConfig()->getRouters();
        $this->assertArrayHasKey('test_router1', $loadedRouters, 'Test router #1 is not initialized in test area.');
        $this->assertArrayHasKey('test_router2', $loadedRouters, 'Test router #2 is not initialized in test area.');

        $testRouterExpected = array(
            'class'           => 'Mage_Core_Controller_Varien_Router_Default',
            'area'            => 'test_area1',
            'frontName'       => 'TESTAREA',
            'base_controller' => 'Mage_Core_Controller_Varien_Action'
        );
        $this->assertEquals($testRouterExpected, $loadedRouters['test_router1'], 'Test router is not loaded correctly');
        $this->assertEquals($testRouterExpected, $loadedRouters['test_router2'], 'Test router is not loaded correctly');
    }
}
