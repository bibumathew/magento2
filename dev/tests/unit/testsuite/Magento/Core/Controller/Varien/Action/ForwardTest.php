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
 * Test class \Magento\Core\Controller\Varien\Action\Forward
 */
class Magento_Core_Controller_Varien_Action_ForwardTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Controller\Varien\Action\Forward
     */
    protected $_object = null;

    /**
     * @var \Magento\Core\Controller\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Core\Controller\Response\Http
     */
    protected $_response;

    protected function setUp()
    {
        $helperMock = $this->getMock('Magento_Backend_Helper_Data', array(), array(),
            'Magento_Backend_Helper_DataProxy', false);
        $this->_request  = new Magento_Core_Controller_Request_Http($helperMock);
        $this->_response = new Magento_Core_Controller_Response_Http(
            $this->getMock('Magento_Core_Model_Event_Manager', array(), array(), '', false)
        );

        $this->_object = new \Magento\Core\Controller\Varien\Action\Forward($this->_request, $this->_response);
    }

    protected function tearDown()
    {
        unset($this->_object);
        unset($this->_request);
        unset($this->_response);
    }

    /**
     * Test that \Magento\Core\Controller\Varien\Action\Forward::dispatch() does not change dispatched flag
     */
    public function testDispatch()
    {
        $this->_request->setDispatched(true);
        $this->assertTrue($this->_request->isDispatched());
        $this->_object->dispatch('any action');
        $this->assertFalse($this->_request->isDispatched());
    }
}
