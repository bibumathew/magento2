<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @magentoAppArea adminhtml
 */
class Magento_Backend_Model_Auth_SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento\Core\Model\Config\Scope')
            ->setCurrentScope(\Magento\Core\Model\App\Area::AREA_ADMINHTML);
        $this->_auth  = Mage::getModel('Magento\Backend\Model\Auth');
        $this->_model = Mage::getModel('Magento\Backend\Model\Auth\Session');
        $this->_auth->setAuthStorage($this->_model);
    }

    protected function tearDown()
    {
        $this->_model = null;
        Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento\Core\Model\Config\Scope')
            ->setCurrentScope(null);
    }

    /**
     * Disabled form security in order to prevent exit from the app
     * @magentoConfigFixture current_store admin/security/session_lifetime 100
     */
    public function testIsLoggedIn()
    {
        $this->_auth->login(
            Magento_TestFramework_Bootstrap::ADMIN_NAME, Magento_TestFramework_Bootstrap::ADMIN_PASSWORD);
        $this->assertTrue($this->_model->isLoggedIn());

        $this->_model->setUpdatedAt(time() - 101);
        $this->assertFalse($this->_model->isLoggedIn());
    }

    /**
     * Disabled form security in order to prevent exit from the app
     * @magentoConfigFixture current_store admin/security/session_lifetime 59
     */
    public function testIsLoggedInWithIgnoredLifetime()
    {
        $this->_auth->login(
            Magento_TestFramework_Bootstrap::ADMIN_NAME, Magento_TestFramework_Bootstrap::ADMIN_PASSWORD);
        $this->assertTrue($this->_model->isLoggedIn());

        $this->_model->setUpdatedAt(time() - 101);
        $this->assertTrue($this->_model->isLoggedIn());
    }
}
