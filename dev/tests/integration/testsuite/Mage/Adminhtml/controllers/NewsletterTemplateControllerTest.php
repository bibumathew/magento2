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

class Mage_Adminhtml_Newsletter_TemplateControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @var Mage_Newsletter_Model_Template
     */
    protected $_model;

    public function setUp()
    {
        parent::setUp();
        $post = array('code'=>'test data',
                      'subject'=>'test data2',
                      'sender_email'=>'sender@email.com',
                      'sender_name'=>'Test Sender Name',
                      'text'=>'Template Content');
        $this->getRequest()->setPost($post);
        $this->_model = Mage::getModel('Mage_Newsletter_Model_Template');
    }

    public function tearDown()
    {
        /**
         * Unset messages
         */
        Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(true);
        unset($this->_model);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSaveActionCreateNewTemplateAndVerifySuccessMessage()
    {
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('id', $this->_model->getId());
        $this->dispatch('backend/admin/newsletter_template/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors());
        /**
         * Check that success message is set
         */
        $successMessages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        $this->assertCount(1, $successMessages, 'Success message was not set');
        $this->assertEquals('The newsletter template has been saved.', current($successMessages)->getCode());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/newsletter_sample.php
     */
    public function testSaveActionEditTemplateAndVerifySuccessMessage()
    {
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('id', $this->_model->getId());
        $this->dispatch('backend/admin/newsletter_template/save');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors());

        /**
         * Check that success message is set
         */
        $successMessages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        $this->assertCount(1, $successMessages, 'Success message was not set');
        $this->assertEquals('The newsletter template has been saved.', current($successMessages)->getCode());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSaveActionTemplateWithInvalidDataAndVerifySuccessMessage()
    {
        $post = array('code'=>'test data',
                      'subject'=>'test data2',
                      'sender_email'=>'sender_email.com',
                      'sender_name'=>'Test Sender Name',
                      'text'=>'Template Content');
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/admin/newsletter_template/save');

        /**
         * Check that errors was generated and set to session
         */
        Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors();

        /**
         * Check that success message is not set
         */
        $successMessages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        $this->assertEmpty($successMessages);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/newsletter_sample.php
     */
    public function testDeleteActionTemplateAndVerifySuccessMessage()
    {
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('id', $this->_model->getId());
        $this->dispatch('backend/admin/newsletter_template/delete');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors());

        /**
         * Check that success message is set
         */
        $successMessages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        $this->assertCount(1, $successMessages, 'Success message was not set');
        $this->assertEquals('The newsletter template has been deleted.', current($successMessages)->getCode());
    }
}
