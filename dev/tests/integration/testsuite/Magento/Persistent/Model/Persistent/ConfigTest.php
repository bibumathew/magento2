<?php
/**
 * \Magento\Persistent\Model\Persistent\Config
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Persistent\Model\Persistent;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Persistent\Config
     */
    protected $_model;

    /** @var  \Magento\ObjectManager */
    protected $_objectManager;

    public function setUp()
    {
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                'Magento\Filesystem\DirectoryList',
                array(
                    'root' => \Magento\Filesystem\DirectoryList::ROOT,
                    'uris' => array(),
                    'dirs' => array(
                        \Magento\Filesystem\DirectoryList::MODULES => dirname(__DIR__)
                    ),
                )
            );
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Filesystem', array('directoryList' => $directoryList));

        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create(
            'Magento\Persistent\Model\Persistent\Config',
            array('filesystem' => $filesystem)
        );
    }

    public function testCollectInstancesToEmulate()
    {
        $this->_model->setConfigFilePath(__DIR__ . '/_files/persistent.xml');
        $result = $this->_model->collectInstancesToEmulate();
        $expected = include '_files/expectedArray.php';
        $this->assertEquals($expected, $result);
    }

    public function testGetBlockConfigInfo()
    {
        $this->_model->setConfigFilePath(__DIR__ . '/_files/persistent.xml');
        $blocks = $this->_model->getBlockConfigInfo('Magento\Sales\Block\Reorder\Sidebar');
        $expected = include '_files/expectedBlocksArray.php';
        $this->assertEquals($expected, $blocks);
    }

    public function testGetBlockConfigInfoNotConfigured()
    {
        $this->_model->setConfigFilePath(__DIR__ . '/_files/persistent.xml');
        $blocks = $this->_model->getBlockConfigInfo('Magento\Catalog\Block\Product\Compare\ListCompare');
        $this->assertEquals(array(), $blocks);
    }

}
