<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_ImportExport_Model_Export_Config_XsdTest extends PHPUnit_Framework_TestCase
{
    /**
     * Path to xsd file
     * @var string
     */
    protected $_xsdSchemaPath;

    /**
     * @var Magento_TestFramework_Utility_XsdValidator
     */
    protected $_xsdValidator;

    protected function setUp()
    {
        $this->_xsdSchemaPath = BP . '/app/code/Magento/ImportExport/etc/';
        $this->_xsdValidator = new Magento_TestFramework_Utility_XsdValidator();
    }

    /**
     * @param string $schemaName
     * @param string $xmlString
     * @param array $expectedError
     */
    protected function _loadDataForTest($schemaName, $xmlString, $expectedError)
    {
        $actualError = $this->_xsdValidator->validate($this->_xsdSchemaPath . $schemaName, $xmlString);
        $this->assertEquals($expectedError, $actualError);
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesExportOptionsDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidProductOptionsXml($xmlString, $expectedError)
    {
        $this->_loadDataForTest('export.xsd', $xmlString, $expectedError);
    }

    /**
     * @param string $xmlString
     * @param array $expectedError
     * @dataProvider schemaCorrectlyIdentifiesInvalidExportMergedXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesInvalidProductOptionsMergedXml($xmlString, $expectedError)
    {
        $this->_loadDataForTest('export_merged.xsd', $xmlString, $expectedError);
    }

    /**
     * @param string $schemaName
     * @param string $validFileName
     * @dataProvider schemaCorrectlyIdentifiesValidXmlDataProvider
     */
    public function testSchemaCorrectlyIdentifiesValidXml($schemaName, $validFileName)
    {
        $xmlString = file_get_contents(__DIR__ . '/_files/' . $validFileName);
        $schemaPath = $this->_xsdSchemaPath . $schemaName;
        $actualResult = $this->_xsdValidator->validate($schemaPath, $xmlString);
        $this->assertEquals(array(), $actualResult);
    }

    /**
     * Data provider with valid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesValidXmlDataProvider()
    {
        return array(
            'product_options' => array('export.xsd', 'export_valid.xml'),
            'product_options_merged' => array('export_merged.xsd', 'export_merged_valid.xml')
        );
    }

    /**
     * Data provider with invalid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesExportOptionsDataProvider()
    {
        return include(__DIR__ . '/_files/invalidExportXmlArray.php');
    }


    /**
     * Data provider with invalid xml array according to schema
     */
    public function schemaCorrectlyIdentifiesInvalidExportMergedXmlDataProvider()
    {
        return include(__DIR__ . '/_files/invalidExportMergedXmlArray.php');
    }
}