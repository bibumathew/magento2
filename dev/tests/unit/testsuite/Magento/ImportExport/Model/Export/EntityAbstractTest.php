<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for \Magento\ImportExport\Model\Export\EntityAbstract
 */
namespace Magento\ImportExport\Model\Export;

class EntityAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for setter and getter of file name property
     *
     * @covers \Magento\ImportExport\Model\Export\EntityAbstract::getFileName
     * @covers \Magento\ImportExport\Model\Export\EntityAbstract::setFileName
     */
    public function testGetFileNameAndSetFileName()
    {
        /** @var $model \Magento\ImportExport\Model\Export\EntityAbstract */
        $model = $this->getMockForAbstractClass(
            'Magento\ImportExport\Model\Export\EntityAbstract',
            array(),
            'Stub_UnitTest_Magento_ImportExport_Model_Export_Entity_TestSetAndGet',
            false
        );

        $testFileName = 'test_file_name';

        $fileName = $model->getFileName();
        $this->assertNull($fileName);

        $model->setFileName($testFileName);
        $this->assertEquals($testFileName, $model->getFileName());

        $fileName = $model->getFileName();
        $this->assertEquals($testFileName, $fileName);
    }
}
