<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Core_Model_EncryptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setHelperGetHashDataProvider
     */
    public function testSetHelperGetHash($input)
    {
        $objectManager = $this->getMock('Magento\ObjectManager');
        $objectManager->expects($this->once())
            ->method('get')
            ->with($this->stringContains('Data'))
            ->will($this->returnValue($this->getMock('Magento\Core\Helper\Data', array(), array(), '', false, false)));
        $coreConfig = $this->getMock('Magento_Core_Model_Config', array(), array(), '', false);

        /**
         * @var \Magento\Core\Model\Encryption
         */
        $model = new Magento_Core_Model_Encryption($objectManager, $coreConfig);
        $model->setHelper($input);
        $model->getHash('password', 1);
    }

    /**
     * @return array
     */
    public function setHelperGetHashDataProvider()
    {
        return array(
            'string' => array('Magento\Core\Helper\Data'),
            'object' => array($this->getMock('Magento\Core\Helper\Data', array(), array(), '', false, false)),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetHelperException()
    {
        $objectManager = $this->getMock('Magento\ObjectManager');
        $coreConfig = $this->getMock('Magento_Core_Model_Config', array(), array(), '', false);

        /**
         * @var \Magento\Core\Model\Encryption
         */
        $model = new Magento_Core_Model_Encryption($objectManager, $coreConfig);
        /** Mock object is not instance of Magento_Code_Helper_Data and should not pass validation */
        $input = $this->getMock('Magento\Code\Helper\Data', array(), array(), '', false);
        $model->setHelper($input);
    }
}
