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
 * Test of theme customization model
 */
namespace Magento\View\Design\Theme;

class CustomizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Customization
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customizationPath;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $theme;

    protected function setUp()
    {
        $this->fileProvider = $this->getMock('Magento\View\Design\Theme\FileProviderInterface',
            array(), array(), '', false);
        $collectionFactory = $this->getMock('Magento\Core\Model\Resource\Theme\File\CollectionFactory',
            array('create'), array(), '', false);
        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->fileProvider));
        $this->customizationPath = $this->getMock(
            'Magento\View\Design\Theme\Customization\Path',
            array(),
            array(),
            '',
            false
        );
        $this->theme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('__wakeup', 'save', 'load'),
            array(),
            '',
            false
        );

        $this->model = new Customization($this->fileProvider, $this->customizationPath, $this->theme);
    }

    protected function tearDown()
    {
        $this->model = null;
        $this->fileProvider = null;
        $this->customizationPath = null;
        $this->theme = null;
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::getFiles
     * @covers \Magento\View\Design\Theme\Customization::__construct
     */
    public function testGetFiles()
    {
        $collection = $this->getMock('Magento\Core\Model\Resource\Theme\File\Collection', array(), array(), '', false);
        $collection->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array()));
        $this->fileProvider->expects($this->once())
            ->method('getCollection')
            ->with($this->theme)
            ->will($this->returnValue($collection));
        $this->assertEquals(array(), $this->model->getFiles());
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::getFilesByType
     */
    public function testGetFilesByType()
    {
        $type = 'sample-type';
        $collection = $this->getMock('Magento\Core\Model\Resource\Theme\File\Collection', array(), array(), '', false);
        $collection->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array()));
        $this->fileProvider->expects($this->once())
            ->method('getCollection')
            ->with($this->theme, array('file_type' => $type))
            ->will($this->returnValue($collection));
        $this->assertEquals(array(), $this->model->getFilesByType($type));
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::generateFileInfo
     */
    public function testGenerationOfFileInfo()
    {
        $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'getFileInfo'), array(), '', false);
        $file->expects($this->once())
            ->method('getFileInfo')
            ->will($this->returnValue(array('sample-generation')));
        $this->assertEquals(
            array(array('sample-generation')),
            $this->model->generateFileInfo(array($file))
        );
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::getCustomizationPath
     */
    public function testGetCustomizationPath()
    {
        $this->customizationPath->expects($this->once())
            ->method('getCustomizationPath')
            ->with($this->theme)
            ->will($this->returnValue('path'));
        $this->assertEquals('path', $this->model->getCustomizationPath());
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::getThemeFilesPath
     * @dataProvider getThemeFilesPathDataProvider
     * @param string $type
     * @param string $expectedMethod
     */
    public function testGetThemeFilesPath($type, $expectedMethod)
    {
        $this->theme->setData(array(
            'id'         => 123,
            'type'       => $type,
            'area'       => 'area51',
            'theme_path' => 'theme_path'
        ));
        $this->customizationPath->expects($this->once())
            ->method($expectedMethod)
            ->with($this->theme)
            ->will($this->returnValue('path'));
        $this->assertEquals('path', $this->model->getThemeFilesPath());
    }

    /**
     * @return array
     */
    public function getThemeFilesPathDataProvider()
    {
        return array(
            'physical' => array(\Magento\View\Design\ThemeInterface::TYPE_PHYSICAL, 'getThemeFilesPath'),
            'virtual'  => array(\Magento\View\Design\ThemeInterface::TYPE_VIRTUAL, 'getCustomizationPath'),
            'staging'  => array(\Magento\View\Design\ThemeInterface::TYPE_STAGING, 'getCustomizationPath'),
        );
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::getCustomViewConfigPath
     */
    public function testGetCustomViewConfigPath()
    {
        $this->customizationPath->expects($this->once())
            ->method('getCustomViewConfigPath')
            ->with($this->theme)
            ->will($this->returnValue('path'));
        $this->assertEquals('path', $this->model->getCustomViewConfigPath());
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::reorder
     * @dataProvider customFileContent
     */
    public function testReorder($sequence, $filesContent)
    {
        $files = array();
        $type = 'sample-type';
        $collection = $this->getMock('Magento\Core\Model\Resource\Theme\File\Collection', array(), array(), '', false);
        foreach ($filesContent as $fileContent) {
            $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'save'), array(), '', false);
            $file->expects($fileContent['isCalled'])
                ->method('save')
                ->will($this->returnSelf());
            $file->setData($fileContent['content']);
            $files[] = $file;
        }
        $collection->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($files));
        $this->fileProvider->expects($this->once())
            ->method('getCollection')
            ->with($this->theme, array('file_type' => $type))
            ->will($this->returnValue($collection));
        $this->assertInstanceOf(
            'Magento\View\Design\Theme\CustomizationInterface', $this->model->reorder($type, $sequence)
        );
    }

    /**
     * Reorder test content
     *
     * @return array
     */
    public function customFileContent()
    {
        return array(array(
            'sequence'     => array(3, 2, 1),
            'filesContent' => array(
                array(
                    'isCalled' => $this->once(),
                    'content'  => array(
                        'id'         => 1,
                        'theme_id'   => 123,
                        'file_path'  => 'css/custom_file1.css',
                        'content'    => 'css content',
                        'sort_order' => 1
                    )
                ),
                array(
                    'isCalled' => $this->never(),
                    'content'  => array(
                        'id'         => 2,
                        'theme_id'   => 123,
                        'file_path'  => 'css/custom_file2.css',
                        'content'    => 'css content',
                        'sort_order' => 1
                    )
                ),
                array(
                    'isCalled' => $this->once(),
                    'content'  => array(
                        'id'         => 3,
                        'theme_id'   => 123,
                        'file_path'  => 'css/custom_file3.css',
                        'content'    => 'css content',
                        'sort_order' => 5
                    )
                )
            )
        ));
    }

    /**
     * @covers \Magento\View\Design\Theme\Customization::delete
     */
    public function testDelete()
    {
        $collection = $this->getMock('Magento\Core\Model\Resource\Theme\File\Collection', array(), array(), '', false);
        $file = $this->getMock('Magento\Core\Model\Theme\File', array('__wakeup', 'delete'), array(), '', false);
        $file->expects($this->once())->method('delete')->will($this->returnSelf());
        $file->setData(array(
            'id'         => 1,
            'theme_id'   => 123,
            'file_path'  => 'css/custom_file1.css',
            'content'    => 'css content',
            'sort_order' => 1
        ));
        $collection->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($file)));
        $this->fileProvider->expects($this->once())
            ->method('getCollection')
            ->with($this->theme)
            ->will($this->returnValue($collection));

        $this->assertInstanceOf('Magento\View\Design\Theme\CustomizationInterface', $this->model->delete(array(1)));
    }
}
