<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Magento_Adminhtml_Block_Page_System_Config_Robots_Reset
 */
class Magento_Adminhtml_Block_Page_System_Config_Robots_ResetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Adminhtml_Block_Page_System_Config_Robots_Reset
     */
    private $_resetRobotsBlock;

    /**
     * @var Magento_Page_Helper_Robots|PHPUnit_Framework_MockObject_MockObject
     */
    private $_mockRobotsHelper;

    protected function setUp()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_resetRobotsBlock = $objectManagerHelper->getObject(
            'Magento_Adminhtml_Block_Page_System_Config_Robots_Reset',
            array(
                'application' => $this->getMock('Magento_Core_Model_App', array(), array(), '', false),
                'urlBuilder' => $this->getMock('Magento_Backend_Model_Url', array(), array(), '', false)
            )
        );
        $this->_mockRobotsHelper = $this->getMock('Magento_Page_Helper_Robots',
            array('getRobotsDefaultCustomInstructions'), array(), '', false, false
        );
        Mage::register('_helper/Magento_Page_Helper_Robots', $this->_mockRobotsHelper);
    }

    protected function tearDown()
    {
        Mage::unregister('_helper/Magento_Page_Helper_Robots');
    }

    /**
     * @covers Magento_Adminhtml_Block_Page_System_Config_Robots_Reset::getRobotsDefaultCustomInstructions
     */
    public function testGetRobotsDefaultCustomInstructions()
    {
        $expectedInstructions = 'User-agent: *';
        $this->_mockRobotsHelper
            ->expects($this->once())
            ->method('getRobotsDefaultCustomInstructions')
            ->will($this->returnValue($expectedInstructions));
        $this->assertEquals($expectedInstructions, $this->_resetRobotsBlock->getRobotsDefaultCustomInstructions());
    }
}
