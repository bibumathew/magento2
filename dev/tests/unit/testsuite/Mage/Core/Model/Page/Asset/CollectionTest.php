<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Core_Model_Page_Asset_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Page_Asset_Collection
     */
    protected $_object;

    /**
     * @var Mage_Core_Model_Page_Asset_AssetInterface
     */
    protected $_asset;

    protected function setUp()
    {
        $this->_object = new Mage_Core_Model_Page_Asset_Collection();
        $this->_asset = new Mage_Core_Model_Page_Asset_Remote('http://127.0.0.1/magento/test.css');
        $this->_object->add('asset', $this->_asset);
    }

    public function testAdd()
    {
        $assetNew = new Mage_Core_Model_Page_Asset_Remote('http://127.0.0.1/magento/test.js');
        $this->_object->add('asset_new', $assetNew);
        $this->assertSame(array('asset' => $this->_asset, 'asset_new' => $assetNew), $this->_object->getAll());
    }

    public function testHas()
    {
        $this->assertTrue($this->_object->has('asset'));
        $this->assertFalse($this->_object->has('non_existing_asset'));
    }

    public function testAddSameInstance()
    {
        $this->_object->add('asset_clone', $this->_asset);
        $this->assertSame(array('asset' => $this->_asset, 'asset_clone' => $this->_asset), $this->_object->getAll());
    }

    public function testAddOverrideExisting()
    {
        $assetOverridden = new Mage_Core_Model_Page_Asset_Remote('http://127.0.0.1/magento/test_overridden.css');
        $this->_object->add('asset', $assetOverridden);
        $this->assertSame(array('asset' => $assetOverridden), $this->_object->getAll());
    }

    public function testRemove()
    {
        $this->_object->remove('asset');
        $this->assertSame(array(), $this->_object->getAll());
    }

    public function testGetAll()
    {
        $this->assertSame(array('asset' => $this->_asset), $this->_object->getAll());
    }
}