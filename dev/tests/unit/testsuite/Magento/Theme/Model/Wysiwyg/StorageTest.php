<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Theme
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Storage model test
 */
namespace Magento\Theme\Model\Wysiwyg;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_storageRoot;

    /**
     * @var \Magento\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $_helperStorage;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var null|\Magento\Theme\Model\Wysiwyg\Storage
     */
    protected $_storageModel;

    /**
     * @var \Magento\Image\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryWrite;

    protected function setUp()
    {
        $this->_filesystem      = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $this->_helperStorage   = $this->getMock('Magento\Theme\Helper\Storage', array(), array(), '', false);
        $this->_objectManager   = $this->getMock('Magento\ObjectManager', array(), array(), '', false);
        $this->_imageFactory    = $this->getMock('Magento\Image\AdapterFactory', array(), array(), '', false);
        $this->directoryWrite   = $this->getMock('Magento\Filesystem\Directory\Write', array(), array(), '', false);

        $this->_filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->directoryWrite));

        $this->_storageModel = new \Magento\Theme\Model\Wysiwyg\Storage(
            $this->_filesystem,
            $this->_helperStorage,
            $this->_objectManager,
            $this->_imageFactory
        );

        $this->_storageRoot = \Magento\Filesystem::DIRECTORY_SEPARATOR . 'root';
    }

    protected function tearDown()
    {
        $this->_filesystem = null;
        $this->_helperStorage = null;
        $this->_objectManager = null;
        $this->_storageModel = null;
        $this->_storageRoot = null;
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::_createThumbnail
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::uploadFile
     */
    public function testUploadFile()
    {
        $uplaoder = $this->_prepareUploader();

        $uplaoder->expects($this->once())
            ->method('save')
            ->will($this->returnValue(array('not_empty')));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageType')
            ->will($this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE));

        /** Prepare filesystem */

        $this->directoryWrite->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));

        $this->directoryWrite->expects($this->once())
            ->method('isReadable')
            ->will($this->returnValue(true));

        /** Prepare image */

        $image = $this->getMock('Magento\Image\Adapter\Gd2', array(), array(), '', false);

        $image->expects($this->once())
            ->method('open')
            ->will($this->returnValue(true));

        $image->expects($this->once())
            ->method('keepAspectRatio')
            ->will($this->returnValue(true));

        $image->expects($this->once())
            ->method('resize')
            ->will($this->returnValue(true));

        $image->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $this->_imageFactory->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($image));

        /** Prepare session */

        $session = $this->getMock('Magento\Backend\Model\Session', array(), array(), '', false);

        $this->_helperStorage->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));

        $expectedResult = array(
            'not_empty',
            'cookie' => array(
                'name'     => null,
                'value'    => null,
                'lifetime' => null,
                'path'     => null,
                'domain'   => null
            )
        );

        $this->assertEquals($expectedResult, $this->_storageModel->uploadFile($this->_storageRoot));
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::uploadFile
     * @expectedException \Magento\Core\Exception
     */
    public function testUploadInvalidFile()
    {
        $uplaoder = $this->_prepareUploader();

        $uplaoder->expects($this->once())
            ->method('save')
            ->will($this->returnValue(null));

        $this->_storageModel->uploadFile($this->_storageRoot);
    }

    protected function _prepareUploader()
    {
        $uploader = $this->getMock('Magento\Core\Model\File\Uploader', array(), array(), '', false);

        $this->_objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($uploader));

        $uploader->expects($this->once())
            ->method('setAllowedExtensions')
            ->will($this->returnValue($uploader));

        $uploader->expects($this->once())
            ->method('setAllowRenameFiles')
            ->will($this->returnValue($uploader));

        $uploader->expects($this->once())
            ->method('setFilesDispersion')
            ->will($this->returnValue($uploader));

        return $uploader;
    }

    /**
     * @dataProvider booleanCasesDataProvider
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::createFolder
     */
    public function testCreateFolder($isWritable)
    {
        $newDirectoryName = 'dir1';
        $fullNewPath = $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . $newDirectoryName;

        $this->directoryWrite->expects($this->any())
            ->method('isWritable')
            ->with($this->_storageRoot)
            ->will($this->returnValue($isWritable));

        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($fullNewPath)
            ->will($this->returnValue(false));

        $this->_helperStorage->expects($this->once())
            ->method('getShortFilename')
            ->with($newDirectoryName)
            ->will($this->returnValue($newDirectoryName));

        $this->_helperStorage->expects($this->once())
            ->method('convertPathToId')
            ->with($fullNewPath)
            ->will($this->returnValue($newDirectoryName));

        $this->_helperStorage->expects($this->any())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));

        $expectedResult = array(
            'name'       => $newDirectoryName,
            'short_name' => $newDirectoryName,
            'path'       => \Magento\Filesystem::DIRECTORY_SEPARATOR . $newDirectoryName,
            'id'         => $newDirectoryName
        );

        $this->assertEquals(
            $expectedResult,
            $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot)
        );
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::createFolder
     * @expectedException \Magento\Core\Exception
     */
    public function testCreateFolderWithInvalidName()
    {
        $newDirectoryName = 'dir2!#$%^&';
        $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot);
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::createFolder
     * @expectedException \Magento\Core\Exception
     */
    public function testCreateFolderDirectoryAlreadyExist()
    {
        $newDirectoryName = 'mew';
        $fullNewPath = $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . $newDirectoryName;

        $this->directoryWrite->expects($this->any())
            ->method('isWritable')
            ->with($this->_storageRoot)
            ->will($this->returnValue(true));

        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($fullNewPath)
            ->will($this->returnValue(true));

        $this->_storageModel->createFolder($newDirectoryName, $this->_storageRoot);
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::getDirsCollection
     */
    public function testGetDirsCollection()
    {
        $dirs = array(
            $this->_storageRoot . '/dir1',
            $this->_storageRoot . '/dir2'
        );

        $this->directoryWrite->expects($this->any())
            ->method('isExist')
            ->with($this->_storageRoot)
            ->will($this->returnValue(true));

        $this->directoryWrite->expects($this->once())
            ->method('search')
            ->will($this->returnValue($dirs));

        $this->directoryWrite->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));

        $this->assertEquals($dirs, $this->_storageModel->getDirsCollection($this->_storageRoot));
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::getDirsCollection
     * @expectedException \Magento\Core\Exception
     */
    public function testGetDirsCollectionWrongDirName()
    {
        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($this->_storageRoot)
            ->will($this->returnValue(false));

        $this->_storageModel->getDirsCollection($this->_storageRoot);
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::getFilesCollection
     */
    public function testGetFilesCollection()
    {
        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($this->_storageRoot));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageType')
            ->will($this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT));

        $this->_helperStorage->expects($this->any())
            ->method('urlEncode')
            ->will($this->returnArgument(0));


        $paths = array(
            $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'font1.ttf',
            $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'font2.ttf'
        );

        $this->directoryWrite->expects($this->once())
            ->method('search')
            ->will($this->returnValue($paths));

        $this->directoryWrite->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));

        $result = $this->_storageModel->getFilesCollection();

        $this->assertCount(2, $result);
        $this->assertEquals('font1.ttf', $result[0]['text']);
        $this->assertEquals('font2.ttf', $result[1]['text']);
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::getFilesCollection
     */
    public function testGetFilesCollectionImageType()
    {
        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($this->_storageRoot));

        $this->_helperStorage->expects($this->once())
            ->method('getStorageType')
            ->will($this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE));

        $this->_helperStorage->expects($this->any())
            ->method('urlEncode')
            ->will($this->returnArgument(0));

        $paths = array(
            $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'picture1.jpg'
        );

        $this->directoryWrite->expects($this->once())
            ->method('search')
            ->will($this->returnValue($paths));

        $this->directoryWrite->expects($this->once())
            ->method('isFile')
            ->with($this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'picture1.jpg')
            ->will($this->returnValue(true));

        $result = $this->_storageModel->getFilesCollection();

        $this->assertCount(1, $result);
        $this->assertEquals('picture1.jpg', $result[0]['text']);
        $this->assertEquals('picture1.jpg', $result[0]['thumbnailParams']['file']);
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::getTreeArray
     */
    public function testTreeArray()
    {
        $currentPath = $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'dir';
        $dirs = array(
            $currentPath . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'dir_one',
            $currentPath . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'dir_two'
        );

        $expectedResult = array(
            array(
                'text' => pathinfo($dirs[0], PATHINFO_BASENAME),
                'id'   => $dirs[0],
                'cls'  => 'folder'
            ),
            array(
                'text' => pathinfo($dirs[1], PATHINFO_BASENAME),
                'id'   => $dirs[1],
                'cls'  => 'folder'
        ));

        $this->directoryWrite->expects($this->once())
            ->method('isExist')
            ->with($currentPath)
            ->will($this->returnValue(true));

        $this->directoryWrite->expects($this->once())
            ->method('search')
            ->will($this->returnValue($dirs));

        $this->directoryWrite->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));


        $this->_helperStorage->expects($this->once())
            ->method('getCurrentPath')
            ->will($this->returnValue($currentPath));

        $this->_helperStorage->expects($this->any())
            ->method('getShortFilename')
            ->will($this->returnArgument(0));

        $this->_helperStorage->expects($this->any())
            ->method('convertPathToId')
            ->will($this->returnArgument(0));

        $result = $this->_storageModel->getTreeArray();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::deleteFile
     */
    public function testDeleteFile()
    {
        $image = 'image.jpg';
        $storagePath = $this->_storageRoot;
        $imagePath = $storagePath . \Magento\Filesystem::DIRECTORY_SEPARATOR . $image;
        $thumbnailDir = $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR
            . \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY;

        $session = $this->getMock('Magento\Backend\Model\Session', array('getStoragePath'), array(), '', false);
        $session->expects($this->atLeastOnce())
            ->method('getStoragePath')
            ->will($this->returnValue($storagePath));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getSession')
            ->will($this->returnValue($session));

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('urlDecode')
            ->with($image)
            ->will($this->returnArgument(0));

        $this->directoryWrite->expects($this->at(0))
            ->method('getRelativePath')
            ->with($this->_storageRoot)
            ->will($this->returnValue($this->_storageRoot));

        $this->directoryWrite->expects($this->at(1))
            ->method('getRelativePath')
            ->with($this->_storageRoot . '/' . $image)
            ->will($this->returnValue($this->_storageRoot . '/' . $image));

        $this->directoryWrite->expects($this->any())
            ->method('delete')
            ->with($imagePath);

        $this->assertInstanceOf('Magento\Theme\Model\Wysiwyg\Storage', $this->_storageModel->deleteFile($image));
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::deleteDirectory
     */
    public function testDeleteDirectory()
    {
        $directoryPath = $this->_storageRoot . \Magento\Filesystem::DIRECTORY_SEPARATOR . '..'
            . \Magento\Filesystem::DIRECTORY_SEPARATOR . 'root';

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));

        $this->directoryWrite->expects($this->once())
            ->method('delete')
            ->with($directoryPath);

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    /**
     * @covers \Magento\Theme\Model\Wysiwyg\Storage::deleteDirectory
     * @expectedException \Magento\Core\Exception
     */
    public function testDeleteRootDirectory()
    {
        $directoryPath = $this->_storageRoot;

        $this->_helperStorage->expects($this->atLeastOnce())
            ->method('getStorageRoot')
            ->will($this->returnValue($this->_storageRoot));

        $this->_storageModel->deleteDirectory($directoryPath);
    }

    public function booleanCasesDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }
}
