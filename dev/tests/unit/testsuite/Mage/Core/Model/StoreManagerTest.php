<?php
/**
 * Test class for Mage_Core_Model_StoreManager
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Core_Model_StoreManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_StoreManager
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storage;

    protected function setUp()
    {
        $this->_factoryMock = $this->getMock('Mage_Core_Model_Store_StorageFactory', array(), array(), '', false);
        $this->_requestMock = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $this->_storage = $this->getMock('Mage_Core_Model_Store_StorageInterface');

        $this->_model = new Mage_Core_Model_StoreManager(
            $this->_factoryMock,
            $this->_requestMock,
            $this->_helperFactoryMock,
            'scope_code',
            'scope_type'
        );
    }

    /**
     * @param $method
     * @param $arguments
     * @param $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethods($method, $arguments, $expectedResult)
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $map = array_values($arguments);
        $map[] = $expectedResult;
        $this->_storage->expects($this->once())
            ->method($method)
            ->will($this->returnValueMap(array($map)));

        $actualResult = call_user_func_array(array($this->_model, $method), $arguments);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function proxyMethodDataProvider()
    {
        return array(
            'getCurrentStore' => array('getCurrentStore', array(), 'currentStoreObject'),
            'getAnyStoreView' => array('getAnyStoreView', array(), 'anyStoreObject'),
            'clearWebsiteCache' => array('clearWebsiteCache', array('id' => 101), null),
            'getGroups' => array('getGroups', array('withDefault' => true, 'codeKey' => true), 'groupsArray'),
            'getGroup' => array('getGroup', array('id' => 102), 'groupObject'),
            'getDefaultStoreView' => array('getDefaultStoreView', array(), 'defaultStoreObject'),
            'reinitStores' => array('reinitStores', array(), null),
            'getWebsites' => array('getWebsites', array('withDefault' => true, 'codeKey' => true), 'websitesArray'),
            'getWebsite' => array('getWebsite', array('id' => 103), 'websiteObject'),
            'getStores' => array('getStores', array('withDefault' => true, 'codeKey' => true), 'storesArray'),
            'getStore' => array('getStore', array('id' => 104), 'storeObject'),
            'hasSingleStore' => array('hasSingleStore', array(), 'singleStoreResult'),
            'throwStoreException' => array('throwStoreException', array(), null),
        );
    }

    public function testGetStorageWithCurrentStore()
    {
        $arguments = array(
            'isSingleStoreAllowed' => true,
            'currentStore' => 'current_store_code',
            'scopeCode' => 'scope_code',
            'scopeType' => 'scope_type',
        );

        $this->_factoryMock->expects($this->any())
            ->method('get')
            ->with($arguments)
            ->will($this->returnValue($this->_storage));

        $this->_storage->expects($this->once())->method('setCurrentStore')->with('current_store_code');

        $this->_model->setCurrentStore('current_store_code');
    }

    public function testGetStorageWithSingleStoreMode()
    {
        $arguments = array(
            'isSingleStoreAllowed' => false,
            'currentStore' => null,
            'scopeCode' => 'scope_code',
            'scopeType' => 'scope_type',
        );

        $this->_factoryMock->expects($this->any())
            ->method('get')
            ->with($arguments)
            ->will($this->returnValue($this->_storage));

        $this->_storage->expects($this->once())->method('setIsSingleStoreModeAllowed')->with(false);

        $this->_model->setIsSingleStoreModeAllowed(false);
    }

    public function testIsSingleStoreModeWhenSingleStoreModeEnabledAndHasSingleStore()
    {
        $helperMock = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);
        $helperMock->expects($this->once())->method('isSingleStoreModeEnabled')->will($this->returnValue(true));

        $this->_helperFactoryMock
            ->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Helper_Data')
            ->will($this->returnValue($helperMock));

        $this->_storage->expects($this->once())->method('hasSingleStore')->will($this->returnValue(true));

        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $this->assertTrue($this->_model->isSingleStoreMode());
    }

    public function testIsSingleStoreModeWhenSingleStoreModeDisabledAndHasSingleStore()
    {
        $helperMock = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);
        $helperMock->expects($this->once())->method('isSingleStoreModeEnabled')->will($this->returnValue(false));

        $this->_helperFactoryMock
            ->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Helper_Data')
            ->will($this->returnValue($helperMock));

        $this->_storage->expects($this->once())->method('hasSingleStore')->will($this->returnValue(true));

        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $this->assertFalse($this->_model->isSingleStoreMode());
    }

    public function testGetSafeStoreWithoutException()
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));
        $this->_storage->expects($this->once())->method('getStore')->with(10)->will($this->returnValue('storeObject'));
        $this->_requestMock->expects($this->never())->method('setActionName');
        $this->_model->getSafeStore(10);
    }

    public function testGetSafeStoreWithExceptionWithCurrentStore()
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));
        $this->_storage->expects($this->once())
            ->method('getStore')
            ->with(10)
            ->will($this->returnCallback(
                function(){
                    throw new Exception('test');
                }
            )
        );

        $this->_storage->expects($this->once())->method('getCurrentStore')->will($this->returnValue('current'));
        $this->_requestMock->expects($this->once())->method('setActionName')->with('noRoute');

        $this->assertInstanceOf('Varien_Object', $this->_model->getSafeStore(10));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetSafeStoreWithExceptionAndWithoutCurrentStore()
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));
        $this->_storage->expects($this->once())
            ->method('getStore')
            ->with(10)
            ->will($this->returnCallback(
                function(){
                    throw new Exception('test');
                }
            )
        );

        $this->_storage->expects($this->once())->method('getCurrentStore')->will($this->returnValue(false));
        $this->_requestMock->expects($this->never())->method('setActionName');

        $this->_model->getSafeStore(10);
    }
}
