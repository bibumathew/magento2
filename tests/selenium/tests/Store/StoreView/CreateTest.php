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
 * Test creation new store view
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Store_StoreView_CreateTest extends Mage_Selenium_TestCase {

    /**
     * Preconditions:
     *
     * Log in to Backend.
     *
     * Navigate to System -> Manage Stores
     */
    protected function assertPreConditions()
    {
        $this->assertTrue($this->loginAdminUser());
        $this->assertTrue($this->admin());
        $this->assertTrue($this->navigate('manage_stores'));
    }

    /**
     * 
     */
    public function test_Navigation()
    {
        $this->assertTrue($this->clickButton('create_store_view'),
                'There is no "Create Store View" button on the page');
        $this->assertTrue($this->navigated('new_store_view'),
                'Wrong page is displayed');
        $this->assertTrue($this->controlIsPresent('button', 'back'),
                'There is no "Back" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'save_store_view'),
                'There is no "Save" button on the page');
        $this->assertTrue($this->controlIsPresent('button', 'reset'),
                'There is no "Reset" button on the page');
    }

    /**
     * Create Store View. Fill in only reqired fields.
     *
     * Steps:
     * 
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in reqired fields.
     * 
     * 3. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is created.
     * 
     * Success Message is displayed
     */
    public function test_WithRequiredFieldsOnly()
    {
        $this->clickButton('create_store_view');
        $this->fillForm($this->loadData('generic_store_view', NULL, NULL));
        $this->clickButton('save_store_view');
        $this->assertFalse($this->errorMessage(), $this->messages);
        $this->assertTrue($this->navigated('manage_stores'),
                'After successful creation store view should be redirected to Manage Stores page');
        $this->assertTrue($this->successMessage('success_saved_store_view'),
                'No success message is displayed');
    }

    /**
     * Create Store View. Fill in all reqired fields except the field "Name" .
     *
     * Steps:
     *
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in fields.
     *
     * 3. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is not created.
     *
     * Error Message is displayed.
     */
    public function test_WithRequiredFieldsEmpty_EmptyName()
    {
        $this->clickButton('create_store_view');
        $this->fillForm($this->loadData('generic_store_view',
                        array('store_view_name' => null), 'store_view_code'));
        $this->clickButton('save_store_view', FALSE);
        $this->assertTrue($this->errorMessage('empty_reqired_field'),
                'No error message is displayed');
        $this->assertFalse($this->successMessage(), $this->messages);
    }

    /**
     * Create Store View. Fill in all reqired fields except the field "Code" .
     *
     * Steps:
     *
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in fields.
     *
     * 3. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is not created.
     *
     * Error Message is displayed.
     */
    public function test_WithRequiredFieldsEmpty_EmptyCode()
    {
        $this->clickButton('create_store_view');
        $this->fillForm($this->loadData('generic_store_view',
                        array('store_view_code' => null), NULL));
        $this->clickButton('save_store_view', FALSE);
        $this->assertTrue($this->errorMessage('empty_reqired_field'),
                'No error message is displayed');
        $this->assertFalse($this->successMessage(), $this->messages);
    }

    /**
     * Create Store View. Fill in only reqired fields. Use max long values for fields 'Name' and 'Code'
     *
     * Steps:
     *
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in required fields by long value alpha-numeric data.
     *
     * 3. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is created. Success Message is displayed.
     *
     * Length of field "Name" is 255 characters. Length of field "Code" is 32 characters.
     */
    public function test_WithLongValues()
    {
        $this->clickButton('create_store_view');
        $longValues = array(
            'store_view_name' => $this->generate('string', 255, ':alnum:'),
            'store_view_code' => $this->generate('string', 32, array(':lower:', ':digit:'))
        );
        $this->fillForm($this->loadData('generic_store_view', $longValues, NULL));
        $this->clickButton('save_store_view');
        $this->assertFalse($this->errorMessage(), $this->messages);
        $this->assertTrue($this->navigated('manage_stores'),
                'After successful creation store view should be redirected to Manage Stores page');
        $this->assertTrue($this->successMessage('success_saved_store_view'),
                'No success message is displayed');
        // @TODO
        //$this->searchAndOpen($longValues);
        //$xpathName = $uipage->getMainForm()->getTab('account_information')->getFieldset('password')->getFields();
        //$xpathCode = $this->_getUimapData('admin/edit_store_view/uimap/form/fieldset_store_view_info/fields/store_view_code');
        //$this->assertEquals(strlen($this->getValue($xpathName)), 255);
        //$this->assertEquals(strlen($this->getValue($xpathCode)), 32);
    }

    /**
     * Create Store View. Fill in field 'Name' by using special characters.
     *
     * Steps:
     *
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in 'Name' field by special characters.
     *
     * 3. Fill other required fields by regular data.
     *
     * 4. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is created.
     *
     * Success Message is displayed
     */
    public function test_WithSpecialCharacters_InName()
    {
        $this->clickButton('create_store_view');
        $this->fillForm($this->loadData('generic_store_view',
                        array('store_view_name' => $this->generate('string', 32, ':punct:')),
                        'store_view_code'));
        $this->clickButton('save_store_view');
        $this->assertFalse($this->errorMessage(), $this->messages);
        $this->assertTrue($this->successMessage('success_saved_store_view'),
                'No success message is displayed');
        $this->assertTrue($this->navigated('manage_stores'),
                'After successful creation store view should be redirected to Manage Stores page');
    }

    /**
     * Create Store View.  Fill in field 'Code' by using special characters.
     *
     * Steps:
     *
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in 'Code' field by special characters.
     *
     * 3. Fill other required fields by regular data.
     *
     * 4. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is not created.
     *
     * Error Message is displayed.
     */
    public function test_WithSpecialCharacters_InCode()
    {
        $this->clickButton('create_store_view');
        $this->fillForm($this->loadData('generic_store_view',
                        array('store_view_code' => $this->generate('string', 32, ':punct:')), NULL));
        $this->clickButton('save_store_view');
        $this->assertTrue($this->errorMessage('wrong_store_view_code'),
                'No error message is displayed');
        $this->assertFalse($this->successMessage(), $this->messages);
    }

    /**
     * Create Store View.  Fill in field 'Code' by using wrong values.
     *
     * Steps:
     *
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in 'Code' field by wrong value.
     *
     * 3. Fill other required fields by regular data.
     *
     * 4. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is not created.
     *
     * Error Message is displayed.
     *
     * @dataProvider data_InvalidCode
     */
    public function test_WithInvalidCode($invalidCode)
    {
        $this->clickButton('create_store_view');
        $this->fillForm($this->loadData('generic_store_view', $invalidCode, null));
        $this->clickButton('save_store_view');
        $this->assertTrue($this->errorMessage('wrong_store_view_code'),
                'No error message is displayed');
        $this->assertFalse($this->successMessage(), $this->messages);
    }

    public function data_InvalidCode()
    {
        return array(
            array(array('store_view_code' => 'invalid code')),
            array(array('store_view_code' => 'Invalid_code2')),
            array(array('store_view_code' => '2invalid_code2'))
        );
    }

    /**
     * Create Store View.  Fill in field 'Code' by using code that already exist.
     *
     * Steps:
     *
     * 1. Click 'Create Store View' button.
     *
     * 2. Fill in 'Code' field by using code that already exist.
     *
     * 3. Fill other required fields by regular data.
     *
     * 4. Click 'Save Store View' button.
     *
     * Expected result:
     *
     * Store View is not created.
     *
     * Error Message is displayed.
     *
     * @depends test_WithRequiredFieldsOnly
     */
    public function test_WithCodeThatAlreadyExists()
    {
        $this->clickButton('create_store_view');
        $this->fillForm($this->loadData('generic_store_view', NULL, NULL));
        $this->clickButton('save_store_view');
        $this->assertTrue($this->errorMessage('store_view_code_exist'),
                'No error message is displayed');
        $this->assertFalse($this->successMessage(), $this->messages);
    }

}