<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Backend config model
 * Used to save configuration
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Backend_Model_Config extends Varien_Object
{
    /**
     * Event dispatcher
     *
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * System configuration structure
     *
     * @var Mage_Backend_Model_Config_Structure
     */
    protected $_configStructure;

    /**
     * Application config
     *
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * Global factory
     *
     * @var Mage_Core_Model_Config
     */
    protected $_objectFactory;

    /**
     * TransactionFactory
     *
     * @var Mage_Core_Model_Resource_Transaction_Factory
     */
    protected $_transactionFactory;

    /**
     * Global Application
     *
     * @var Mage_Core_Model_App
     */
    protected $_application;

    /**
     * @param Mage_Core_Model_App $application
     * @param Mage_Core_Model_Config $config
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Config_Structure $configStructure
     * @param Mage_Core_Model_Resource_Transaction_Factory $transactionFactory
     */
    public function __construct(
        Mage_Core_Model_App $application,
        Mage_Core_Model_Config $config,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Config_Structure $configStructure,
        Mage_Core_Model_Resource_Transaction_Factory $transactionFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_configStructure = $configStructure;
        $this->_transactionFactory = $transactionFactory;
        $this->_appConfig = $config;
        $this->_application = $application;
    }

    /**
     * Save config section
     * Require set: section, website, store and groups
     *
     * @return Mage_Backend_Model_Config
     */
    public function save()
    {
        $this->_validate();
        $this->_getScope();

        $this->_eventManager->dispatch('model_config_data_save_before', array('object' => $this));

        $sectionId = $this->getSection();
        $website = $this->getWebsite();
        $store   = $this->getStore();
        $groups  = $this->getGroups();
        $scope   = $this->getScope();
        $scopeId = $this->getScopeId();

        if (empty($groups)) {
            return $this;
        }

        $oldConfig = $this->_getConfig(true);

        $deleteTransaction = $this->_transactionFactory->create();
        /* @var $deleteTransaction Mage_Core_Model_Resource_Transaction */
        $saveTransaction = $this->_transactionFactory->create();
        /* @var $saveTransaction Mage_Core_Model_Resource_Transaction */

        // Extends for old config data
        $oldConfigAdditionalGroups = array();
        $mappedFields = array();

        foreach ($groups as $groupId => $groupData) {
            /**
             * Map field names if they were cloned
             */
            /** @var $group Mage_Backend_Model_Config_Structure_Element_Group */
            $group = $this->_configStructure->getElementByPathParts(array($sectionId, $groupId));

            if ($group->shouldCloneFields()) {
                $cloneModel = $group->getCloneModel();
                $mappedFields = array();

                /** @var $field Mage_Backend_Model_Config_Structure_Element_Field */
                foreach ($group->getChildren() as $field) {
                    foreach ($cloneModel->getPrefixes() as $prefix) {
                        $mappedFields[$prefix['field'] . $field->getId()] = $field->getId();
                    }
                }
            }
            // set value for group field entry by fieldname
            // use extra memory
            $fieldsetData = array();
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $fieldsetData[$fieldId] = (is_array($fieldData) && isset($fieldData['value']))
                    ? $fieldData['value'] : null;
            }

            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                /** @var $field Mage_Backend_Model_Config_Structure_Element_Field */
                $field = $this->_configStructure->getElementByPathParts(array($sectionId, $group->getId(), $fieldId));

                /** @var Mage_Core_Model_Config_Data $backendModel  */
                $backendModel = $this->_getBackendModel($field, $group, $mappedFields, $fieldId, $sectionId);

                if ($group->shouldCloneFields() && isset($mappedFields[$fieldId])) {
                    $field = $this->_configStructure->getElementByPathParts(
                        array($sectionId, $group->getId(), $mappedFields[$fieldId])
                    );
                }

                $data = array(
                    'field' => $fieldId,
                    'groups' => $groups,
                    'group_id' => $group->getId(),
                    'store_code' => $store,
                    'website_code' => $website,
                    'scope' => $scope,
                    'scope_id' => $scopeId,
                    'field_config' => $field,
                    'fieldset_data' => $fieldsetData,
                );
                $backendModel->addData($data);
                    

                $this->_checkSingleStoreMode($field, $backendModel);

                if (false == isset($fieldData['value'])) {
                    $fieldData['value'] = null;
                }

                $path = $field->getPath();
                /**
                 * Look for custom defined field path
                 */
                if ($field && $field->getConfigPath()) {
                    $configPath = $field->getConfigPath();
                    if (!empty($configPath) && strrpos($configPath, '/') > 0) {
                        // Extend old data with specified section group
                        $groupPath = substr($configPath, 0, strrpos($configPath, '/'));
                        if (!isset($oldConfigAdditionalGroups[$groupPath])) {
                            $oldConfig = $this->extendConfig($groupPath, true, $oldConfig);
                            $oldConfigAdditionalGroups[$groupPath] = true;
                        }
                        $path = $configPath;
                    }
                }

                $inherit = !empty($fieldData['inherit']);

                $backendModel->setPath($path)
                    ->setValue($fieldData['value']);

                if (isset($oldConfig[$path])) {
                    $backendModel->setConfigId($oldConfig[$path]['config_id']);

                    /**
                     * Delete config data if inherit
                     */
                    if (!$inherit) {
                        $saveTransaction->addObject($backendModel);
                    } else {
                        $deleteTransaction->addObject($backendModel);
                    }
                } elseif (!$inherit) {
                    $backendModel->unsConfigId();
                    $saveTransaction->addObject($backendModel);
                }
            }

        }

        $deleteTransaction->delete();
        $saveTransaction->save();

        return $this;
    }

    /**
     * Get Backend model
     *
     * @param Mage_Backend_Model_Config_Structure_Element_Field $field
     * @param Mage_Backend_Model_Config_Structure_Element_Group $group
     * @param array $mappedFields
     * @param string $fieldId
     * @param string $sectionId
     * @return Mage_Core_Model_Abstract|Mage_Core_Model_Config_Data
     */
    protected function _getBackendModel(Mage_Backend_Model_Config_Structure_Element_Field $field,
        Mage_Backend_Model_Config_Structure_Element_Group $group,
        array $mappedFields,
        $fieldId,
        $sectionId
    ) {
        if ($field && $field->hasBackendModel()) {
            return $field->getBackendModel();
        } elseif ($group->shouldCloneFields() && isset($mappedFields[$fieldId])) {
            /** @var Mage_Backend_Model_Config_Structure_Element_Field $clonedField  */
            $clonedField = $this->_configStructure->getElementByPathParts(
                array($sectionId, $group->getId(), $mappedFields[$fieldId])
            );
            if ($clonedField && $clonedField->hasBackendModel()) {
                return $clonedField->getBackendModel();
            }
        }
        return $this->_objectFactory->getModelInstance('Mage_Core_Model_Config_Data');
    }

    /**
     * Load config data for section
     *
     * @return array
     */
    public function load()
    {
        $this->_validate();
        $this->_getScope();

        return $this->_getConfig(false);
    }

    /**
     * Extend config data with additional config data by specified path
     *
     * @param string $path Config path prefix
     * @param bool $full Simple config structure or not
     * @param array $oldConfig Config data to extend
     * @return array
     */
    public function extendConfig($path, $full = true, $oldConfig = array())
    {
        $extended = $this->_getPathConfig($path, $full);
        if (is_array($oldConfig) && !empty($oldConfig)) {
            return $oldConfig + $extended;
        }
        return $extended;
    }

    /**
     * Validate isset required parametrs
     *
     */
    protected function _validate()
    {
        if (is_null($this->getSection())) {
            $this->setSection('');
        }
        if (is_null($this->getWebsite())) {
            $this->setWebsite('');
        }
        if (is_null($this->getStore())) {
            $this->setStore('');
        }
    }

    /**
     * Get scope name and scopeId
     *
     */
    protected function _getScope()
    {
        if ($this->getStore()) {
            $scope   = 'stores';
            $scopeId = (int) $this->_appConfig->getNode('stores/' . $this->getStore() . '/system/store/id');
        } elseif ($this->getWebsite()) {
            $scope   = 'websites';
            $scopeId = (int) $this->_appConfig->getNode('websites/' . $this->getWebsite() . '/system/website/id');
        } else {
            $scope   = 'default';
            $scopeId = 0;
        }
        $this->setScope($scope);
        $this->setScopeId($scopeId);
    }

    /**
     * Return formatted config data for current section
     *
     * @param bool $full Simple config structure or not
     * @return array
     */
    protected function _getConfig($full = true)
    {
        return $this->_getPathConfig($this->getSection(), $full);
    }

    /**
     * Return formatted config data for specified path prefix
     *
     * @param string $path Config path prefix
     * @param bool $full Simple config structure or not
     * @return array
     */
    protected function _getPathConfig($path, $full = true)
    {
        $configDataCollection = $this->_objectFactory->getModelInstance('Mage_Core_Model_Config_Data')
            ->getCollection()
            ->addScopeFilter($this->getScope(), $this->getScopeId(), $path);

        $config = array();
        foreach ($configDataCollection as $data) {
            if ($full) {
                $config[$data->getPath()] = array(
                    'path'      => $data->getPath(),
                    'value'     => $data->getValue(),
                    'config_id' => $data->getConfigId()
                );
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }
        return $config;
    }

    /**
     * Set correct scope if isSingleStoreMode = true
     *
     * @param Mage_Backend_Model_Config_Structure_Element_Field $fieldConfig
     * @param Mage_Core_Model_Config_Data $dataObject
     */
    protected function _checkSingleStoreMode(
        Mage_Backend_Model_Config_Structure_Element_Field $fieldConfig,
        $dataObject
    ) {
        $isSingleStoreMode = $this->_application->isSingleStoreMode();
        if (!$isSingleStoreMode) {
            return;
        }
        if (!$fieldConfig->showInDefault()) {
            $websites = $this->_application->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $dataObject->setScope('websites');
            $dataObject->setWebsiteCode($singleStoreWebsite->getCode());
            $dataObject->setScopeId($singleStoreWebsite->getId());
        }
    }
}
