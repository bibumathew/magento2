<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Catalog_Model_Product_Type_ConfigurableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Catalog_Model_Product_Type_Configurable
     */
    protected $_model;

    protected function setUp()
    {
        $coreDataMock = $this->getMock('Magento_Core_Helper_Data', array(), array(), '', false);
        $fileStorageDbMock = $this->getMock('Magento_Core_Helper_File_Storage_Database', array(), array(), '', false);
        $filesystem = $this->getMockBuilder('Magento_Filesystem')->disableOriginalConstructor()->getMock();
        $this->_model = new Magento_Catalog_Model_Product_Type_Configurable(
            $coreDataMock,
            $fileStorageDbMock,
            $filesystem
        );
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->_model->hasWeight(), 'This product has not weight, but it should');
    }
}
