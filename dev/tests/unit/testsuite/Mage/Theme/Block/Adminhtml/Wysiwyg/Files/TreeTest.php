<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Theme
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Theme_Block_Adminhtml_Wysiwyg_Files_TreeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var Mage_Theme_Helper_Storage|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperStorage;

    /**
     * @var Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Tree|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesTree;

    public function setUp()
    {
        $this->_helperStorage = $this->getMock('Mage_Theme_Helper_Storage', array(), array(), '', false);
        $this->_urlBuilder = $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false);

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $constructArguments =  $objectManagerHelper->getConstructArguments(
            'Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Content',
            array('urlBuilder'    => $this->_urlBuilder)
        );
        $this->_filesTree = $this->getMock(
            'Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Tree', array('helper'), $constructArguments
        );

        $this->_filesTree->expects($this->any())
            ->method('helper')
            ->with('Mage_Theme_Helper_Storage')
            ->will($this->returnValue($this->_helperStorage));
    }

    public function testGetTreeLoaderUrl()
    {
        $requestParams = array(
            Mage_Theme_Helper_Storage::PARAM_THEME_ID     => 1,
            Mage_Theme_Helper_Storage::PARAM_CONTENT_TYPE => Mage_Theme_Model_Wysiwyg_Storage::TYPE_IMAGE,
            Mage_Theme_Helper_Storage::PARAM_NODE         => 'root'
        );
        $expectedUrl = 'some_url';

        $this->_helperStorage->expects($this->once())
            ->method('getRequestParams')
            ->will($this->returnValue($requestParams));

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/treeJson', $requestParams)
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_filesTree->getTreeLoaderUrl());
    }
}