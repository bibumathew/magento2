<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Design\FileResolution\Strategy;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\View\Design\Fallback\Factory;

/**
 * Fallback Test
 *
 * @package Magento\View
 */
class FallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_baseDir;

    /**
     * @var string
     */
    protected $_viewDir;

    protected function setUp()
    {
        $this->_baseDir = realpath(__DIR__ . '/../../../_files/fallback');
        $this->_viewDir = $this->_baseDir . DIRECTORY_SEPARATOR . 'design';
    }

    /**
     * Build a model to test
     *
     * @return \Magento\View\Design\FileResolution\Strategy\Fallback
     */
    protected function _buildModel()
    {
        // Prepare config with directories
        $dirs = new \Magento\App\Dir(
            $this->_baseDir,
            array(),
            array(\Magento\App\Dir::THEMES => $this->_viewDir)
        );

        return Bootstrap::getObjectManager()->create(
            'Magento\View\Design\FileResolution\Strategy\Fallback',
            array('fallbackFactory' => new Factory($dirs))
        );
    }

    /**
     * Compose custom theme model with designated path
     *
     * @param string $area
     * @param string $themePath
     * @return \Magento\Core\Model\Theme
     */
    protected function _getThemeModel($area, $themePath)
    {
        /** @var $collection \Magento\Core\Model\Theme\Collection */
        $collection = Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Theme\Collection');
        $themeModel = $collection->setBaseDir($this->_viewDir)
            ->addDefaultPattern()
            ->addFilter('theme_path', $themePath)
            ->addFilter('area', $area)
            ->getFirstItem();
        return $themeModel;
    }

    /**
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string|null $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($file, $area, $themePath, $module, $expectedFilename)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')->setAreaCode($area);
        $model = $this->_buildModel($area, $themePath, null);
        $themeModel = $this->_getThemeModel($area, $themePath);

        $expectedFilename = str_replace('/', DS, $expectedFilename);
        $actualFilename = $model->getFile($area, $themeModel, $file, $module);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getFileDataProvider()
    {
        return array(
            'non-modular: no default inheritance' => array(
                'fixture_template.phtml', 'frontend', 'vendor_standalone_theme', null,
                null,
            ),
            'non-modular: inherit same package & parent theme' => array(
                'fixture_template.phtml', 'frontend', 'vendor_custom_theme', null,
                '%s/frontend/vendor_default/fixture_template.phtml',
            ),
            'non-modular: inherit same package & grandparent theme' => array(
                'fixture_template.phtml', 'frontend', 'vendor_custom_theme2', null,
                '%s/frontend/vendor_default/fixture_template.phtml',
            ),
            'modular: no default inheritance' => array(
                'fixture_template.phtml', 'frontend', 'vendor_standalone_theme', 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular file' => array(
                'fixture_template.phtml', 'frontend', 'vendor_default', 'NonExisting_Module',
                null,
            ),
            'modular: inherit same package & parent theme' => array(
                'fixture_template.phtml', 'frontend', 'vendor_custom_theme', 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/fixture_template.phtml',
            ),
            'modular: inherit same package & grandparent theme' => array(
                'fixture_template.phtml', 'frontend', 'vendor_custom_theme2', 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/fixture_template.phtml',
            ),
        );
    }

    /**
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string|null $expectedFilename
     *
     * @dataProvider getLocaleFileDataProvider
     */
    public function testGetI18nCsvFile($area, $themePath, $locale, $expectedFilename)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')->setAreaCode($area);
        $model = $this->_buildModel($area, $themePath, $locale);
        $themeModel = $this->_getThemeModel($area, $themePath);

        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $actualFilename = $model->getFile($area, $themeModel, 'i18n/' . $locale . '.csv');

        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getLocaleFileDataProvider()
    {
        return array(
            'no default inheritance' => array(
                'frontend', 'vendor_standalone_theme', 'en_US',
                null,
            ),
            'inherit parent theme' => array(
                'frontend', 'vendor_custom_theme', 'en_US',
                '%s/frontend/vendor_custom_theme/i18n/en_US.csv',
            ),
            'inherit grandparent theme' => array(
                'frontend', 'vendor_custom_theme2', 'en_US',
                '%s/frontend/vendor_custom_theme/i18n/en_US.csv',
            ),
        );
    }

    /**
     * Test for the skin files fallback according to the themes inheritance
     *
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @param string|null $expectedFilename
     *
     * @dataProvider getViewFileDataProvider
     */
    public function testGetViewFile($file, $area, $themePath, $locale, $module, $expectedFilename)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\State')->setAreaCode($area);
        $model = $this->_buildModel();
        $themeModel = $this->_getThemeModel($area, $themePath);

        $expectedFilename = str_replace('/', DIRECTORY_SEPARATOR, $expectedFilename);
        $actualFilename = $model->getViewFile($area, $themeModel, $locale, $file, $module);
        if ($expectedFilename) {
            $this->assertStringMatchesFormat($expectedFilename, $actualFilename);
            $this->assertFileExists($actualFilename);
        } else {
            $this->assertFileNotExists($actualFilename);
        }
    }

    public function getViewFileDataProvider()
    {
        return array(
            'non-modular: no default inheritance' => array(
                'fixture_script.js', 'frontend', 'vendor_standalone_theme', null, null,
                null,
            ),
            'non-modular: inherit same package & parent theme' => array(
                'fixture_script.js', 'frontend', 'vendor_custom_theme', null, null,
                '%s/frontend/vendor_default/fixture_script.js',
            ),
            'non-modular: inherit same package & grandparent theme' => array(
                'fixture_script.js', 'frontend', 'vendor_custom_theme2', null, null,
                '%s/frontend/vendor_default/fixture_script.js',
            ),
            'non-modular: fallback to non-localized file' => array(
                'fixture_script.js', 'frontend', 'vendor_default', 'en_US', null,
                '%s/frontend/vendor_default/fixture_script.js',
            ),
            'non-modular: localized file' => array(
                'fixture_script.js', 'frontend', 'vendor_default', 'ru_RU', null,
                '%s/frontend/vendor_default/i18n/ru_RU/fixture_script.js',
            ),
            'non-modular: override js lib file' => array(
                'mage/script.js', 'frontend', 'vendor_custom_theme', null, null,
                '%s/frontend/vendor_custom_theme/mage/script.js',
            ),
            'non-modular: inherit js lib file' => array(
                'mage/script.js', 'frontend', 'vendor_default', null, null,
                '%s/pub/lib/mage/script.js',
            ),
            'modular: no default inheritance' => array(
                'fixture_script.js', 'frontend', 'vendor_standalone_theme', null, 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular file' => array(
                'fixture_script.js', 'frontend', 'vendor_default', null, 'NonExisting_Module',
                null,
            ),
            'modular: no fallback to js lib file' => array(
                'mage/script.js', 'frontend', 'vendor_default', null, 'Fixture_Module',
                null,
            ),
            'modular: no fallback to non-modular localized file' => array(
                'fixture_script.js', 'frontend', 'vendor_default', 'ru_RU', 'NonExisting_Module',
                null,
            ),
            'modular: inherit same package & parent theme' => array(
                'fixture_script.js', 'frontend', 'vendor_custom_theme', null, 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/fixture_script.js',
            ),
            'modular: inherit same package & grandparent theme' => array(
                'fixture_script.js', 'frontend', 'vendor_custom_theme2', null, 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/fixture_script.js',
            ),
            'modular: fallback to non-localized file' => array(
                'fixture_script.js', 'frontend', 'vendor_default', 'en_US', 'Fixture_Module',
                '%s/frontend/vendor_default/Fixture_Module/fixture_script.js',
            ),
            'modular: localized file' => array(
                'fixture_script.js', 'frontend', 'vendor_custom_theme2', 'ru_RU', 'Fixture_Module',
                '%s/frontend/vendor_default/i18n/ru_RU/Fixture_Module/fixture_script.js',
            ),
        );
    }
}
