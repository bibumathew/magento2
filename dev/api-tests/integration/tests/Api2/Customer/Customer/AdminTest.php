<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Magento
 * @package     Magento_Test
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for customer API2 by admin api user
 *
 * @category    Magento
 * @package     Magento_Test
 * @author      Magento Api Team <api-team@magento.com>
 */
class Api2_Customer_Customer_AdminTest extends Magento_Test_Webservice_Rest_Admin
{
    /**
     * Customer model instance
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $this->_customer = require dirname(__FILE__) . '/../../../../fixtures/Customer/Customer.php';
        $this->_customer->save();

        $this->addModelToDelete($this->_customer, true);
    }

    /**
     * Test create customer
     */
    public function testCreate()
    {
        $response = $this->callPost('customers/1', array());
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_METHOD_NOT_ALLOWED, $response->getStatus());
    }

    /**
     * Test retrieve existing customer data
     */
    public function testRetrieve()
    {
        $response = $this->callGet('customers/' . $this->_customer->getId());

        $this->assertEquals(Mage_Api2_Model_Server::HTTP_OK, $response->getStatus());

        $responseData = $response->getBody();
        $this->assertNotEmpty($responseData);

        foreach ($responseData as $field => $value) {
            $this->assertEquals($this->_customer->getData($field), $value);
        }
    }

    /**
     * Test retrieve not existing customer
     */
    public function testRetrieveUnavailableResource()
    {
        $response = $this->callGet('customers/invalid_id');
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_NOT_FOUND, $response->getStatus());
    }

    /**
     * Test update customer
     */
    public function testUpdate()
    {
        $putData = array(
            'firstname' => 'Salt',
            'lastname'  => 'Pepper',
            'email'     => 'newemail@example.com' . mt_rand()
        );
        $response = $this->callPut('customers/' . $this->_customer->getId(), $putData);

        $this->assertEquals(Mage_Api2_Model_Server::HTTP_OK, $response->getStatus());

        // Reload customer
        $this->_customer->load($this->_customer->getId());

        foreach ($putData as $field => $value) {
            $this->assertEquals($this->_customer->getData($field), $value);
        }
    }

    /**
     * Test update not existing customer
     */
    public function testUpdateUnavailableResource()
    {
        $response = $this->callPut('customers/invalid_id', array());
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_NOT_FOUND, $response->getStatus());
    }

    /**
     * Test delete customer
     */
    public function testDelete()
    {
        $response = $this->callDelete('customers/' . $this->_customer->getId());

        $this->assertEquals(Mage_Api2_Model_Server::HTTP_OK, $response->getStatus());

        /** @var $model Mage_Customer_Model_Customer */
        $model = Mage::getModel('customer/customer')->load($this->_customer->getId());
        $this->assertEmpty($model->getId());
    }

    /**
     * Test delete not existing customer
     */
    public function testDeleteUnavailableResource()
    {
        $response = $this->callDelete('customers/invalid_id');
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_NOT_FOUND, $response->getStatus());
    }
}
