<?php
/**
 * {license_notice}
 *
 * @category    Tools
 * @package     unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../') . '/tools/migration/Acl/Generator.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../') . '/tools/migration/Acl/FileWriter.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../') . '/tools/migration/Acl/Formatter.php';

/**
 * Tools_Migration_Acl test case
 */
class Tools_Migration_Acl_GeneratorSaveTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var $model Tools_Migration_Acl_Generator
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_fixturePath;

    /**
     * @var string
     */
    protected $_originFile;

    /**
     * @var string
     */
    protected $_aclFile;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_xmlFormatterMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileWriterMock;

    public function setUp()
    {
        $this->_xmlFormatterMock = $this->getMock('Tools_Migration_Acl_Formatter');
        $this->_fileWriterMock = $this->getMock('Tools_Migration_Acl_FileWriter');
        $this->_model = new Tools_Migration_Acl_Generator($this->_xmlFormatterMock, $this->_fileWriterMock);

        $this->_fixturePath = realpath(__DIR__) . DIRECTORY_SEPARATOR . '_files';
        $path = $this->_fixturePath . DIRECTORY_SEPARATOR . 'save' . DIRECTORY_SEPARATOR;

        $this->_originFile = $path . 'adminhtml.xml';
        $this->_aclFile = $path . 'adminhtml' . DIRECTORY_SEPARATOR . 'acl.xml';

        copy($this->_originFile . '.dist', $this->_originFile);

        $dom = new DOMDocument();
        $config = $dom->createElement('config');
        $dom->appendChild($config);
        $acl = $dom->createElement('acl');
        $config->appendChild($acl);
        $resources = $dom->createElement('resources');
        $acl->appendChild($resources);

        $resource1 = $dom->createElement('resource');
        $resource1->setAttribute('id', 'Map_Module::admin');
        $resources->appendChild($resource1);

        $resource2 = $dom->createElement('resource');
        $resource2->setAttribute('id', 'Module_One::customer');
        $resource2->setAttribute('title', 'Customers');
        $resource2->setAttribute('sortOrder', '40');
        $resource1->appendChild($resource2);

        $resource3 = $dom->createElement('resource');
        $resource3->setAttribute('id', 'Module_Two::group');
        $resource3->setAttribute('title', 'Customer Groups');
        $resource3->setAttribute('sortOrder', '10');
        $resource2->appendChild($resource3);

        $this->_model->setParsedDomList(array($this->_originFile => $dom));
    }

    public function tearDown()
    {
        unset($this->_model);
        unset($this->_xmlFormatterMock);
        unset($this->_fileWriterMock);
    }

    public function testSaveAclFiles()
    {
        $domList = $this->_model->getParsedDomList();
        $dom = clone $domList[$this->_originFile];
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $this->_xmlFormatterMock->expects($this->once())
            ->method('parseString')
            ->with($dom->saveXml(), array(
                'indent' => true,
                'input-xml' => true,
                'output-xml' => true,
                'add-xml-space' => false,
                'indent-spaces' => 4,
                'wrap' => 1000
            ))
            ->will($this->returnCallback(
                function($string) {
                    return 'formatted' . $string;
                }
            ));

        $this->_fileWriterMock->expects($this->once())
            ->method('write')
            ->with(
                $this->equalTo($this->_aclFile),
                $this->stringStartsWith('formatted')
            );

        $this->_model->saveAclFiles();
    }
}
