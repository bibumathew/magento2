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
 * Test class for \Magento\Adminhtml\Block\Page\System\Config\Robots\Reset
 */
namespace Magento\Adminhtml\Block\Page\System\Config\Robots;

class ResetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Adminhtml\Block\Page\System\Config\Robots\Reset
     */
    private $_resetRobotsBlock;

    /**
     * @var \Magento\Core\Model\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreConfigMock;

    protected function setUp()
    {
        $this->coreConfigMock = $this->getMock(
            'Magento\Core\Model\Config', array('getValue'), array(), '', false
        );

        $this->_resetRobotsBlock = new Reset(
            $this->getMock('Magento\Backend\Block\Template\Context', array(), array(), '', false),
            $this->coreConfigMock,
            array()
        );
    }

    /**
     * @covers \Magento\Adminhtml\Block\Page\System\Config\Robots\Reset::getRobotsDefaultCustomInstructions
     */
    public function testGetRobotsDefaultCustomInstructions()
    {
        $expectedInstructions = 'User-agent: *';
        $this->coreConfigMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($expectedInstructions));
        $this->assertEquals($expectedInstructions, $this->_resetRobotsBlock->getRobotsDefaultCustomInstructions());
    }
}
