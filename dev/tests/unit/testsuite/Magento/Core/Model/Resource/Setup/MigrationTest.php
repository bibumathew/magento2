<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Tests for resource setup model needed for migration process between Magento versions
 */
class Magento_Core_Model_Resource_Setup_MigrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Result of update class aliases to compare with expected.
     * Used in callback for Magento_DB_Select::update.
     *
     * @var array
     */
    protected $_actualUpdateResult;

    /**
     * Where conditions to compare with expected.
     * Used in callback for Magento_DB_Select::where.
     *
     * @var array
     */
    protected $_actualWhere;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Magento_DB_Select
     */
    protected $_selectMock;

    protected function tearDown()
    {
        unset($this->_actualUpdateResult);
        unset($this->_actualWhere);
        unset($this->_selectMock);
    }

    /**
     * Retrieve all necessary objects mocks which used inside customer storage
     *
     * @param int $tableRowsCount
     * @param array $tableData
     * @param array $aliasesMap
     *
     * @return array
     */
    protected function _getModelDependencies($tableRowsCount = 0, $tableData = array(), $aliasesMap = array())
    {
        $this->_selectMock = $this->getMock('Magento_DB_Select', array(), array(), '', false);
        $this->_selectMock->expects($this->any())
                    ->method('from')
                    ->will($this->returnSelf());
        $this->_selectMock->expects($this->any())
                    ->method('where')
                    ->will($this->returnCallback(array($this, 'whereCallback')));

        $adapterMock = $this->getMock('Magento_DB_Adapter_Pdo_Mysql',
            array('select', 'update', 'fetchAll', 'fetchOne'), array(), '', false
        );
        $adapterMock->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->_selectMock));
        $adapterMock->expects($this->any())
            ->method('update')
            ->will($this->returnCallback(array($this, 'updateCallback')));
        $adapterMock->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue($tableData));
        $adapterMock->expects($this->any())
            ->method('fetchOne')
            ->will($this->returnValue($tableRowsCount));

        return array(
            'resource_config'   => 'not_used',
            'connection_config' => 'not_used',
            'module_config'     => 'not_used',
            'base_dir'          => 'not_used',
            'path_to_map_file'  => 'not_used',
            'connection'        => $adapterMock,
            'core_helper'       => $this->getMock('Magento_Core_Helper_Data', array(), array(), '', false, false),
            'aliases_map'       => $aliasesMap
        );
    }

    /**
     * Callback for Magento_DB_Select::update
     *
     * @param string $table
     * @param array $bind
     * @param array $where
     */
    public function updateCallback($table, array $bind, $where)
    {
        $fields = array_keys($bind);
        $replacements = array_values($bind);

        $this->_actualUpdateResult[] = array(
            'table' => $table,
            'field' => $fields[0],
            'to' => $replacements[0],
            'from' => $where
        );
    }

    /**
     * Callback for Magento_DB_Select::where
     *
     * @param string $condition
     * @return PHPUnit_Framework_MockObject_MockObject|Magento_DB_Select
     */
    public function whereCallback($condition)
    {
        if (null === $this->_actualWhere) {
            $this->_actualWhere = array();
        }
        if (!empty($condition) && false === strpos($condition, ' IS NOT NULL')
            && !in_array($condition, $this->_actualWhere)
        ) {
            $this->_actualWhere[] = $condition;
        }
        return $this->_selectMock;
    }

    /**
     * @covers Magento_Core_Model_Resource_Setup_Migration::appendClassAliasReplace
     */
    public function testAppendClassAliasReplace()
    {
        $setupModel = new Magento_Core_Model_Resource_Setup_Migration(
            $this->getMock('Magento_Core_Model_Logger', array(), array(), '', false),
            $this->getMock('Magento_Core_Model_Event_Manager', array(), array(), '', false),
            $this->getMock('Magento_Core_Model_Config_Resource', array(), array(), '', false, false),
            $this->getMock('Magento_Core_Model_Config', array(), array(), '', false, false),
            $this->getMock('Magento_Core_Model_ModuleListInterface'),
            $this->getMock('Magento_Core_Model_Resource', array(), array(), '', false, false),
            $this->getMock('Magento_Core_Model_Config_Modules_Reader', array(), array(), '', false, false),
            $this->getMock('Magento_Filesystem', array(), array(), '', false),
            $this->getMock('Magento_Core_Helper_Data', array(), array(), '', false),
            'core_setup',
            $this->_getModelDependencies()
        );

        $setupModel->appendClassAliasReplace('tableName', 'fieldName', 'entityType', 'fieldContentType',
            array('pk_field1', 'pk_field2'), 'additionalWhere'
        );

        $expectedRulesList = array (
            'tableName' => array(
                'fieldName' => array(
                    'entity_type'      => 'entityType',
                    'content_type'     => 'fieldContentType',
                    'pk_fields'        => array('pk_field1', 'pk_field2'),
                    'additional_where' => 'additionalWhere'
                )
            )
        );

        $this->assertAttributeEquals($expectedRulesList, '_replaceRules', $setupModel);
    }

    /**
     * @dataProvider updateClassAliasesDataProvider
     * @covers Magento_Core_Model_Resource_Setup_Migration::doUpdateClassAliases
     * @covers Magento_Core_Model_Resource_Setup_Migration::_updateClassAliasesInTable
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getRowsCount
     * @covers Magento_Core_Model_Resource_Setup_Migration::_applyFieldRule
     * @covers Magento_Core_Model_Resource_Setup_Migration::_updateRowsData
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getTableData
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getReplacement
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getCorrespondingClassName
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getModelReplacement
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getPatternReplacement
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getClassName
     * @covers Magento_Core_Model_Resource_Setup_Migration::_isFactoryName
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getModuleName
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getCompositeModuleName
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getAliasFromMap
     * @covers Magento_Core_Model_Resource_Setup_Migration::_pushToMap
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getAliasesMap
     * @covers Magento_Core_Model_Resource_Setup_Migration::_getAliasInSerializedStringReplacement
     * @covers Magento_Core_Model_Resource_Setup_Migration::_parseSerializedString
     */
    public function testDoUpdateClassAliases($replaceRules, $tableData, $expected, $aliasesMap = array())
    {
        $this->_actualUpdateResult = array();

        $tableRowsCount = count($tableData);

        $setupModel = new Magento_Core_Model_Resource_Setup_Migration(
            $this->getMock('Magento_Core_Model_Logger', array(), array(), '', false),
            $this->getMock('Magento_Core_Model_Event_Manager', array(), array(), '', false),
            $this->getMock('Magento_Core_Model_Config_Resource', array(), array(), '', false, false),
            $this->getMock('Magento_Core_Model_Config', array(), array(), '', false, false),
            $this->getMock('Magento_Core_Model_ModuleListInterface'),
            $this->getMock('Magento_Core_Model_Resource', array(), array(), '', false, false),
            $this->getMock('Magento_Core_Model_Config_Modules_Reader', array(), array(), '', false, false),
            $this->getMock('Magento_Filesystem', array(), array(), '', false),
            $this->getMock('Magento_Core_Helper_Data', array(), array(), '', false),
            'core_setup',
            $this->_getModelDependencies($tableRowsCount, $tableData, $aliasesMap)
        );

        $setupModel->setTable('table', 'table');

        foreach ($replaceRules as $replaceRule) {
            call_user_func_array(array($setupModel, 'appendClassAliasReplace'), $replaceRule);
        }

        $setupModel->doUpdateClassAliases();

        $this->assertEquals($expected['updates'], $this->_actualUpdateResult);

        if (isset($expected['where'])) {
            $this->assertEquals($expected['where'], $this->_actualWhere);
        }

        if (isset($expected['aliases_map'])) {
            $this->assertAttributeEquals($expected['aliases_map'], '_aliasesMap', $setupModel);
        }
    }

    /**
     * Data provider for updating class aliases
     *
     * @return array
     */
    public function updateClassAliasesDataProvider()
    {
        return array(
            'plain text replace model'         => include __DIR__ . '/_files/data_content_plain_model.php',
            'plain text replace resource'      => include __DIR__ . '/_files/data_content_plain_resource.php',
            'plain text replace with pk field' => include __DIR__ . '/_files/data_content_plain_pk_fields.php',
            'xml replace'                      => include __DIR__ . '/_files/data_content_xml.php',
            'wiki markup replace'              => include __DIR__ . '/_files/data_content_wiki.php',
            'serialized php replace'           => include __DIR__ . '/_files/data_content_serialized.php',
        );
    }

    /**
     * @covers Magento_Core_Model_Resource_Setup_Migration::getCompositeModules
     */
    public function testGetCompositeModules()
    {
        $compositeModules = Magento_Core_Model_Resource_Setup_Migration::getCompositeModules();
        $this->assertInternalType('array', $compositeModules);
        $this->assertNotEmpty($compositeModules);
        foreach ($compositeModules as $classAlias => $className) {
            $this->assertInternalType('string', $classAlias);
            $this->assertInternalType('string', $className);
            $this->assertNotEmpty($classAlias);
            $this->assertNotEmpty($className);
        }
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Magento_Filesystem
     */
    protected function _getFilesystemMock()
    {
        $mock = $this->getMockBuilder('Magento_Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        return $mock;
    }
}
