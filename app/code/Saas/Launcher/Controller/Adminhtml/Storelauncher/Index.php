<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Saas_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Launcher controller
 *
 * @category    Magento
 * @package     Saas_Launcher
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Saas_Launcher_Controller_Adminhtml_Storelauncher_Index extends Saas_Launcher_Controller_BasePage
{
    /**
     * Core Config Model
     *
     * @var Magento_Core_Model_Config
     */
    protected $_configModel;

    /**
     * Config Writer Model
     *
     * @var Magento_Core_Model_Config_Storage_WriterInterface
     */
    protected $_configWriter;

    /**
     * Launcher Helper
     *
     * @var Saas_Launcher_Helper_Data
     */
    protected  $_launcherHelper;

    /**
     * @param Magento_Backend_Controller_Context $context
     * @param Magento_Core_Model_Config $configModel
     * @param Magento_Core_Model_Config_Storage_WriterInterface $configWriter
     * @param Saas_Launcher_Helper_Data $launcherHelper
     * @param string $areaCode
     */
    public function __construct(
        Magento_Backend_Controller_Context $context,
        Magento_Core_Model_Config $configModel,
        Magento_Core_Model_Config_Storage_WriterInterface $configWriter,
        Saas_Launcher_Helper_Data $launcherHelper,
        $areaCode = null
    ) {
        parent::__construct($context, $areaCode);
        $this->_configModel = $configModel;
        $this->_configWriter = $configWriter;
        $this->_launcherHelper = $launcherHelper;
    }

    /**
     * Launch store action
     */
    public function launchAction()
    {
        $responseContent = array();
        /** @var $page Saas_Launcher_Model_Page */
        $page = $this->_objectManager->create('Saas_Launcher_Model_Page')->loadByPageCode('store_launcher');
        if ($page->isComplete()) {
            $this->_configWriter->save('design/head/demonotice', 0);
            $this->_configWriter->save(
                Saas_Launcher_Helper_Data::CONFIG_PATH_LAUNCHER_PHASE,
                Saas_Launcher_Helper_Data::LAUNCHER_PHASE_PROMOTE_STORE
            );
            $this->_configModel->reinit();
            $responseContent = array(
                'success' => true,
            );
        } else {
            $responseContent = array(
                'success' => false,
                'error_message' => $this->_launcherHelper->__('All Tiles have to be completed before this action.')
            );
        }

        $this->getResponse()->setBody($this->_launcherHelper->jsonEncode($responseContent));
    }

    /**
     * Change state after showing Welcome Screen
     */
    public function showScreenAction()
    {
        $launcherFlag = $this->_objectManager->get('Saas_Launcher_Model_Storelauncher_Flag');
        $launcherFlag->loadSelf()->setState(1);
        $launcherFlag->save();
        $responseContent = $this->_launcherHelper->jsonEncode(array(
            'success' => true,
            'error_message' => '',
        ));
        $this->getResponse()->setBody($responseContent);
    }
}
