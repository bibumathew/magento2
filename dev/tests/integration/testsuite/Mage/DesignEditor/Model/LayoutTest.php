<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test for VDE layout
 */
class Mage_DesignEditor_Model_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Mage_DesignEditor_Model_Layout::sanitizeLayout
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Invalid block type: Namespace_Module_Block_NotSafe
     */
    public function testGenerateElements()
    {
        $layout = $this->_getLayoutWithTestUpdate();
        $layout->generateElements();

        $expectedXml = new Varien_Simplexml_Element(file_get_contents(__DIR__ . '/_files/expected_layout_update.xml'));
        $this->assertStringMatchesFormat($expectedXml->asNiceXml(), $layout->getNode()->asNiceXml());

        $layout = $this->_getLayoutWithTestUpdate(false);
        $layout->generateElements();
    }

    /**
     * Retrieve test layout with test layout update
     *
     * @param bool $isSanitizeBlocks
     * @param bool $enableWrapping
     * @return Mage_DesignEditor_Model_Layout
     */
    protected function _getLayoutWithTestUpdate($isSanitizeBlocks = true, $enableWrapping = true)
    {
        /** @var $layout Mage_DesignEditor_Model_Layout */
        $layout = Mage::getObjectManager()->create('Mage_DesignEditor_Model_Layout');
        $layout->setSanitizing($isSanitizeBlocks);
        $layout->setWrapping($enableWrapping);
        $layout->getUpdate()->addUpdate(file_get_contents(__DIR__ . '/_files/layout_update.xml'));
        $layout->generateXml();

        return $layout;
    }

    /**
     * @covers Mage_DesignEditor_Model_Layout::_renderBlock
     * @covers Mage_DesignEditor_Model_Layout::_renderContainer
     * @covers Mage_DesignEditor_Model_Layout::_wrapElement
     */
    public function testRenderElement()
    {
        $blockName = 'safe.block';
        $containerName = 'content';

        $blockWrapper = 'class="vde_element_wrapper vde_draggable vde_removable" data-name="' . $blockName . '"';
        $blockTitle = '<div class="vde_element_title">' . $blockName . '</div>';
        $containerWrapper = 'class="vde_element_wrapper vde_container" data-name="' . $containerName . '"';
        $containerTitle = '<div class="vde_element_title">' . ucfirst($containerName) . '</div>';

        $layout = $this->_getLayoutWithTestUpdate();
        $layout->generateElements();

        $actualContent = $layout->renderElement($blockName);
        $this->assertContains($blockWrapper, $actualContent);
        $this->assertContains($blockTitle, $actualContent);

        $actualContent = $layout->renderElement($containerName);
        $this->assertContains($containerWrapper, $actualContent);
        $this->assertContains($containerTitle, $actualContent);

        $layout = $this->_getLayoutWithTestUpdate(true, false);
        $layout->generateElements();

        $actualContent = $layout->renderElement($blockName);
        $this->assertNotContains($blockWrapper, $actualContent);
        $this->assertNotContains($blockTitle, $actualContent);

        $actualContent = $layout->renderElement($containerName);
        $this->assertNotContains($containerWrapper, $actualContent);
        $this->assertNotContains($containerTitle, $actualContent);
    }
}