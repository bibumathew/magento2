<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Stream filter that collect the data that is going through the stream
 *
 * @link http://php.net/manual/en/function.stream-filter-register.php
 */
class Magento_Test_Profiler_OutputBambooTestFilter extends php_user_filter
{
    private static $_collectedData = '';

    /**
     * Collect intercepted data
     *
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     * @param bool $closing
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$_collectedData .= $bucket->data;
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }

    public static function resetCollectedData()
    {
        self::$_collectedData = '';
    }

    /**
     * Assert that collected data matches expected format
     *
     * @param string $expectedData
     */
    public static function assertCollectedData($expectedData)
    {
        PHPUnit_Framework_Assert::assertStringMatchesFormat(
            $expectedData,
            self::$_collectedData,
            'Expected data went through the stream.'
        );
    }
}

/**
 * Test class for Magento_Test_Profiler_OutputBamboo.
 */
class Magento_Test_Profiler_OutputBambooTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Profiler_OutputBamboo
     */
    protected $_output;

    public static function setUpBeforeClass()
    {
        stream_filter_register('dataCollectorFilter', 'Magento_Test_Profiler_OutputBambooTestFilter');
    }

    /**
     * Reset collected data and prescribe to pass stream data through the collector filter
     */
    protected function setUp()
    {
        Magento_Test_Profiler_OutputBambooTestFilter::resetCollectedData();

        /**
         * @link http://php.net/manual/en/wrappers.php.php
         */
        $this->_output = new Magento_Test_Profiler_OutputBamboo(
            'php://filter/write=dataCollectorFilter/resource=php://memory',
            array('sample metric (ms)' => array('profiler_key_for_sample_metric'))
        );
    }

    public function testDisplay()
    {
        $this->_output->display();
        Magento_Test_Profiler_OutputBambooTestFilter::assertCollectedData("Timestamp,\"sample metric (ms)\"\n%d,%d");
    }
}