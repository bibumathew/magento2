<?php

class Admin_Scope_Store extends TestCaseAbstract
{

    /**
     * Setup procedure.
     * Initializes model and loads configuration
     */
    function setUp() {
        $this->model = $this->getModel('admin/scope/store');
        $this->setUiNamespace();
    }

    /**
     * Test store creation
     */
    function testStoreCreation() {
        if ($this->model->doLogin()) {
            $this->model->doDelete();
            $this->model->doCreate();
        }
        
    }
}
