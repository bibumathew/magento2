<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */
/**
 * Controller context
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Magento_Backend_Controller_Context extends Magento_Core_Controller_Varien_Action_Context
{
    /**
     * @var Magento_Backend_Model_Session
     */
    protected $_session;

    /**
     * @var Magento_Backend_Helper_Data
     */
    protected $_helper;

    /**
     * @var Magento_AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var Magento_Core_Model_Translate
     */
    protected $_translator;

    /**
     * @var Magento_Backend_Model_Auth
     */
    protected $_auth;

    /**
     * @var Magento_Backend_Model_Url
     */
    protected $_backendUrl;

    /**
     * @var Magento_Core_Model_LocaleInterface
     */
    protected $_locale;

    /**
     * @param Magento_Core_Model_Logger $logger
     * @param Magento_Core_Controller_Request_Http $request
     * @param Magento_Core_Controller_Response_Http $response
     * @param Magento_ObjectManager $objectManager
     * @param Magento_Core_Controller_Varien_Front $frontController
     * @param Magento_Core_Model_Layout $layout
     * @param Magento_Core_Model_Event_Manager $eventManager
     * @param bool $isRenderInherited
     * @param Magento_Backend_Model_Session $session
     * @param Magento_Backend_Helper_Data $helper
     * @param Magento_AuthorizationInterface $authorization
     * @param Magento_Core_Model_Translate $translator
     * @param Magento_Backend_Model_Auth $auth
     * @param Magento_Backend_Model_Url $backendUrl
     * @param Magento_Core_Model_LocaleInterface $locale
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Magento_Core_Model_Logger $logger,
        Magento_Core_Controller_Request_Http $request,
        Magento_Core_Controller_Response_Http $response,
        Magento_ObjectManager $objectManager,
        Magento_Core_Controller_Varien_Front $frontController,
        Magento_Core_Model_Layout $layout,
        Magento_Core_Model_Event_Manager $eventManager,
        $isRenderInherited,
        Magento_Backend_Model_Session $session,
        Magento_Backend_Helper_Data $helper,
        Magento_AuthorizationInterface $authorization,
        Magento_Core_Model_Translate $translator,
        Magento_Backend_Model_Auth $auth,
        Magento_Backend_Model_Url $backendUrl,
        Magento_Core_Model_LocaleInterface $locale
    ) {
        parent::__construct($logger, $request, $response, $objectManager, $frontController, $layout, $eventManager, 
            $isRenderInherited
        );
        $this->_session = $session;
        $this->_helper = $helper;
        $this->_authorization = $authorization;
        $this->_translator = $translator;
        $this->_auth = $auth;
        $this->_backendUrl = $backendUrl;
        $this->_locale = $locale;
    }

    /**
     * @return \Magento_Backend_Helper_Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return \Magento_Backend_Model_Session
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * @return \Magento_AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * @return \Magento_Core_Model_Translate
     */
    public function getTranslator()
    {
        return $this->_translator;
    }

    /**
     * @return \Magento_Backend_Model_Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Magento_Backend_Model_Url
     */
    public function getBackendUrl()
    {
        return $this->_backendUrl;
    }

    /**
     * @return \Magento_Core_Model_LocaleInterface
     */
    public function getLocale()
    {
        return $this->_locale;
    }
}
