<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Core_Model_Cache_Frontend_PoolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Cache\Frontend\Pool
     */
    protected $_model;

    /**
     * @dataProvider cacheBackendDataProvider
     */
    public function testGetCache($cacheBackendName)
    {
        $settings = array('backend' => $cacheBackendName);
        $this->_model = new \Magento\Core\Model\Cache\Frontend\Pool(
            Mage::getModel('Magento\Core\Model\Cache\Frontend\Factory'),
            $settings
        );


        $cache = $this->_model->get(\Magento\Core\Model\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID);
        $this->assertInstanceOf('Magento\Cache\FrontendInterface', $cache);
        $this->assertInstanceOf('Zend_Cache_Backend_Interface', $cache->getBackend());
    }

    public function cacheBackendDataProvider()
    {
        return array(
            array('sqlite'),
            array('memcached'),
            array('apc'),
            array('xcache'),
            array('eaccelerator'),
            array('database'),
            array('File'),
            array('')
        );
    }
}
