<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Core\App\Action;


class FormKeyValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_sessionMock = $this->getMock('Magento\Core\Model\Session', array('getFormKey'), array(), '', false);
        $this->_requestMock = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $this->_model = new \Magento\Core\App\Action\FormKeyValidator(
            $this->_sessionMock
        );
    }

    /**
     * @param string $formKey
     * param bool $expected
     * @dataProvider validateDataProvider
     */
    public function testValidate($formKey, $expected)
    {
        $this->_requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('form_key', null)
            ->will($this->returnValue($formKey));
        $this->_sessionMock->expects($this->once())->method('getFormKey')->will($this->returnValue('formKey'));
        $this->assertEquals($expected, $this->_model->validate($this->_requestMock));
    }

    public function validateDataProvider()
    {
        return array(
          'formKeyExist' => array('formKey', true),
          'formKeyNotEqualToFormKeyInSession' => array('formKeySession', false)
        );
    }
}