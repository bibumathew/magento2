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
 * Test class for \Magento\ImportExport\Model\Source\Import\Behavior\Custom
 */
namespace Magento\ImportExport\Model\Source\Import\Behavior;

class CustomTest
    extends \Magento\ImportExport\Model\Source\Import\BehaviorTestCaseAbstract
{
    /**
     * Expected behavior group code
     *
     * @var string
     */
    protected $_expectedCode = 'custom';

    /**
     * Expected behaviours
     *
     * @var array
     */
    protected $_expectedBehaviors = array(
        \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_CUSTOM,
    );

    protected function setUp()
    {
        parent::setUp();
        $this->_model = new \Magento\ImportExport\Model\Source\Import\Behavior\Custom(array());
    }

    /**
     * Test toArray method
     *
     * @covers \Magento\ImportExport\Model\Source\Import\Behavior\Custom::toArray
     */
    public function testToArray()
    {
        $behaviorData = $this->_model->toArray();
        $this->assertInternalType('array', $behaviorData);
        $this->assertEquals($this->_expectedBehaviors, array_keys($behaviorData));
    }

    /**
     * Test behavior group code
     *
     * @covers \Magento\ImportExport\Model\Source\Import\Behavior\Custom::getCode
     */
    public function testGetCode()
    {
        $this->assertEquals($this->_expectedCode, $this->_model->getCode());
    }
}
