<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Adminhtml_System_StoreControllerTest extends Mage_Backend_Utility_Controller
{
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/system_store/index');

        $response = $this->getResponse()->getBody();
        $this->assertSelectRegExp('#add', '/Create Website/', 1, $response);
        $this->assertSelectCount('#add_group', 1, $response);
        $this->assertSelectCount('#add_store', 1, $response);
    }

    /**
     * @magentoConfigFixture limitations/store_group 1
     */
    public function testIndexActionStoreGroupRestricted()
    {
        $this->dispatch('backend/admin/system_store/index');
        $response = $this->getResponse()->getBody();
        $this->assertSelectCount('#add_group', 0, $response);
        $this->assertNotContains('You are using the maximum number of stores allowed.', $response);
    }
}
