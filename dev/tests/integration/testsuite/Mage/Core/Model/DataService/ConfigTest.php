<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_DataService_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_DataService_Config
     */
    protected $_config;

    public function setup()
    {
        $dirs = Mage::getObjectManager()->create(
            'Mage_Core_Model_Dir', array('baseDir' => array(__DIR__ . '/_files'),
                                         'dirs'    => array(Mage_Core_Model_Dir::MODULES => __DIR__ . '/_files'),)
        );
        $fileReader = Mage::getObjectManager()->create(
            'Mage_Core_Model_Config_Loader_Modules_File', array('dirs' => $dirs)
        );
        /** @var Mage_Core_Model_DataService_Config_Loader $configLoader */
        $configLoader = Mage::getObjectManager()->create(
            'Mage_Core_Model_DataService_Config_Loader', array('dirs' => $dirs, 'fileReader' => $fileReader)
        );

        /** @var Mage_Core_Model_DataService_Config_Reader $dsConfigReader */
        /*$dsConfigReader = Mage::getObjectManager()->
            create('Mage_Core_Model_DataService_Config_Reader',
                array(
                    'fileReader' => $configLoader,
                    'configLoader' => $configLoader,
                ));*/

        $this->_config = new Mage_Core_Model_DataService_Config($configLoader);
    }

    public function testGetClassByAliasOverride()
    {
        $classInfo = $this->_config->getClassByAlias('alias');
        $this->assertEquals('last_service', $classInfo['class']);
        $this->assertEquals('last_method', $classInfo['retrieveMethod']);
        $this->assertEquals(
            array(
                'last_arg' => 'last_value',
                'last_arg_two' => 'last_value_two',
            ),
            $classInfo['methodArguments']
        );
    }

}