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

class Mage_Core_Model_Design_Fallback_List_FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Fallback_List_File
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = Mage::getObjectManager()->create('Mage_Core_Model_Design_Fallback_List_File');
    }

    /**
     * @dataProvider getPatternDirsDataProvider
     */
    public function testGetPatternDirs($namespace, $module, $expectedIndexes)
    {
        $dir = Mage::getObjectManager()->get('Mage_Core_Model_Dir');

        $parentTheme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $parentTheme->setThemePath('parent_theme_path');

        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme->setThemePath('theme_path');
        $theme->setParentTheme($parentTheme);

        $params = array(
            'theme' => $theme,
            'area' => 'area',
        );

        $params['namespace'] = $namespace;
        $params['module'] = $module;

        $actualResult = $this->_model->getPatternDirs($params);

        /**
         * This is array of all possible paths. Data provider returns indexes of this array as an expected result
         * Indexes added for easier reading
         */
        $fullExpectedResult = array(
            0 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/theme_path',
            1 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/theme_path/namespace_module',
            2 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/parent_theme_path',
            3 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/parent_theme_path/namespace_module',
            4 => $dir->getDir(Mage_Core_Model_Dir::MODULES) . '/namespace/module/view/area'
        );

        $expectedArray = array();
        foreach ($expectedIndexes as $index) {
            $expectedArray[] = $fullExpectedResult[$index];
        }

        $this->assertSame($expectedArray, $actualResult);
    }

    public function getPatternDirsDataProvider()
    {
        return array(
            'all parameters passed' => array(
                'namespace' => 'namespace',
                'module' => 'module',
                array(0, 1, 2, 3, 4)
            ),
            'no module parameter passed' => array(
                'namespace' => 'namespace',
                'module' => null,
                array(0, 2)
            ),
            'no namespace parameter passed' => array(
                'namespace' => null,
                'module' => 'module',
                array(0, 2)
            ),
            'no optional parameters passed' => array(
                'namespace' => null,
                'module' => null,
                array(0, 2)
            ),
        );
    }

    /**
     * @dataProvider getPatternDirsExceptionDataProvider
     */
    public function testGetPatternDirsException($setParams, $expectedMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedMessage);

        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme->setThemePath('theme_path');

        $params = array(
            'theme' => $theme,
            'area' => 'area',
            'namespace' => 'namespace',
            'module' => 'module'
        );
        $params = array_merge($params, $setParams);
        $this->_model->getPatternDirs($params);
    }

    public function getPatternDirsExceptionDataProvider()
    {
        return array(
            'No theme' => array(
                array('theme' => null),
                '$params["theme"] should be passed and should implement Mage_Core_Model_ThemeInterface'
            ),
            'No area' => array(
                array('area' => null),
                'Required parameter \'area\' was not passed'
            ),
        );
    }
}