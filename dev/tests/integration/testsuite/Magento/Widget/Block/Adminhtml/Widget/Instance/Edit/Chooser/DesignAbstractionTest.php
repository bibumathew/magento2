<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Widget
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */


namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * @magentoAppArea adminhtml
 */
class DesignAbstractionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\DesignAbstraction|
     *      \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $layoutUtility = new \Magento\Core\Utility\Layout($this);
        $appState = $objectManager->get('Magento\App\State');
        $appState->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $processorMock = $this->getMock(
            'Magento\View\Layout\Processor', array('isPageLayoutDesignAbstraction'), array(), '', false
        );
        $processorMock->expects($this->exactly(2))
            ->method('isPageLayoutDesignAbstraction')
            ->will($this->returnCallback(
                    function ($abstraction) {
                        return $abstraction['design_abstraction'] === 'page_layout';
                    }
                ));
        $processorFactoryMock = $this->getMock(
            'Magento\View\Layout\ProcessorFactory', array('create'), array(), '', false
        );
        $processorFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnCallback(
                    function ($data) use ($processorMock, $layoutUtility) {
                        return ($data === array())
                            ? $processorMock
                            : $layoutUtility->getLayoutUpdateFromFixture(glob(__DIR__ . '/_files/layout/*.xml'));

                    }
                ));

        $this->_block = new DesignAbstraction(
            $objectManager->get('Magento\View\Element\Template\Context'),
            $processorFactoryMock,
            $objectManager->get('Magento\Core\Model\Resource\Theme\CollectionFactory'),
            $appState,
            array(
                'name'  => 'design_abstractions',
                'id'    => 'design_abstraction_select',
                'class' => 'design-abstraction-select',
                'title' => 'Design Abstraction Select',
            )
        );
    }

    public function testToHtml()
    {
        $this->assertXmlStringEqualsXmlFile(
            __DIR__ . '/_files/design-abstraction_select.html',
            $this->_block->toHtml()
        );
    }
}
