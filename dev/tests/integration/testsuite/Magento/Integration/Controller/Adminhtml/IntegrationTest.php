<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 *
 */
namespace Magento\Integration\Controller\Adminhtml;

/**
 * \Magento\Integration\Controller\Adminhtml\Integration
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class IntegrationTest extends \Magento\Backend\Utility\Controller
{
    /** @var \Magento\Integration\Model\Integration  */
    private $_integration;

    protected function setUp()
    {
        parent::setUp();
        $this->_createDummyIntegration();
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/admin/integration/index');
        $response = $this->getResponse()->getBody();

        $this->assertContains('Integrations', $response);
        $this->assertSelectCount('#integrationGrid', 1, $response);
    }

    public function testNewAction()
    {
        $this->dispatch('backend/admin/integration/new');
        $response = $this->getResponse()->getBody();

        $this->assertEquals('new', $this->getRequest()->getActionName());
        $this->assertContains('entry-edit form-inline', $response);
        $this->assertContains('New Integration', $response);
        $this->assertSelectCount('#integration_properties_base_fieldset', 1, $response);
    }

    public function testEditAction()
    {
        $integrationId = $this->_integration->getId();
        $this->getRequest()->setParam('id', $integrationId);
        $this->dispatch('backend/admin/integration/edit');
        $response = $this->getResponse()->getBody();
        $saveLink = 'integration/save/';
            
        $this->assertContains('entry-edit form-inline', $response);
        $this->assertContains('Edit &quot;'. $this->_integration->getName() .'&quot; Integration', $response);
        $this->assertContains($saveLink, $response);
        $this->assertSelectCount('#integration_properties_base_fieldset', 1, $response);
        $this->assertSelectCount('#integration_edit_tabs_info_section_content', 1, $response);
    }

    public function testSaveActionUpdateIntegration()
    {
        $integrationId = $this->_integration->getId();
        $integrationName = $this->_integration->getName();
        $this->getRequest()->setParam('id', $integrationId);
        $url = 'http://magento.ll/endpoint_url';
        $this->getRequest()->setPost(array(
                'name' => $integrationName,
                'email' => 'test@magento.com',
                'authentication' => '1',
                'endpoint' => $url
        ));
        $this->dispatch('backend/admin/integration/save');
        $this->assertSessionMessages(
            $this->equalTo(array("The integration '$integrationName' has been saved.")),
            \Magento\Message\InterfaceMessage::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/integration/index/'));
    }

    public function testSaveActionNewIntegration()
    {
        $url = 'http://magento.ll/endpoint_url';
        $integrationName = md5(rand());
        $this->getRequest()->setPost(array(
            'name' => $integrationName,
            'email' => 'test@magento.com',
            'authentication' => '1',
            'endpoint' => $url
        ));
        $this->dispatch('backend/admin/integration/save');

        $this->assertSessionMessages(
            $this->equalTo(array("The integration '$integrationName' has been saved.")),
            \Magento\Message\InterfaceMessage::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/integration/index/'));
    }

    /**
     * Creates a dummy integration for use in dispatched methods under testing
     */
    private function _createDummyIntegration()
    {
        /** @var $factory \Magento\Integration\Model\Integration\Factory */
        $factory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Integration\Model\Integration\Factory');
        $this->_integration = $factory->create()
            ->setName(md5(rand()))
            ->save();
    }
}
