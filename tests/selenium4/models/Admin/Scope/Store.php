<?php
/**
 * Admin_Scope_Store model
 *
 * @author Magento Inc.
 */
class Model_Admin_Scope_Store extends Model_Admin {
    /**
     * Loading configuration data for the testCase
     */
    public function loadConfigData()
    {
        parent::loadConfigData();

        $this->storeData = array(
            'name'         => Core::getEnvConfig('backend/scope/store/storename'),
            'siteName'     => Core::getEnvConfig('backend/scope/site/name'),
            'rootCategory' => Core::getEnvConfig('backend/managecategories/rootname'),
        );
    }

    /**
     * Create store sequence test
     *
     * @param array $params May contain the following params:
     * name, siteName, rootCategory
     */
    public function doCreate($params = array())
    {
        $storeData = $params ? $params : $this->storeData;

        $this->clickAndWait(
            $this->getUiElement("/admin/topmenu/system/managestores/link/openpage")
        );

        $this->setUiNamespace('admin/pages/system/scope/store');
        $this->clickAndWait($this->getUiElement("/admin/pages/system/scope/manage_stores/buttons/create_web_store"));
        //Fill all fields
        $this->select($this->getUiElement("selectors/site"), $storeData['siteName']);
        $this->type($this->getUiElement("inputs/name"), $storeData['name']);
        $this->select($this->getUiElement("selectors/root_category"), $storeData['rootCategory']);
        $this->clickAndWait($this->getUiElement("buttons/save"));

        // check for error message
        if ($this->waitForElement($this->getUiElement('/admin/messages/error'),1)) {
            $etext = $this->getText($this->getUiElement('/admin/messages/error'));
            $this->setVerificationErrors('doStoreCreate: ' . $etext);
        } else {
          // Check for success message
          if ($this->waitForElement($this->getUiElement('/admin/messages/success'),1)) {
            $this->printInfo('Store ' . $storeData['name'] . ' has been created');
          } else {
            $this->setVerificationErrors('doStoreCreate: no success message');
          }
        }
    }

    /**
     * Delete store
     *
     * @param array $params May contain the following params:
     * name, code
     */
    public function doDelete($params = array())
    {
        $this->printDebug('doStoreDelete started');
        $siteData = $params ? $params : $this->storeData;
        $name = $this->storeData['name'];

        $this->clickAndWait(
            $this->getUiElement("/admin/topmenu/system/managestores/link/openpage")
        );

        if ($this->doOpen($name)) {
            $this->setUiNamespace('/admin/pages/system/scope/store');
            //Delete site
            $this->clickAndWait($this->getUiElement('buttons/delete'));
            //Select No backup
            $this->waitForElement($this->getUiElement('/admin/pages/system/scope/create_backup/selectors/create_backup'),5);
            $this->select($this->getUiElement('/admin/pages/system/scope/create_backup/selectors/create_backup'),'label=No');
            //Delete Store
            $this->click($this->getUiElement('buttons/delete'));
            $this->waitForElement($this->getUiElement('/admin/pages/system/scope/manage_stores/elements/store_table'),130);

            // check for error message
            if ($this->waitForElement($this->getUiElement('/admin/messages/error'),1)) {
                $etext = $this->getText($this->getUiElement('/admin/messages/error'));
                $this->setVerificationErrors('doDelete: ' . $etext);
            } else {
              // Check for success message
              if ($this->waitForElement($this->getUiElement('/admin/messages/success'),1)) {
                $this->printInfo('Store ' . $name . ' has been deleted');
              } else {
                $this->setVerificationErrors('doStoreDelete: no success message');
              }
            }
        }
        $this->printDebug('doStoreDelete finished...');
    }

    /**
     * Open store from admin. If there are several stores with same storename - will opens first one
     * @param name, code
     * @return boolean
     */
    public function doOpen($params = array())
    {
        $this->printDebug('doOpenStore started');
        $userData = $params ? $params : $this->storeData;
        $name = $this->storeData['name'];
        $this->setUiNamespace('/admin/pages/system/scope/manage_stores');
        //Open ManageStores
        $this->clickAndWait(
            $this->getUiElement("/admin/topmenu/system/managestores/link/openpage")
        );

        // Filter users by name
        $this->click($this->getUiElement('buttons/reset_filter'));
        sleep(1);
        //Filter by username
        $this->type($this->getUiElement('filters/store_name'),$name);
        $this->clickAndWait($this->getUiElement('buttons/search'));
        sleep(1);
        //Open user with 'Store Name' == name
        if ($this->waitForElement($this->getUiElement('elements/no_records'),2)) {
          // Store not founded
          $this->printDebug('doOpenStore finished with false');
          return false;
        } else {
            //Open Store
            $this->clickAndWait($this->getUiElement('elements/body') . '//tr[1]/td[2]//a');
            $this->printInfo('Store ' . $name . ' opened');
            return true;
        }
    }
}
