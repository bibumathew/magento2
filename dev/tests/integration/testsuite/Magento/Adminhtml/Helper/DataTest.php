<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @magentoAppArea adminhtml
 */
class Magento_Adminhtml_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf('Magento\Backend\Helper\Data',
            Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento_Adminhtml_Helper_Data'));
    }
}
