<?php
/**
 * Test class for Enterprise_PageCache_Model_Processor
 *
 * {license_notice}
 *
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Enterprise_PageCache_Model_ProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Enterprise_PageCache_Model_Processor
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_restrictionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fpcCacheMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_designPackageMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subProcessorFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_placeholderFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_containerFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_environmentMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestIdentifierMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_designInfoMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_metadataMock;

    protected function setUp()
    {
        $this->_restrictionMock = $this->getMock('Enterprise_PageCache_Model_Processor_RestrictionInterface',
            array(), array(), '', false
        );
        $this->_fpcCacheMock = $this->getMock('Enterprise_PageCache_Model_Cache', array(), array(), '', false);
        $this->_designPackageMock = $this->getMock('Mage_Core_Model_Design_Package_Proxy',
            array(), array(), '', false
        );
        $this->_subProcessorFactoryMock = $this->getMock('Enterprise_PageCache_Model_Cache_SubProcessorFactory',
            array(), array(), '', false
        );
        $this->_placeholderFactoryMock = $this->getMock('Enterprise_PageCache_Model_Container_PlaceholderFactory',
            array(), array(), '', false
        );
        $this->_containerFactoryMock = $this->getMock('Enterprise_PageCache_Model_ContainerFactory',
            array(), array(), '', false
        );
        $this->_environmentMock = $this->getMock('Enterprise_PageCache_Model_Environment',
            array(), array(), '', false
        );
        $this->_requestIdentifierMock = $this->getMock('Enterprise_PageCache_Model_Request_Identifier',
            array(), array(), '', false
        );
        $this->_designInfoMock = $this->getMock('Enterprise_PageCache_Model_DesignPackage_Info',
            array(), array(), '', false
        );
        $this->_metadataMock = $this->getMock('Enterprise_PageCache_Model_Metadata', array(), array(), '', false);

        $this->_model = new  Enterprise_PageCache_Model_Processor(
            $this->_restrictionMock,
            $this->_fpcCacheMock,
            $this->_designPackageMock,
            $this->_subProcessorFactoryMock,
            $this->_placeholderFactoryMock,
            $this->_containerFactoryMock,
            $this->_environmentMock,
            $this->_requestIdentifierMock,
            $this->_designInfoMock,
            $this->_metadataMock
        );
    }

    public function testGetRequestId()
    {
        $this->_requestIdentifierMock->expects($this->once())
            ->method('getRequestId')->will($this->returnValue('test_id'));

        $this->assertEquals('test_id', $this->_model->getRequestId());
    }

    public function testGetRequestCacheId()
    {
        $this->_requestIdentifierMock->expects($this->once())
            ->method('getRequestCacheId')->will($this->returnValue('test_cache_id'));

        $this->assertEquals('test_cache_id', $this->_model->getRequestCacheId());
    }

    public function testisAllowed()
    {
        $this->_requestIdentifierMock->expects($this->once())
            ->method('getRequestId')->will($this->returnValue('test_id'));

        $this->_restrictionMock->expects($this->once())->method('isAllowed')
            ->with('test_id')->will($this->returnValue(true));


        $this->assertTrue($this->_model->isAllowed());
    }

    public function testGetRecentlyViewedCountCacheIdWithoutCookie()
    {
        $this->_environmentMock->expects($this->once())
            ->method('hasCookie')
            ->with(Mage_Core_Model_Store::COOKIE_NAME)
            ->will($this->returnValue(false));
        $expected = 'recently_viewed_count';

        $this->assertEquals($expected, $this->_model->getRecentlyViewedCountCacheId());
    }

    public function testGetRecentlyViewedCountCacheIdWithCookie()
    {
        $this->_environmentMock->expects($this->once())
            ->method('hasCookie')
            ->with(Mage_Core_Model_Store::COOKIE_NAME)
            ->will($this->returnValue(true));

        $this->_environmentMock->expects($this->once())
            ->method('getCookie')
            ->with(Mage_Core_Model_Store::COOKIE_NAME)
            ->will($this->returnValue('100'));

        $expected = 'recently_viewed_count_100';

        $this->assertEquals($expected, $this->_model->getRecentlyViewedCountCacheId());
    }

    public function testGetSessionInfoCacheIdWithoutCookie()
    {
        $this->_environmentMock->expects($this->once())
            ->method('hasCookie')
            ->with(Mage_Core_Model_Store::COOKIE_NAME)
            ->will($this->returnValue(false));
        $expected = 'full_page_cache_session_info';

        $this->assertEquals($expected, $this->_model->getSessionInfoCacheId());
    }

    public function testGetSessionInfoCacheIdWithCookie()
    {
        $this->_environmentMock->expects($this->once())
            ->method('hasCookie')
            ->with(Mage_Core_Model_Store::COOKIE_NAME)
            ->will($this->returnValue(true));

        $this->_environmentMock->expects($this->once())
            ->method('getCookie')
            ->with(Mage_Core_Model_Store::COOKIE_NAME)
            ->will($this->returnValue('100'));

        $expected = 'full_page_cache_session_info_100';

        $this->assertEquals($expected, $this->_model->getSessionInfoCacheId());
    }

    public function testAddGetRequestTag()
    {
        $tags = array(Enterprise_PageCache_Model_Processor::CACHE_TAG);
        $this->assertEquals($tags, $this->_model->getRequestTags());

        $this->_model->addRequestTag('some_tag');
        $tags[] = 'some_tag';
        $this->assertEquals($tags, $this->_model->getRequestTags());
    }

    public function testSetMetadata()
    {
        $testKey = 'test_key';
        $testValue = 'test_value';
        $this->_metadataMock->expects($this->once())->method('setMetadata')->with($testKey, $testValue);

        $this->_model->setMetadata($testKey, $testValue);
    }

    public function testGetMetadata()
    {
        $testKey = 'test_key';
        $testValue = 'test_value';

        $this->_metadataMock->expects($this->once())
            ->method('getMetadata')->with($testKey)->will($this->returnValue($testValue));

        $this->assertEquals($testValue, $this->_model->getMetadata($testKey));
    }

    public function testGetSetSubprocessor()
    {
        $this->assertNull($this->_model->getSubprocessor());
        $subProcessor = $this->getMock('Enterprise_PageCache_Model_Cache_SubProcessorInterface');
        $this->_model->setSubprocessor($subProcessor);
        $this->assertEquals($subProcessor, $this->_model->getSubprocessor());
    }

}
