<?php
/**
 * {license_notice}
 *
 * @category    tests
 * @package     static
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * JSHint static code analysis tests for javascript files
 */
class Magento_Test_Js_LiveCodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_reportFile = '';

    /**
     * @var array
     */
    protected static $_whiteListJsFiles = array();

    /**
     * @var array
     */
    protected static $_blackListJsFiles = array();

    /**
     * @static Return all files under a path
     * @param string $path
     * @return array
     */
    protected static function _scanJsFile($path)
    {
        if (is_file($path)) {
            return array($path);
        }
        $path = $path == '' ? __DIR__ : $path;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $regexIterator = new RegexIterator($iterator, '/\\.js$/');
        $filePaths = array();
        foreach ($regexIterator as $filePath) {
            $filePaths[] = $filePath->getPathname();
        }
        return $filePaths;
    }

    /**
     * @static Setup report file, black list and white list
     *
     */
    public static function setUpBeforeClass()
    {
        $reportDir = Magento_TestFramework_Utility_Files::init()->getPathToSource() . '/dev/tests/static/report';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0777);
        }
        self::$_reportFile = $reportDir . '/js_report.txt';
        @unlink(self::$_reportFile);
        $whiteList = self::_readLists(__DIR__ . '/_files/whitelist/*.txt');
        $blackList = self::_readLists(__DIR__ . '/_files/blacklist/*.txt');
        foreach ($blackList as $listFiles) {
            self::$_blackListJsFiles = array_merge(self::$_blackListJsFiles, self::_scanJsFile($listFiles));
        }
        foreach ($whiteList as $listFiles) {
            self::$_whiteListJsFiles = array_merge(self::$_whiteListJsFiles, self::_scanJsFile($listFiles));
        }
        $blackListJsFiles = self::$_blackListJsFiles;
        $filter = function ($value) use ($blackListJsFiles) {
            return !in_array($value, $blackListJsFiles);
        };
        self::$_whiteListJsFiles = array_filter(self::$_whiteListJsFiles, $filter);
    }

    /**
     * @dataProvider codeJsHintDataProvider
     */
    public function testCodeJsHint($filename)
    {
        $cmd = new Magento_TestFramework_Inspection_JsHint_Command($filename, self::$_reportFile);
        $result = false;
        try {
            $result = $cmd->canRun();
        } catch (Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
        if ($result) {
            $this->assertTrue($cmd->run(array()), $cmd->getLastRunMessage());
        }
    }

    /**
     * Build data provider array with command, js file name, and option
     * @return array
     */
    public function codeJsHintDataProvider()
    {
        self::setUpBeforeClass();
        $map = function ($value) {
            return array($value);
        };
        return array_map($map, self::$_whiteListJsFiles);
    }

    /**
     * Read all text files by specified glob pattern and combine them into an array of valid files/directories
     *
     * The Magento root path is prepended to all (non-empty) entries
     *
     * @param string $globPattern
     * @return array
     */
    protected static function _readLists($globPattern)
    {
        $result = array();
        foreach (glob($globPattern) as $list) {
            $result = array_merge($result, file($list));
        }
        $map = function ($value) {
            return trim($value) ? Magento_TestFramework_Utility_Files::init()->getPathToSource() . DIRECTORY_SEPARATOR .
                str_replace('/', DIRECTORY_SEPARATOR, trim($value)) : '';
        };
        return array_filter(array_map($map, $result), 'file_exists');
    }
}
