<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Captcha\Model;

class CaptchaFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**@var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Captcha\Model\CaptchaFactory */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager', array(), array(), '', false);
        $this->_model = new \Magento\Captcha\Model\CaptchaFactory($this->_objectManagerMock);
    }

    public function testCreatePositive()
    {
        $captchaType = 'default';

        $defaultCaptchaMock = $this->getMock('Magento\Captcha\Model\DefaultModel', array(), array(), '', false);

        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Captcha\Model\\' . ucfirst($captchaType)))
            ->will($this->returnValue($defaultCaptchaMock));

        $this->assertEquals($defaultCaptchaMock, $this->_model->create($captchaType, 'form_id'));
    }

    public function testCreateNegative()
    {
        $captchaType = 'wrong_instance';

        $defaultCaptchaMock = $this->getMock('stdClass', array(), array(), '', false);

        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Captcha\Model\\' . ucfirst($captchaType)))
            ->will($this->returnValue($defaultCaptchaMock));

        $this->setExpectedException('InvalidArgumentException',
            'Magento\Captcha\Model\\' . ucfirst($captchaType) .
            ' does not implement \Magento\Captcha\Model\ModelInterface');

        $this->assertEquals($defaultCaptchaMock, $this->_model->create($captchaType, 'form_id'));
    }
}
