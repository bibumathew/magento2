<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Adminhtml_Controller_IndexTest extends Mage_Backend_Utility_Controller
{
    /**
     * @covers Mage_Adminhtml_Controller_Index::globalSearchAction
     */
    public function testGlobalSearchAction()
    {
        $this->getRequest()->setParam('isAjax', 'true');
        $this->getRequest()->setPost('query', 'dummy');
        $this->dispatch('backend/admin/index/globalSearch');

        $actual = $this->getResponse()->getBody();
        $this->assertEquals(array(), json_decode($actual));
    }
}
