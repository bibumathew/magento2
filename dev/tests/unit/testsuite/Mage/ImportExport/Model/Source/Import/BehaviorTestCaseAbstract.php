<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Abstract class for behavior tests
 */
abstract class Mage_ImportExport_Model_Source_Import_BehaviorTestCaseAbstract extends PHPUnit_Framework_TestCase
{
    /**
     * Model for testing
     *
     * @var Mage_ImportExport_Model_Source_Import_BehaviorAbstract
     */
    protected $_model;

    public function tearDown()
    {
        unset($this->_model);
    }
}
