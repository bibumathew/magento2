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
 * Installation wizard controller
 */
class Mage_Install_Controller_Wizard extends Mage_Install_Controller_Action
{
    /**
     * Perform necessary checks for all actions
     *
     * Redirect out if system is already installed
     * Throw a bootstrap exception if page cannot be displayed due to misconfigured base directories
     *
     * @throws Magento_BootstrapException
     */
    public function preDispatch()
    {
        if (Mage::isInstalled()) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->_redirect('/');
            return;
        }

        $this->setFlag('', self::FLAG_NO_CHECK_INSTALLATION, true);
        return parent::preDispatch();
    }

    /**
     * Retrieve installer object
     *
     * @return Mage_Install_Model_Installer
     */
    protected function _getInstaller()
    {
        return Mage::getSingleton('Mage_Install_Model_Installer');
    }

    /**
     * Retrieve wizard
     *
     * @return Mage_Install_Model_Wizard
     */
    protected function _getWizard()
    {
        return Mage::getSingleton('Mage_Install_Model_Wizard');
    }

    /**
     * Prepare layout
     *
     * @return unknown
     */
    protected function _prepareLayout()
    {
        $this->loadLayout('install_wizard');
        $step = $this->_getWizard()->getStepByRequest($this->getRequest());
        if ($step) {
            $step->setActive(true);
        }

        $this->getLayout()->addBlock('Mage_Install_Block_State', 'install.state', 'left');
        return $this;
    }

    /**
     * Checking installation status
     *
     * @return bool
     */
    protected function _checkIfInstalled()
    {
        if ($this->_getInstaller()->isApplicationInstalled()) {
            $this->getResponse()->setRedirect(Mage::getBaseUrl())->sendResponse();
            exit;
        }
        return true;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_forward('begin');
    }

    /**
     * Begin installation action
     */
    public function beginAction()
    {
        $this->_checkIfInstalled();

        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Mage_Install_Model_Session');

        $this->getLayout()->addBlock('Mage_Install_Block_Begin', 'install.begin', 'content');

        $this->renderLayout();
    }

    /**
     * Process begin step POST data
     */
    public function beginPostAction()
    {
        $this->_checkIfInstalled();

        $agree = $this->getRequest()->getPost('agree');
        if ($agree && $step = $this->_getWizard()->getStepByName('begin')) {
            $this->getResponse()->setRedirect($step->getNextUrl());
        }
        else {
            $this->_redirect('install');
        }
    }

    /**
     * Localization settings
     */
    public function localeAction()
    {
        $this->_checkIfInstalled();
        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Mage_Install_Model_Session');
        $this->getLayout()->addBlock('Mage_Install_Block_Locale', 'install.locale', 'content');

        $this->renderLayout();
    }

    /**
     * Change current locale
     */
    public function localeChangeAction()
    {
        $this->_checkIfInstalled();

        $locale = $this->getRequest()->getParam('locale');
        $timezone = $this->getRequest()->getParam('timezone');
        $currency = $this->getRequest()->getParam('currency');
        if ($locale) {
            Mage::getSingleton('Mage_Install_Model_Session')->setLocale($locale);
            Mage::getSingleton('Mage_Install_Model_Session')->setTimezone($timezone);
            Mage::getSingleton('Mage_Install_Model_Session')->setCurrency($currency);
        }

        $this->_redirect('*/*/locale');
    }

    /**
     * Saving localization settings
     */
    public function localePostAction()
    {
        $this->_checkIfInstalled();
        $step = $this->_getWizard()->getStepByName('locale');

        if ($data = $this->getRequest()->getPost('config')) {
            Mage::getSingleton('Mage_Install_Model_Session')->setLocaleData($data);
        }

        $this->getResponse()->setRedirect($step->getNextUrl());
    }

    public function downloadAction()
    {
        $this->_checkIfInstalled();
        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Mage_Install_Model_Session');
        $this->getLayout()->addBlock('Mage_Install_Block_Download', 'install.download', 'content');

        $this->renderLayout();
    }

    public function downloadPostAction()
    {
        $this->_checkIfInstalled();
        switch ($this->getRequest()->getPost('continue')) {
            case 'auto':
                $this->_forward('downloadAuto');
                break;

            case 'manual':
                $this->_forward('downloadManual');
                break;

            case 'svn':
                $step = $this->_getWizard()->getStepByName('download');
                $this->getResponse()->setRedirect($step->getNextUrl());
                break;

            default:
                $this->_redirect('*/*/download');
        }
    }

    public function downloadAutoAction()
    {
        $step = $this->_getWizard()->getStepByName('download');
        $this->getResponse()->setRedirect($step->getNextUrl());
    }

    public function installAction()
    {
        $pear = Magento_Pear::getInstance();
        $params = array('comment'=>Mage::helper('Mage_Install_Helper_Data')->__("Downloading and installing Magento, please wait...") . "\r\n\r\n");
        if ($this->getRequest()->getParam('do')) {
            if ($state = $this->getRequest()->getParam('state', 'beta')) {
                $result = $pear->runHtmlConsole(array(
                'comment'   => Mage::helper('Mage_Install_Helper_Data')->__("Setting preferred state to: %s", $state) . "\r\n\r\n",
                'command'   => 'config-set',
                'params'    => array('preferred_state', $state)
                ));
                if ($result instanceof PEAR_Error) {
                    $this->installFailureCallback();
                    exit;
                }
            }
            $params['command'] = 'install';
            $params['options'] = array('onlyreqdeps'=>1);
            $params['params'] = Mage::getModel('Mage_Install_Model_Installer_Pear')->getPackages();
            $params['success_callback'] = array($this, 'installSuccessCallback');
            $params['failure_callback'] = array($this, 'installFailureCallback');
        }
        $pear->runHtmlConsole($params);
        Mage::app()->getFrontController()->getResponse()->clearAllHeaders();
    }

    public function installSuccessCallback()
    {
        echo 'parent.installSuccess()';
    }

    public function installFailureCallback()
    {
        echo 'parent.installFailure()';
    }

    public function downloadManualAction()
    {
        $step = $this->_getWizard()->getStepByName('download');
        #if (!$this->_getInstaller()->checkDownloads()) {
        #    $this->getResponse()->setRedirect($step->getUrl());
        #} else {
        $this->getResponse()->setRedirect($step->getNextUrl());
        #}
    }

    /**
     * Configuration data installation
     */
    public function configAction()
    {
        $this->_checkIfInstalled();
        $this->_getInstaller()->checkServer();

        $this->setFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);

        if ($data = $this->getRequest()->getQuery('config')) {
            Mage::getSingleton('Mage_Install_Model_Session')->setLocaleData($data);
        }

        $this->_prepareLayout();
        $this->_initLayoutMessages('Mage_Install_Model_Session');
        $this->getLayout()->addBlock('Mage_Install_Block_Config', 'install.config', 'content');

        $this->renderLayout();
    }

    /**
     * Process configuration POST data
     */
    public function configPostAction()
    {
        $this->_checkIfInstalled();
        $step = $this->_getWizard()->getStepByName('config');

        $config             = $this->getRequest()->getPost('config');
        $connectionConfig   = $this->getRequest()->getPost('connection');

        if ($config && $connectionConfig && isset($connectionConfig[$config['db_model']])) {

            $data = array_merge($config, $connectionConfig[$config['db_model']]);

            Mage::getSingleton('Mage_Install_Model_Session')
                ->setConfigData($data)
                ->setSkipUrlValidation($this->getRequest()->getPost('skip_url_validation'))
                ->setSkipBaseUrlValidation($this->getRequest()->getPost('skip_base_url_validation'));
            try {
                $this->_getInstaller()->installConfig($data);
                $this->_redirect('*/*/installDb');
                return $this;
            }
            catch (Exception $e){
                Mage::getSingleton('Mage_Install_Model_Session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($step->getUrl());
            }
        }
        $this->getResponse()->setRedirect($step->getUrl());
    }

    /**
     * Install DB
     */
    public function installDbAction()
    {
        $this->_checkIfInstalled();
        $step = $this->_getWizard()->getStepByName('config');
        try {
            $this->_getInstaller()->installDb();
            /**
             * Clear session config data
             */
            Mage::getSingleton('Mage_Install_Model_Session')->getConfigData(true);

            Mage::app()->getStore()->resetConfig();

            $this->getResponse()->setRedirect(Mage::getUrl($step->getNextUrlPath()));
        }
        catch (Exception $e){
            Mage::getSingleton('Mage_Install_Model_Session')->addError($e->getMessage());
            $this->getResponse()->setRedirect($step->getUrl());
        }
    }

    /**
     * Install administrator account
     */
    public function administratorAction()
    {
        $this->_checkIfInstalled();

        $this->_prepareLayout();
        $this->_initLayoutMessages('Mage_Install_Model_Session');

        $this->getLayout()->addBlock('Mage_Install_Block_Admin', 'install.administrator', 'content');
        $this->renderLayout();
    }

    /**
     * Process administrator installation POST data
     */
    public function administratorPostAction()
    {
        $this->_checkIfInstalled();

        $step = Mage::getSingleton('Mage_Install_Model_Wizard')->getStepByName('administrator');
        $adminData      = $this->getRequest()->getPost('admin');
        $encryptionKey  = $this->getRequest()->getPost('encryption_key');

        try {
            $encryptionKey = $this->_getInstaller()->getValidEncryptionKey($encryptionKey);
            $this->_getInstaller()->createAdministrator($adminData);
            $this->_getInstaller()->installEncryptionKey($encryptionKey);
            $this->getResponse()->setRedirect($step->getNextUrl());
        } catch (Exception $e){
            /** @var $session Mage_Core_Model_Session_Generic */
            $session = Mage::getSingleton('Mage_Install_Model_Session');
            $session->setAdminData($adminData);
            if ($e instanceof Mage_Core_Exception) {
                $session->addMessages($e->getMessages());
            } else {
                $session->addError($e->getMessage());
            }
            $this->getResponse()->setRedirect($step->getUrl());
        }
    }

    /**
     * End installation
     */
    public function endAction()
    {
        $this->_checkIfInstalled();

        $date = (string)Mage::getConfig()->getNode('global/install/date');
        if ($date !== Mage_Install_Model_Installer_Config::TMP_INSTALL_DATE_VALUE) {
            $this->_redirect('*/*');
            return;
        }

        $this->_getInstaller()->finish();

        Mage_AdminNotification_Model_Survey::saveSurveyViewed(true);

        $this->_prepareLayout();
        $this->_initLayoutMessages('Mage_Install_Model_Session');

        $this->getLayout()->addBlock('Mage_Install_Block_End', 'install.end', 'content');
        $this->renderLayout();
        Mage::getSingleton('Mage_Install_Model_Session')->clear();
    }
}
