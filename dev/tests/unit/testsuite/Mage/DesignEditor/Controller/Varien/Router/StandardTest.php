<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_DesignEditor_Controller_Varien_Router_StandardTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test area code
     */
    const AREA_CODE = 'frontend';

    /**
     * Test VDE front name prefix
     */
    const VDE_FRONT_NAME = 'test_front_name';

    /**
     * Test VDE configuration data
     */
    const VDE_CONFIGURATION_DATA = 'vde_config_data';

    /**#@+
     * Test path and host
     */
    const TEST_PATH = '/customer/account';
    const TEST_HOST = 'http://test.domain';
    /**#@-*/

    /**
     * @var Mage_DesignEditor_Controller_Varien_Router_Standard
     */
    protected $_model;

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param bool $isConfiguration
     * @param array $routers
     * @param string|null $matchedValue
     *
     * @dataProvider matchDataProvider
     */
    public function testMatch(
        Mage_Core_Controller_Request_Http $request,
        $isVde,
        $isLoggedIn,
        $isConfiguration,
        array $routers = array(),
        $matchedValue = null
    ) {
        $this->_model = $this->_prepareMocksForTestMatch($request, $isVde, $isLoggedIn, $isConfiguration, $routers);

        $this->assertEquals($matchedValue, $this->_model->match($request));
        if ($isVde && $isLoggedIn) {
            $this->assertEquals(self::TEST_PATH, $request->getPathInfo());
        }
    }

    /**
     * Data provider for testMatch
     *
     * @return array
     */
    public function matchDataProvider()
    {
        $vdeUrl    = self::TEST_HOST . '/' . self::VDE_FRONT_NAME . self::TEST_PATH;
        $notVdeUrl = self::TEST_HOST . self::TEST_PATH;

        $silencedMethods = array('_canBeStoreCodeInUrl');
        $excludedRouters = array(
            'admin' => 'admin router',
            'vde'   => 'vde router',
        );

        // test data to verify routers match logic
        $matchedRequest = $this->getMock('Mage_Core_Controller_Request_Http', $silencedMethods, array($vdeUrl));
        $routerMockedMethods = array('match');

        $matchedController = $this->getMockForAbstractClass('Mage_Core_Controller_Varien_ActionAbstract', array(), '',
            false
        );

        // method "match" will be invoked for this router because it's first in the list
        $matchedRouter = $this->getMock(
            'Mage_Core_Controller_Varien_Router_Base', $routerMockedMethods, array(), '', false
        );
        $matchedRouter->expects($this->once())
            ->method('match')
            ->with($matchedRequest)
            ->will($this->returnValue($matchedController));

        // method "match" will not be invoked for this router because controller will be found by first router
        $notMatchedRouter = $this->getMock(
            'Mage_Core_Controller_Varien_Router_Base', $routerMockedMethods, array(), '', false
        );
        $notMatchedRouter->expects($this->never())
            ->method('match');

        $matchedRouters = array_merge($excludedRouters,
            array('matched' => $matchedRouter, 'not_matched' => $notMatchedRouter)
        );

        return array(
            'not vde request' => array(
                '$request' => $this->getMock(
                    'Mage_Core_Controller_Request_Http', $silencedMethods, array($notVdeUrl)
                ),
                '$isVde'           => false,
                '$isLoggedIn'      => true,
                '$isConfiguration' => false,
            ),
            'not logged as admin' => array(
                '$request' => $this->getMock(
                    'Mage_Core_Controller_Request_Http', $silencedMethods, array($vdeUrl)
                ),
                '$isVde'           => true,
                '$isLoggedIn'      => false,
                '$isConfiguration' => false,
            ),
            'no matched routers' => array(
                '$request' => $this->getMock(
                    'Mage_Core_Controller_Request_Http', $silencedMethods, array($vdeUrl)
                ),
                '$isVde'           => true,
                '$isLoggedIn'      => true,
                '$isConfiguration' => false,
                '$routers'         => $excludedRouters
            ),
            'matched routers' => array(
                '$request'         => $matchedRequest,
                '$isVde'           => true,
                '$isLoggedIn'      => true,
                '$isConfiguration' => true,
                '$routers'         => $matchedRouters,
                '$matchedValue'    => $matchedController,
            ),
        );
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param bool $isConfiguration
     * @param array $routers
     * @return Mage_DesignEditor_Controller_Varien_Router_Standard
     */
    protected function _prepareMocksForTestMatch(
        Mage_Core_Controller_Request_Http $request,
        $isVde,
        $isLoggedIn,
        $isConfiguration,
        array $routers
    ) {
        // default mocks - not affected on method functionality
        $controllerFactory  = $this->getMock('Mage_Core_Controller_Varien_Action_Factory', array(), array(), '', false);
        $objectManager      = $this->getMock('Magento_ObjectManager_Zend', array('get'), array(), '', false);
        $filesystem         = $this->getMockBuilder('Magento_Filesystem')->disableOriginalConstructor()->getMock();
        $app                = $this->getMock('Mage_Core_Model_App', array(), array(), '', false);

        $helper         = $this->_getHelperMock();
        $backendSession = $this->_getBackendSessionMock($isVde, $isLoggedIn);
        $stateModel     = $this->_getStateModelMock($routers);
        $configuration  = $this->_getConfigurationMock($isVde, $isLoggedIn, $isConfiguration);
        $callback = function ($name) use ($helper, $backendSession, $stateModel, $configuration) {
            switch ($name) {
                case 'Mage_DesignEditor_Helper_Data': return $helper;
                case 'Mage_Backend_Model_Auth_Session': return $backendSession;
                case 'Mage_DesignEditor_Model_State': return $stateModel;
                case 'Mage_Core_Model_Config': return $configuration;
                default: return null;
            }
        };
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnCallback($callback));

        $frontController = $this->getMock('Mage_Core_Controller_Varien_Front',
            array('applyRewrites', 'getRouters'), array(), '', false
        );
        if ($isVde && $isLoggedIn) {
            $frontController->expects($this->once())
                ->method('applyRewrites')
                ->with($request);
            $frontController->expects($this->once())
                ->method('getRouters')
                ->will($this->returnValue($routers));
        }

        $router = new Mage_DesignEditor_Controller_Varien_Router_Standard(
            $controllerFactory,
            $objectManager,
            $filesystem,
            $app,
            'frontend',
            'Mage_Core_Controller_Varien_Action'
        );
        $router->setFront($frontController);
        return $router;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getHelperMock()
    {
        $helper = $this->getMock('Mage_DesignEditor_Helper_Data', array('getFrontName'), array(), '', false);
        $helper->expects($this->atLeastOnce())
            ->method('getFrontName')
            ->will($this->returnValue(self::VDE_FRONT_NAME));
        return $helper;
    }

    /**
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getBackendSessionMock($isVde, $isLoggedIn)
    {
        $backendSession = $this->getMock('Mage_Backend_Model_Auth_Session', array('isLoggedIn'), array(), '', false);
        $backendSession->expects($isVde ? $this->once() : $this->never())
            ->method('isLoggedIn')
            ->will($this->returnValue($isLoggedIn));
        return $backendSession;
    }

    /**
     * @param array $routers
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getStateModelMock(array $routers)
    {
        $stateModel = $this->getMock('Mage_DesignEditor_Model_State', array('update'), array(), '', false);
        if (array_key_exists('matched', $routers)) {
            $stateModel->expects($this->once())
                ->method('update')
                ->with(self::AREA_CODE);
            return $stateModel;
        }
        return $stateModel;
    }

    /**
     * @param bool $isVde
     * @param bool $isLoggedIn
     * @param bool $isConfiguration
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getConfigurationMock($isVde, $isLoggedIn, $isConfiguration)
    {
        $configuration = $this->getMock('Mage_Core_Model_Config', array('getNode'), array(), '', false);
        if ($isVde && $isLoggedIn) {
            $configurationData = null;
            if ($isConfiguration) {
                $configurationData = self::VDE_CONFIGURATION_DATA;
            }
            $configuration->expects($this->at(0))
                ->method('getNode')
                ->with(Mage_DesignEditor_Model_Area::AREA_VDE)
                ->will($this->returnValue($configurationData));

            if ($isConfiguration) {
                $elementMock = $this->getMock('stdClass', array('extend'), array(), '', false);
                $elementMock->expects($this->once())
                    ->method('extend')
                    ->with(self::VDE_CONFIGURATION_DATA, true);

                $configuration->expects($this->at(1))
                    ->method('getNode')
                    ->with(Mage_Core_Model_App_Area::AREA_FRONTEND)
                    ->will($this->returnValue($elementMock));
            }
        }
        return $configuration;
    }
}
