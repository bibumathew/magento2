<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Install
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * DB Installer
 *
 * @category   Mage
 * @package    Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Model_Installer_Db extends Mage_Install_Model_Installer_Abstract
{
    /**
     * @var database resource
     */
    protected $_dbResource;

    /**
     * Resource configuration
     *
     * @var Mage_Core_Model_Config_Resource
     */
    protected $_resourceConfig;

    /**
     * @param Mage_Core_Model_Config_Resource $resourceConfig
     */
    public function __construct(Mage_Core_Model_Config_Resource $resourceConfig)
    {
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * Check database connection
     * and return checked connection data
     *
     * @param array $data
     * @return array
     */
    public function checkDbConnectionData($data)
    {
        $data = $this->_getCheckedData($data);

        try {
            $dbModel = ($data['db_model']);

            if (!$resource = $this->_getDbResource($dbModel)) {
                Mage::throwException(__('There is no resource for %s DB model.', $dbModel));
            }

            $resource->setConfig($data);

            // check required extensions
            $absenteeExtensions = array();
            $extensions = $resource->getRequiredExtensions();
            foreach ($extensions as $extName) {
                if (!extension_loaded($extName)) {
                    $absenteeExtensions[] = $extName;
                }
            }
            if (!empty($absenteeExtensions)) {
                Mage::throwException(
                    __('PHP Extensions "%s" must be loaded.', implode(',', $absenteeExtensions))
                );
            }

            $version    = $resource->getVersion();
            $requiredVersion = (string) Mage::getConfig()
                ->getNode(sprintf('install/databases/%s/min_version', $dbModel));

            // check DB server version
            if (version_compare($version, $requiredVersion) == -1) {
                Mage::throwException(
                    __('The database server version doesn\'t match system requirements (required: %s, actual: %s).', $requiredVersion, $version)
                );
            }

            // check InnoDB support
            if (!$resource->supportEngine()) {
                Mage::throwException(
                    __('Database server does not support the InnoDB storage engine.')
                );
            }

            // TODO: check user roles
        }
        catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::throwException(__($e->getMessage()));
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException(__('Something went wrong while connecting to the database.'));
        }

        return $data;
    }

    /**
     * Check database connection data
     *
     * @param  array $data
     * @return array
     */
    protected function _getCheckedData($data)
    {
        if (!isset($data['db_name']) || empty($data['db_name'])) {
            Mage::throwException(__('The Database Name field cannot be empty.'));
        }
        //make all table prefix to lower letter
        if ($data['db_prefix'] != '') {
           $data['db_prefix'] = strtolower($data['db_prefix']);
        }
        //check table prefix
        if ($data['db_prefix'] != '') {
            if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $data['db_prefix'])) {
                Mage::throwException(
                    __('The table prefix should contain only letters (a-z), numbers (0-9) or underscores (_); the first character should be a letter.')
                );
            }
        }
        //set default db model
        if (!isset($data['db_model']) || empty($data['db_model'])) {
            $data['db_model'] = $this->_resourceConfig
                ->getResourceConnectionConfig(Mage_Core_Model_Resource::DEFAULT_SETUP_RESOURCE)->model;
        }
        //set db type according the db model
        if (!isset($data['db_type'])) {
            $data['db_type'] = (string) Mage::getSingleton('Mage_Core_Model_Config_Modules')
                ->getNode(sprintf('install/databases/%s/type', $data['db_model']));
        }

        $dbResource = $this->_getDbResource($data['db_model']);
        $data['db_pdo_type'] = $dbResource->getPdoType();

        if (!isset($data['db_init_statements'])) {
            $data['db_init_statements'] = (string) Mage::getSingleton('Mage_Core_Model_Config_Modules')
                ->getNode(sprintf('install/databases/%s/initStatements', $data['db_model']));
        }

        return $data;
    }

    /**
     * Retrieve the database resource
     *
     * @param  string $model database type
     * @return Mage_Install_Model_Installer_Db_Abstract
     */
    protected function _getDbResource($model)
    {
        if (!isset($this->_dbResource)) {
            $resource =  Mage::getSingleton("Mage_Install_Model_Installer_Db_" . ucfirst($model));
            if (!$resource) {
                Mage::throwException(
                    __('Installer does not exist for %s database type', $model)
                );
            }
            $this->_dbResource = $resource;
        }
        return $this->_dbResource;
    }
}
