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

class Mage_Core_Model_AppTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_model;

    /**
     * Application instance initialized with environment
     * Is used in some tests that require initialization
     *
     * @var Mage_Core_Model_App
     */
    protected $_mageModel;

    /**
     * Callback test flag
     *
     * @var bool
     */
    protected $_errorCatchFlag = false;

    protected function setUp()
    {
        $this->_model       = Mage::getModel('Mage_Core_Model_App');
        $this->_mageModel   = Mage::app();
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_mageModel = null;
    }

    /**
     * @covers Mage_Core_Model_App::_initCache
     *
     * @magentoAppIsolation enabled
     */
    public function testInit()
    {
        $this->assertNull($this->_model->getConfig());
        $this->_model->init(Magento_Test_Bootstrap::getInstance()->getInitParams());
        $this->assertInstanceOf('Mage_Core_Model_Config', $this->_model->getConfig());
        $this->assertNotEmpty($this->_model->getConfig()->getNode());
        $this->assertContains(Mage_Core_Model_App::ADMIN_STORE_ID, array_keys($this->_model->getStores(true)));

        // Check that we have shared cache object inside of object manager
        $objectManager = Mage::getObjectManager();
        /** @var $cache Mage_Core_Model_Cache */
        $cache = $objectManager->get('Mage_Core_Model_Cache');
        $appCache = $this->_model->getCacheInstance();
        $this->assertSame($appCache, $cache);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testBaseInit()
    {
        $this->assertNull($this->_model->getConfig());
        $this->_model->baseInit(Magento_Test_Bootstrap::getInstance()->getInitParams());
        $this->assertInstanceOf('Mage_Core_Model_Config', $this->_model->getConfig());
        $this->assertNotEmpty($this->_model->getConfig()->getNode());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRun()
    {
        if (!Magento_Test_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test application run without sending headers');
        }
        $request = new Magento_Test_Request();
        $request->setRequestUri('core/index/index');
        $this->_mageModel->setRequest($request);
        $this->_mageModel->run(Magento_Test_Bootstrap::getInstance()->getInitParams());
        $this->assertTrue($request->isDispatched());
    }

    public function testIsInstalled()
    {
        $this->assertTrue($this->_mageModel->isInstalled());
    }

    public function testGetCookie()
    {
        $this->assertInstanceOf('Mage_Core_Model_Cookie', $this->_model->getCookie());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testIsSingleStoreModeWhenEnabled()
    {
        $this->assertTrue($this->_mageModel->isSingleStoreMode());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store general/single_store_mode/enabled 0
     */
    public function testIsSingleStoreModeWhenDisabled()
    {
        $this->assertFalse($this->_mageModel->isSingleStoreMode());;
    }

    public function testHasSingleStore()
    {
        $this->assertNull($this->_model->hasSingleStore());
        $this->assertTrue($this->_mageModel->hasSingleStore());
    }

    public function testSetCurrentStore()
    {
        $store = Mage::getModel('Mage_Core_Model_Store');
        $this->_model->setCurrentStore($store);
        $this->assertSame($store, $this->_model->getStore());
    }

    public function testSetErrorHandler()
    {
        $this->_model->setErrorHandler(array($this, 'errorHandler'));
        try {
            trigger_error('test', E_USER_NOTICE);
            if (!$this->_errorCatchFlag) {
                $this->fail('Error handler is not working');
            }
            restore_error_handler();
        } catch (Exception $e) {
            restore_error_handler();
            throw $e;
        }
    }

    public function errorHandler()
    {
        $this->_errorCatchFlag = true;
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store general/locale/code de_DE
     */
    public function testLoadArea()
    {
        $translator = Mage::app()->getTranslator();
        $this->assertEmpty($translator->getConfig(Mage_Core_Model_Translate::CONFIG_KEY_LOCALE));
        $this->_model->loadArea('frontend');
        $this->assertEquals('de_DE', $translator->getConfig(Mage_Core_Model_Translate::CONFIG_KEY_LOCALE));
    }

    public function testGetArea()
    {
        $area = $this->_model->getArea('frontend');
        $this->assertInstanceOf('Mage_Core_Model_App_Area', $area);
        $this->assertSame($area, $this->_model->getArea('frontend'));
    }

    /**
     * @expectedException Mage_Core_Model_Store_Exception
     */
    public function testGetNotExistingStore()
    {
        $this->_mageModel->getStore(100);
    }

    public function testGetSafeNotExistingStore()
    {
        $this->_mageModel->getSafeStore(100);
        $this->assertEquals('noRoute', $this->_mageModel->getRequest()->getActionName());
    }

    public function testGetStores()
    {
        $this->assertNotEmpty($this->_mageModel->getStores());
        $this->assertNotContains(Mage_Core_Model_App::ADMIN_STORE_ID, array_keys($this->_mageModel->getStores()));
        $this->assertContains(Mage_Core_Model_App::ADMIN_STORE_ID, array_keys($this->_mageModel->getStores(true)));
    }

    public function testGetDefaultStoreView()
    {
        $store = $this->_mageModel->getDefaultStoreView();
        $this->assertEquals('default', $store->getCode());
    }

    public function testGetDistroLocaleCode()
    {
        $this->assertEquals(Mage_Core_Model_App::DISTRO_LOCALE_CODE, $this->_model->getDistroLocaleCode());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetWebsiteNonExisting()
    {
        $this->assertNotEmpty($this->_mageModel->getWebsite()->getId());
        $this->_mageModel->getWebsite(100);
    }

    public function testGetWebsites()
    {
        $this->assertNotEmpty($this->_mageModel->getWebsites());
        $this->assertNotContains(0, array_keys($this->_mageModel->getWebsites()));
        $this->assertContains(0, array_keys($this->_mageModel->getWebsites(true)));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetGroupNonExisting()
    {
        $this->assertNotEmpty($this->_mageModel->getGroup()->getId());
        $this->_mageModel->getGroup(100);
    }

    public function testGetLocale()
    {
        $locale = $this->_model->getLocale();
        $this->assertInstanceOf('Mage_Core_Model_Locale', $locale);
        $this->assertSame($locale, $this->_model->getLocale());
    }

    public function testGetTranslator()
    {
        $translate = $this->_model->getTranslator();
        $this->assertInstanceOf('Mage_Core_Model_Translate', $translate);
        $this->assertSame($translate, $this->_model->getTranslator());
    }

    /**
     * @dataProvider getHelperDataProvider
     */
    public function testGetHelper($inputHelperName, $expectedClass)
    {
        $this->assertInstanceOf($expectedClass, $this->_model->getHelper($inputHelperName));
    }

    public function getHelperDataProvider()
    {
        return array(
            'class name'  => array('Mage_Core_Helper_Data', 'Mage_Core_Helper_Data'),
            'module name' => array('Mage_Core',             'Mage_Core_Helper_Data'),
        );
    }

    public function testGetBaseCurrencyCode()
    {
        $this->assertEquals('USD', $this->_model->getBaseCurrencyCode());
    }

    public function testGetConfig()
    {
        $this->assertNull($this->_model->getConfig());
        $this->assertInstanceOf('Mage_Core_Model_Config', $this->_mageModel->getConfig());
    }

    public function testGetFrontController()
    {
        $front = $this->_mageModel->getFrontController();
        $this->assertInstanceOf('Mage_Core_Controller_Varien_Front', $front);
        $this->assertSame($front, $this->_mageModel->getFrontController());
    }

    public function testGetCacheInstance()
    {
        $cache = $this->_mageModel->getCacheInstance();
        $this->assertInstanceOf('Mage_Core_Model_Cache', $cache);
        $this->assertSame($cache, $this->_mageModel->getCacheInstance());
    }

    public function testGetCache()
    {
        $this->assertInstanceOf('Zend_Cache_Core', $this->_mageModel->getCache());
    }

    public function testLoadSaveRemoveCache()
    {
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
        $this->_mageModel->saveCache('test_data', 'test_id');
        $this->assertEquals('test_data', $this->_mageModel->loadCache('test_id'));
        $this->_mageModel->removeCache('test_id');
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
    }

    public function testCleanCache()
    {
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
        $this->_mageModel->saveCache('test_data', 'test_id', array('test_tag'));
        $this->assertEquals('test_data', $this->_mageModel->loadCache('test_id'));
        $this->_mageModel->cleanCache(array('test_tag'));
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
    }

    public function testUseCache()
    {
        $this->assertTrue($this->_mageModel->useCache('config'));
        $this->assertFalse($this->_mageModel->useCache('not_existing_type'));
    }

    public function testSetGetRequest()
    {
        $this->assertInstanceOf('Mage_Core_Controller_Request_Http', $this->_model->getRequest());
        $this->_model->setRequest(new Magento_Test_Request());
        $this->assertInstanceOf('Magento_Test_Request', $this->_model->getRequest());
    }

    public function testSetGetResponse()
    {
        if (!Magento_Test_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get response without sending headers');
        }
        $this->assertInstanceOf('Mage_Core_Controller_Response_Http', $this->_model->getResponse());
        $this->_model->setResponse(new Magento_Test_Response());
        $this->assertInstanceOf('Magento_Test_Response', $this->_model->getResponse());
    }

    public function testSetGetUpdateMode()
    {
        $this->assertFalse($this->_model->getUpdateMode());
        $this->_model->setUpdateMode(true);
        $this->assertTrue($this->_model->getUpdateMode());
    }

    /**
     * @expectedException Mage_Core_Model_Store_Exception
     */
    public function testThrowStoreException()
    {
        $this->_model->throwStoreException('test');
    }

    public function testSetGetUseSessionVar()
    {
        $this->assertFalse($this->_model->getUseSessionVar());
        $this->_model->setUseSessionVar(true);
        $this->assertTrue($this->_model->getUseSessionVar());
    }

    public function testGetAnyStoreView()
    {
        $this->assertInstanceOf('Mage_Core_Model_Store', $this->_mageModel->getAnyStoreView());
    }

    public function testSetGetUseSessionInUrl()
    {
        $this->assertTrue($this->_model->getUseSessionInUrl());
        $this->_model->setUseSessionInUrl(false);
        $this->assertFalse($this->_model->getUseSessionInUrl());
    }

    public function testGetGroups()
    {
        $groups = $this->_mageModel->getGroups();
        $this->assertInternalType('array', $groups);
        $this->assertGreaterThanOrEqual(1, count($groups));
    }

    /**
     * @magentoConfigFixture global/di/preferences/Mage_Core_Model_Url Mage_Backend_Model_Url
     * @magentoConfigFixture frontend/di/preferences/Mage_Core_Model_Url Mage_DesignEditor_Model_Url_NavigationMode
     */
    public function testLoadDiConfiguration()
    {
        $objectManager = Mage::getObjectManager();
        $this->_model  = $objectManager->get('Mage_Core_Model_App');
        $this->_model->getConfig()->loadDiConfiguration('frontend');
        $testInstance  = $objectManager->create('Mage_Backend_Block_Widget_Grid_ColumnSet');
        $this->assertAttributeInstanceOf('Mage_DesignEditor_Model_Url_NavigationMode', '_urlBuilder', $testInstance);
    }
}
