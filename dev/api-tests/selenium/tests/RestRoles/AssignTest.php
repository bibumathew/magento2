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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test assigning REST Role to admin User from Backend
 *
 * @method AdminUser_Helper adminUserHelper()
 * @method RestRoles_Helper restRolesHelper()
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RestRoles_AssignTest extends Mage_Selenium_TestCase
{
     /**
     * Rest Role Name
     * @var string
     */
    protected $_restRoleToBeDeleted;
    
     /**
     * <p>Log in to Backend.</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }
    /*
     * Function for deleting after test execution
     */
     protected function tearDown()
    {
        if ($this->_restRoleToBeDeleted) {
            $this->restRolesHelper()->deleteRestRole($this->_restRoleToBeDeleted);
            $this->_restRoleToBeDeleted = null;
        }
    }
    
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Permissions -> REST Roles.</p>
     */
    protected function assertPreConditions()
    {
        $this->navigate('manage_admin_users');
    }
     /**
     * <p>Test navigation.</p>
     * <p>Steps:</p>
     * <p>1. Verify that 'Back' button is present.</p>
     * <p>2. Verify that 'Save Role' button is present.</p>
     * <p>3. Verify that 'Delete Role' button is present.</p>
     * <p>4. Verify that 'Reset' button is present.</p>
     * <p>5. Verify that 'Role Info' tab is present.</p>
     * <p>6. Verify that 'Role API Resources' tab is present.</p>
     * <p>7. Verify that 'Role Users' tab is present.</p>
     *
     * @test
     */
    public function navigation()
    {
        $this->clickButton('add_new_admin_user');
        $this->assertTrue($this->restRolesHelper()->tabIsPresent('rest_role'),
            'There is no "REST Role" tab on the page');
    }
    
    /**
     *Test assigning 
     * 1. Browse to System -> Permissions -> Users.
     * 2. Select Admin user from preconditions.
     * 3. Open REST Roles tab.
     * 4. Set REST Role from precondition.
     * 5. Click Save User button.
     * 6. Open REST Role tab.
     * 
     * @test
     * @depends navigation
     */
    public function assignTest()
    {
        //create REST Role
        $this->navigate('manage_rest_roles');
        $restRoleData = $this->loadData('generic_rest_role');
        $this->_restRoleToBeDeleted = $restRoleData['rest_role_name'];
        $this->restRolesHelper()->createRestRole($restRoleData);
        //create User
        $this->navigate('manage_admin_users');
        $userData = $this->loadData('generic_admin_user', null, array('email', 'user_name'));
        $this->addParameter('id', '0');
        $this->adminUserHelper()->createAdminUser($userData);
        //steps
        $this->openTab('rest_role');
        $this->searchAndChoose(array('role_name' => $restRoleData['rest_role_name']), 'rest_user_roles');
        $this->clickButton('save_admin_user');
        //verifying
        $this->openTab('rest_role');
        $this->assertTrue($this->restRolesHelper()
            ->isGridItemChecked(array('role_name' => $restRoleData['rest_role_name']), 'rest_user_roles'),
            'There is no assigned roles'
        );
    }
}
