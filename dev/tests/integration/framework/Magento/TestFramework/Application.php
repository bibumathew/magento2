<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Encapsulates application installation, initialization and uninstall
 *
 * @todo Implement MAGETWO-1689: Standard Installation Method for Integration Tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Magento_TestFramework_Application
{
    /**
     * Default application area
     */
    const DEFAULT_APP_AREA = 'global';

    /**
     * DB vendor adapter instance
     *
     * @var Magento_TestFramework_Db_DbAbstract
     */
    protected $_db;

    /**
     * @var \Magento\Simplexml\Element
     */
    protected $_localXml;

    /**
     * Application *.xml configuration files
     *
     * @var array
     */
    protected $_globalConfigDir;

    /**
     * Module declaration *.xml configuration files
     *
     * @var array
     */
    protected $_moduleEtcFiles;

    /**
     * Installation destination directory
     *
     * @var string
     */
    protected $_installDir;

    /**
     * Installation destination directory with configuration files
     *
     * @var string
     */
    protected $_installEtcDir;

    /**
     * Application initialization parameters
     *
     * @var array
     */
    protected $_initParams = array();

    /**
     * Mode to run application
     *
     * @var string
     */
    protected $_appMode;

    /**
     * Application area
     *
     * @var null
     */
    protected $_appArea = null;

    /**
     * Primary DI Config
     *
     * @var array
     */
    protected $_primaryConfig = array();

    /**
     * Constructor
     *
     * @param Magento_TestFramework_Db_DbAbstract $dbInstance
     * @param string $installDir
     * @param \Magento\Simplexml\Element $localXml
     * @param $globalConfigDir
     * @param array $moduleEtcFiles
     * @param string $appMode
     */
    public function __construct(
        Magento_TestFramework_Db_DbAbstract $dbInstance, $installDir, \Magento\Simplexml\Element $localXml,
        $globalConfigDir, array $moduleEtcFiles, $appMode
    ) {
        $this->_db              = $dbInstance;
        $this->_localXml        = $localXml;
        $this->_globalConfigDir = realpath($globalConfigDir);
        $this->_moduleEtcFiles  = $moduleEtcFiles;
        $this->_appMode = $appMode;

        $this->_installDir = $installDir;
        $this->_installEtcDir = "$installDir/etc";

        $generationDir = "$installDir/generation";
        $this->_initParams = array(
            Mage::PARAM_APP_DIRS => array(
                \Magento\Core\Model\Dir::CONFIG      => $this->_installEtcDir,
                \Magento\Core\Model\Dir::VAR_DIR     => $installDir,
                \Magento\Core\Model\Dir::MEDIA       => "$installDir/media",
                \Magento\Core\Model\Dir::STATIC_VIEW => "$installDir/pub_static",
                \Magento\Core\Model\Dir::PUB_VIEW_CACHE => "$installDir/pub_cache",
                \Magento\Core\Model\Dir::GENERATION => $generationDir,
            ),
            Mage::PARAM_MODE => $appMode
        );
    }

    /**
     * Retrieve the database adapter instance
     *
     * @return Magento_TestFramework_Db_DbAbstract
     */
    public function getDbInstance()
    {
        return $this->_db;
    }

    /**
     * Get directory path with application instance custom data (cache, temporary directory, etc...)
     */
    public function getInstallDir()
    {
        return $this->_installDir;
    }

    /**
     * Retrieve application initialization parameters
     *
     * @return array
     */
    public function getInitParams()
    {
        return $this->_initParams;
    }

    /**
     * Weather the application is installed or not
     *
     * @return bool
     */
    public function isInstalled()
    {
        return is_file($this->_installEtcDir . '/local.xml');
    }

    /**
     * Initialize an already installed application
     *
     * @param array $overriddenParams
     */
    public function initialize($overriddenParams = array())
    {
        $overriddenParams[Mage::PARAM_BASEDIR] = BP;
        $overriddenParams[Mage::PARAM_MODE] = $this->_appMode;
        Mage::$headersSentThrowsException = false;
        $config = new \Magento\Core\Model\Config\Primary(BP, $this->_customizeParams($overriddenParams));
        if (!Magento_TestFramework_Helper_Bootstrap::getObjectManager()) {
            $objectManager = new Magento_TestFramework_ObjectManager($config,
                new Magento_TestFramework_ObjectManager_Config());
            $primaryLoader = new \Magento\Core\Model\ObjectManager\ConfigLoader\Primary($config->getDirectories());
            $this->_primaryConfig = $primaryLoader->load();
            $objectManager->get('Magento\Core\Model\Resource')
                ->setResourceConfig(Mage::getObjectManager()->get('Magento\Core\Model\Config\Resource'));
        } else {
            $objectManager = Magento_TestFramework_Helper_Bootstrap::getObjectManager();
            Magento_TestFramework_ObjectManager::setInstance($objectManager);
            $config->configure($objectManager);
            $objectManager->addSharedInstance($config, 'Magento\Core\Model\Config\Primary');
            $objectManager->addSharedInstance($config->getDirectories(), 'Magento\Core\Model\Dir');
            $objectManager->loadPrimaryConfig($this->_primaryConfig);
            /** @var $configResource \Magento\Core\Model\Config\Resource */
            $configResource = $objectManager->get('Magento\Core\Model\Config\Resource');
            $configResource->setConfig($config);
            $objectManager->get('Magento\Core\Model\Resource')->setResourceConfig($configResource);
            $verification = $objectManager->get('Magento\Core\Model\Dir\Verification');
            $verification->createAndVerifyDirectories();
            $objectManager->configure(
                $objectManager->get('Magento\Core\Model\ObjectManager\ConfigLoader')->load('global')
            );
        }
        Magento_TestFramework_Helper_Bootstrap::setObjectManager($objectManager);
        $objectManager->get('Magento\Core\Model\Resource')
            ->setResourceConfig($objectManager->get('Magento\Core\Model\Config\Resource'));
        $objectManager->get('Magento\Core\Model\Resource')
            ->setCache($objectManager->get('Magento\Core\Model\CacheInterface'));

        /** Register event observer of Integration Framework */
        /** @var \Magento\Core\Model\Event\Config\Data $eventConfigData */
        $eventConfigData = $objectManager->get('Magento\Core\Model\Event\Config\Data');
        $eventConfigData->merge(
            array('core_app_init_current_store_after' =>
                array('integration_tests' =>
                    array(
                        'instance' => 'Magento_TestFramework_Event_Magento',
                        'method' => 'initStoreAfter',
                        'name' => 'integration_tests'
                    )
                )
            )
        );
        /** @var \Magento\Core\Model\Dir\Verification $verification */
        $verification = $objectManager->get('Magento\Core\Model\Dir\Verification');
        $verification->createAndVerifyDirectories();

        $this->loadArea(Magento_TestFramework_Application::DEFAULT_APP_AREA);

        \Magento\Phrase::setRenderer($objectManager->get('Magento\Phrase\Renderer\Placeholder'));
    }

    /**
     * Reset and initialize again an already installed application
     *
     * @param array $overriddenParams
     */
    public function reinitialize(array $overriddenParams = array())
    {
        $this->_resetApp();
        $this->initialize($overriddenParams);
    }

    /**
     * Run application normally, but with encapsulated initialization options
     *
     * @param Magento_TestFramework_Request $request
     * @param Magento_TestFramework_Response $response
     */
    public function run(Magento_TestFramework_Request $request, Magento_TestFramework_Response $response)
    {
        $composer = Magento_TestFramework_Helper_Bootstrap::getObjectManager();
        $handler = $composer->get('Magento\HTTP\Handler\Composite');
        $handler->handle($request, $response);
    }

    /**
     * Cleanup both the database and the file system
     */
    public function cleanup()
    {
        $this->_db->cleanup();
        $this->_cleanupFilesystem();
    }

    /**
     * Install an application
     *
     * @param string $adminUserName
     * @param string $adminPassword
     * @param string $adminRoleName
     * @throws \Magento\Exception
     */
    public function install($adminUserName, $adminPassword, $adminRoleName)
    {
        $this->_ensureDirExists($this->_installDir);
        $this->_ensureDirExists($this->_installEtcDir);
        $this->_ensureDirExists($this->_installDir . DIRECTORY_SEPARATOR . 'media');
        $this->_ensureDirExists($this->_installDir . DIRECTORY_SEPARATOR . 'static');

        // Copy configuration files
        $globalConfigFiles = glob(
            $this->_globalConfigDir . DIRECTORY_SEPARATOR . '{*,*' . DIRECTORY_SEPARATOR . '*}.xml', GLOB_BRACE
        );
        foreach ($globalConfigFiles as $file) {
            $targetFile = $this->_installEtcDir . str_replace($this->_globalConfigDir, '', $file);
            $this->_ensureDirExists(dirname($targetFile));
            copy($file, $targetFile);
        }

        foreach ($this->_moduleEtcFiles as $file) {
            $targetModulesDir = $this->_installEtcDir . '/modules';
            $this->_ensureDirExists($targetModulesDir);
            copy($file, $targetModulesDir . DIRECTORY_SEPARATOR . basename($file));
        }

        /* Make sure that local.xml contains an invalid installation date */
        $installDate = (string)$this->_localXml->global->install->date;
        if ($installDate && strtotime($installDate)) {
            throw new \Magento\Exception('Local configuration must contain an invalid installation date.');
        }

        /* Replace local.xml */
        $targetLocalXml = $this->_installEtcDir . '/local.xml';
        $this->_localXml->asNiceXml($targetLocalXml);

        /* Initialize an application in non-installed mode */
        $this->initialize();

        /* Run all install and data-install scripts */
        /** @var $updater \Magento\Core\Model\Db\Updater */
        $updater = Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento\Core\Model\Db\Updater');
        $updater->updateScheme();
        $updater->updateData();

        /* Enable configuration cache by default in order to improve tests performance */
        /** @var $cacheState \Magento\Core\Model\Cache\StateInterface */
        $cacheState = Magento_TestFramework_Helper_Bootstrap::getObjectManager()
            ->get('Magento\Core\Model\Cache\StateInterface');
        $cacheState->setEnabled(\Magento\Core\Model\Cache\Type\Config::TYPE_IDENTIFIER, true);
        $cacheState->setEnabled(\Magento\Core\Model\Cache\Type\Layout::TYPE_IDENTIFIER, true);
        $cacheState->setEnabled(\Magento\Core\Model\Cache\Type\Translate::TYPE_IDENTIFIER, true);
        $cacheState->setEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER, true);
        $cacheState->persist();

        /* Fill installation date in local.xml to indicate that application is installed */
        $localXml = file_get_contents($targetLocalXml);
        $localXml = str_replace($installDate, date('r'), $localXml, $replacementCount);
        if ($replacementCount != 1) {
            throw new \Magento\Exception("Unable to replace installation date properly in '$targetLocalXml' file.");
        }
        file_put_contents($targetLocalXml, $localXml, LOCK_EX);

        /* Add predefined admin user to the system */
        $this->_createAdminUser($adminUserName, $adminPassword, $adminRoleName);

        /* Switch an application to installed mode */
        $this->initialize();
    }

    /**
     * Sub-routine for merging custom parameters with the ones defined in object state
     *
     * @param array $params
     * @return array
     */
    private function _customizeParams($params)
    {
        return array_replace_recursive($this->_initParams, $params);
    }

    /**
     * Reset application global state
     */
    protected function _resetApp()
    {
        /** @var $objectManager Magento_TestFramework_ObjectManager */
        $objectManager = Magento_TestFramework_Helper_Bootstrap::getObjectManager();
        $objectManager->clearCache();

        $resource = $objectManager->get('Magento_Core_Model_Registry')
            ->registry('_singleton/Magento\Core\Model\Resource');

        Mage::reset();
        Mage::setObjectManager($objectManager);
        \Magento\Data\Form::setElementRenderer(null);
        \Magento\Data\Form::setFieldsetRenderer(null);
        \Magento\Data\Form::setFieldsetElementRenderer(null);
        $this->_appArea = null;

        if ($resource) {
            $objectManager->get('Magento_Core_Model_Registry')
                ->register('_singleton/Magento\Core\Model\Resource', $resource);
        }
    }

    /**
     * Create a directory with write permissions or don't touch existing one
     *
     * @throws \Magento\Exception
     * @param string $dir
     */
    protected function _ensureDirExists($dir)
    {
        if (!file_exists($dir)) {
            $old = umask(0);
            mkdir($dir, 0777);
            umask($old);
        } else if (!is_dir($dir)) {
            throw new \Magento\Exception("'$dir' is not a directory.");
        }
    }

    /**
     * Remove temporary files and directories from the filesystem
     */
    protected function _cleanupFilesystem()
    {
        \Magento\Io\File::rmdirRecursive($this->_installDir);
    }

    /**
     * Creates predefined admin user to be used by tests, where admin session is required
     *
     * @param string $adminUserName
     * @param string $adminPassword
     * @param string $adminRoleName
     */
    protected function _createAdminUser($adminUserName, $adminPassword, $adminRoleName)
    {
        /** @var $user \Magento\User\Model\User */
        $user = mage::getModel('Magento\User\Model\User');
        $user->setData(array(
            'firstname' => 'firstname',
            'lastname'  => 'lastname',
            'email'     => 'admin@example.com',
            'username'  => $adminUserName,
            'password'  => $adminPassword,
            'is_active' => 1
        ));
        $user->save();

        /** @var $roleAdmin \Magento\User\Model\Role */
        $roleAdmin = Mage::getModel('Magento\User\Model\Role');
        $roleAdmin->load($adminRoleName, 'role_name');

        /** @var $roleUser \Magento\User\Model\Role */
        $roleUser = Mage::getModel('Magento\User\Model\Role');
        $roleUser->setData(array(
            'parent_id'  => $roleAdmin->getId(),
            'tree_level' => $roleAdmin->getTreeLevel() + 1,
            'role_type'  => \Magento\User\Model\Acl\Role\User::ROLE_TYPE,
            'user_id'    => $user->getId(),
            'role_name'  => $user->getFirstname(),
        ));
        $roleUser->save();
    }

    /**
     * Ge current application area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_appArea;
    }

    /**
     * Load application area
     *
     * @param $area
     */
    public function loadArea($area)
    {
        $this->_appArea = $area;
        if ($area == Magento_TestFramework_Application::DEFAULT_APP_AREA) {
            Mage::app()->loadAreaPart($area, \Magento\Core\Model\App\Area::PART_CONFIG);
        } else {
            Mage::app()->loadArea($area);
        }
    }
}
