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
 * Test for skin changing observer
 *
 * @group module:Mage_DesignEditor
 */
class Mage_DesignEditor_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Model_Observer
     */
    protected $_observer;

    protected function setUp()
    {
        $this->_observer = new Mage_DesignEditor_Model_Observer;
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testApplyCustomSkin()
    {
        $newSkin = 'default/default/blank';
        $this->assertNotEquals($newSkin, Mage::getDesign()->getDesignTheme());

        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $session->setSkin($newSkin);
        $this->_observer->applyCustomSkin(new Varien_Event_Observer());
        $this->assertEquals($newSkin, Mage::getDesign()->getDesignTheme());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testApplyCustomSkinChangesNothingWhenNoSkin()
    {
        $currentSkin = Mage::getDesign()->getDesignTheme();
        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $this->assertEmpty($session->getSkin());
        $this->_observer->applyCustomSkin(new Varien_Event_Observer());
        $this->assertEquals($currentSkin, Mage::getDesign()->getDesignTheme());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testApplyCustomSkinDesignNotActive()
    {
        $newSkin = 'default/default/blank';
        $oldSkin = Mage::getDesign()->getDesignTheme();
        $this->assertNotEquals($newSkin, $oldSkin);

        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $session->setSkin($newSkin);

        $this->_observer->applyCustomSkin(new Varien_Event_Observer());
        $this->assertEquals($oldSkin, Mage::getDesign()->getDesignTheme());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testWrapHtmlWithBlockInfo()
    {
        $params = array(
            'name'   => 'block.name',
            'html'   => '<div>Any text</div>',
            'container' => 'Mage_Core_Block_Text_List'
        );
        $observerData = $this->_buildObserverData($params);
        $this->_observer->wrapHtmlWithBlockInfo($observerData);

        $wrappedHtml = $observerData->getTransport()->getHtml();
        $this->assertContains($params['html'], $wrappedHtml);
        $this->assertNotEquals($params['html'], $wrappedHtml);
    }

    /**
     * @param array $params
     * @param string $expectedHtml
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     * @dataProvider wrapHtmlWithBlockInfoDataProvider
     */
    public function testWrapHtmlWithBlockInfoNoWrapping($params, $expectedHtml)
    {
        $observerData = $this->_buildObserverData($params);
        $this->_observer->wrapHtmlWithBlockInfo($observerData);
        $this->assertEquals($expectedHtml, $observerData->getTransport()->getHtml());
    }

    public function wrapHtmlWithBlockInfoDataProvider()
    {
        return array(
            'body' => array(
                'params' => array(
                    'name' => 'block.name',
                    'html' => '<title>Title</title><body>Body</body>'
                ),
                'expectedHtml' => '<title>Title</title><body>Body</body>'
            ),
            'unknown_content' => array(
                'params' => array(
                    'name' => 'block.name',
                    'html' => 'Some content'
                ),
                'expectedHtml' => 'Some content'
            ),
            'vde_blocks' => array(
                'params' => array(
                    'name' => 'block.name',
                    'html' => '<div>Any text</div>',
                    'class' => 'Mage_DesignEditor_Block_That_Belongs_To_That_Module'
                ),
                'expectedHtml' => '<div>Any text</div>'
            )
        );
    }

    protected function _buildObserverData($params)
    {
        if (isset($params['class'])) {
            $block = $this->getMock('Mage_Core_Block_Template', null, array(), $params['class']);
        } else {
            $block = new Mage_Core_Block_Template;
        }
        $block->setNameInLayout($params['name'])
            ->setLayout(new Mage_Core_Model_Layout);

        if (isset($params['container'])) {
            $parentBlock = $this->getMock($params['container'], array('getType'));
            $parentBlock->expects(self::any())
                ->method('getType')
                ->will(new PHPUnit_Framework_MockObject_Stub_Return($params['container']));
            $block->setParentBlock($parentBlock);
        }

        $transport = new Varien_Object();
        $transport->setHtml($params['html']);

        $result = new Varien_Event_Observer;
        $result->setTransport($transport);
        $result->setBlock($block);
        return $result;
    }
}
