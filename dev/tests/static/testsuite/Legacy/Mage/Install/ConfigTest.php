<?php
/**
 * Tests for obsolete nodes in install.xml
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Legacy_Mage_Install_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider configFileDataProvider
     */
    public function testConfigFile($file)
    {
        $xml = simplexml_load_file($file);
        $path = '/config/check/php/extensions';
        $this->assertEmpty(
            $xml->xpath($path),
            "Nodes from '{$path}' in install.xml have been moved module.xml"
        );
    }

    /**
     * @return array
     */
    public function configFileDataProvider()
    {
        return Utility_Files::init()->getConfigFiles('install.xml');
    }
}
