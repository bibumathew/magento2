<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Core\Model\Design\FileResolution;

class StrategyPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\ObjectManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\App\State|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\Dir|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirs;

    /**
     * @var \Magento\Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\Design\FileResolution\StrategyPool|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Core\Model\ObjectManager', array(), array(), '', false);
        $this->_appState = $this->getMock('Magento\Core\Model\App\State', array(), array(), '', false);

        $this->_dirs = new \Magento\Core\Model\Dir('base_dir');

        $this->_filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);

        $this->_model = new \Magento\Core\Model\Design\FileResolution\StrategyPool($this->_objectManager,
            $this->_appState, $this->_dirs, $this->_filesystem);
    }

    /**
     * Test, that strategy creation works and a strategy is returned.
     *
     * Do not test exact strategy returned, as it depends on configuration, which can be changed any time.
     *
     * @param string $mode
     * @dataProvider getStrategyDataProvider
     */
    public function testGetStrategy($mode)
    {
        $this->_appState->expects($this->exactly(3)) // 3 similar methods tested at once
            ->method('getMode')
            ->will($this->returnValue($mode));

        $strategy = new \StdClass;
        $mapDir = 'base_dir/var/' . \Magento\Core\Model\Design\FileResolution\StrategyPool::FALLBACK_MAP_DIR;
        $mapDir = str_replace('/', DIRECTORY_SEPARATOR, $mapDir);
        $map = array(
            array(
                'Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy',
                array(
                    'mapDir' => $mapDir,
                    'baseDir' => 'base_dir'
                ),
                $strategy
            ),
            array('Magento\Core\Model\Design\FileResolution\Strategy\Fallback', array(), $strategy),
        );
        $this->_objectManager->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValueMap($map));

        // Test
        $actual = call_user_func(array($this->_model, 'getFileStrategy'));
        $this->assertSame($strategy, $actual);

        $actual = call_user_func(array($this->_model, 'getLocaleStrategy'));
        $this->assertSame($strategy, $actual);

        $actual = call_user_func(array($this->_model, 'getViewStrategy'));
        $this->assertSame($strategy, $actual);
    }

    public static function getStrategyDataProvider()
    {
        return array(
            'default mode' => array(
                \Magento\Core\Model\App\State::MODE_DEFAULT
            ),
            'production mode' => array(
                \Magento\Core\Model\App\State::MODE_PRODUCTION
            ),
            'developer mode' => array(
                \Magento\Core\Model\App\State::MODE_DEVELOPER
            ),
        );
    }
}
