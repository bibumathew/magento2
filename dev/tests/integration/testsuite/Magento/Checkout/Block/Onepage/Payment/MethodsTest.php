<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for \Magento\Checkout\Block\Onepage\Payment\Methods
 */
class Magento_Checkout_Block_Onepage_Payment_MethodsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Onepage\Payment\Methods
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = Mage::getSingleton('Magento\Core\Model\Layout')
            ->createBlock('Magento\Checkout\Block\Onepage\Payment\Methods');
    }

    public function testGetMethodTitleAndMethodLabelAfterHtml()
    {
        $expectedTitle = 'Free Method';
        $expectedLabel = 'Label After Html';
        $method = Mage::getModel('Magento\Payment\Model\Method\Free');

        $block = $this->_block->getLayout()->createBlock('Magento\Core\Block\Text')
            ->setMethodTitle($expectedTitle)
            ->setMethodLabelAfterHtml($expectedLabel);

        $this->assertEquals('No Payment Information Required', $this->_block->getMethodTitle($method));
        $this->_block->setChild('payment.method.free', $block);
        $actualTitle = $this->_block->getMethodTitle($method);
        $actualLabel = $this->_block->getMethodLabelAfterHtml($method);

        $this->assertEquals($expectedTitle, $actualTitle);
        $this->assertEquals($expectedLabel, $actualLabel);
    }
}
