<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/**
 * Test Directory Region operations
 */
class Mage_Directory_RegionTest extends Magento_Test_TestCase_ApiAbstract
{
    /**
     * Test directoryRegionList API method
     *
     * @return void
     */
    public function testList()
    {
        $data = $this->call('directoryRegionList', array('country' => 'US'));
        $this->assertTrue(is_array($data), 'Region list is not array');
        $this->assertNotEmpty($data, 'Region list is empty');
        $region = reset($data);
        $this->assertTrue(
            is_string($region['name']) && strlen($region['name']),
            'Region name is empty or not a string'
        );
    }
}
