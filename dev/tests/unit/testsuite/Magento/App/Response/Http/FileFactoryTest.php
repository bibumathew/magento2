<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\App\Response\Http;

class FileFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Response\Http\FileFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileSystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $this->_fileSystemMock = $this->getMock(
            'Magento\Filesystem', array('getFileSize', 'isFile'), array(), '', false
        );
        $this->_fileSystemMock->expects($this->any())->method('getFileSize')
            ->withAnyParameters()->will($this->returnValue(0));
        $this->_fileSystemMock->expects($this->any())->method('isFile')
            ->withAnyParameters()->will($this->returnValue(0));
        $this->_responseMock = $this->getMock(
            'Magento\App\Response\Http', array('setHeader', 'setRedirect'), array(), '', false
        );
        $this->_responseMock->expects($this->any())
            ->method('setHeader')
            ->will($this->returnValue($this->_responseMock));
        $this->_responseMock
            ->expects($this->any())
            ->method('setRedirect')
            ->will($this->returnValue($this->_responseMock));
        $this->_model = new \Magento\App\Response\Http\FileFactory(
            $this->_responseMock,
            $this->_fileSystemMock
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateIfContentDoesntHaveRequiredKeys()
    {
        $this->_model->create('fileName', array());
    }

    /**
     * @expectedException \Exception
     * @exceptedExceptionMessage File not found
     */
    public function testCreateIfFileNotExist()
    {
        $file = 'some_file';
        $content = array(
            'type' => 'filename',
            'value' => $file
        );
        $this->_fileSystemMock->expects($this->any())
            ->method('stat')
            ->will($this->returnValue(array('size' => 'string')));
        $this->_model->create('fileName', $content);
    }
}