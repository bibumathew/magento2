<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Core\Model\App;

class EmulationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\App\Emulation
     */
    protected $_model;

    /**
     * @covers \Magento\Core\Model\App\Emulation::startEnvironmentEmulation
     * @covers \Magento\Core\Model\App\Emulation::stopEnvironmentEmulation
     */
    public function testEnvironmentEmulation()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\App\Emulation');
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\View\DesignInterface')
            ->setArea(\Magento\Core\Model\App\Area::AREA_ADMINHTML);

        $initialEnvInfo = $this->_model->startEnvironmentEmulation(1);
        $initialDesign = $initialEnvInfo->getInitialDesign();
        $this->assertEquals(\Magento\Core\Model\App\Area::AREA_ADMINHTML, $initialDesign['area']);
        $this->assertEquals(\Magento\Core\Model\App\Area::AREA_FRONTEND, $design->getArea());

        $this->_model->stopEnvironmentEmulation($initialEnvInfo);
        $this->assertEquals(\Magento\Core\Model\App\Area::AREA_ADMINHTML, $design->getArea());
    }
}
