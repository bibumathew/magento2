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
 * Generic backend controller
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
namespace Magento\Backend\Controller;

abstract class ActionAbstract extends \Magento\Core\Controller\Varien\Action
{
    /**
     * Name of "is URLs checked" flag
     */
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'adminhtml';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array();

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Core\Model\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Core\Model\Translate
     */
    protected $_translator;

    /**
     * @param \Magento\Backend\Controller\Context $context
     */
    public function __construct(\Magento\Backend\Controller\Context $context)
    {
        parent::__construct($context);
        $this->_helper = $context->getHelper();
        $this->_session = $context->getSession();
        $this->_authorization = $context->getAuthorization();
        $this->_translator = $context->getTranslator();
    }

    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return \Magento\Backend\Model\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Retrieve base adminhtml helper
     *
     * @return \Magento\Backend\Helper\Data
     */
    protected function _getHelper()
    {
        return $this->_helper;
    }

    /**
     * Define active menu item in menu block
     * @param string $itemId current active menu item
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _setActiveMenu($itemId)
    {
        /** @var $menuBlock \Magento\Backend\Block\Menu */
        $menuBlock = $this->getLayout()->getBlock('menu');
        $menuBlock->setActive($itemId);
        $parents = $menuBlock->getMenuModel()->getParentItems($itemId);
        $parents = array_reverse($parents);
        foreach ($parents as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            array_unshift($this->_titles, $item->getTitle());
        }
        return $this;
    }

    /**
     * @param $label
     * @param $title
     * @param null $link
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _addBreadcrumb($label, $title, $link=null)
    {
        $this->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
        return $this;
    }

    /**
     * @param \Magento\Core\Block\AbstractBlock $block
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _addContent(\Magento\Core\Block\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'content');
    }

    /**
     * @param \Magento\Core\Block\AbstractBlock $block
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _addLeft(\Magento\Core\Block\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'left');
    }

    /**
     * @param \Magento\Core\Block\AbstractBlock $block
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _addJs(\Magento\Core\Block\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'js');
    }

    /**
     * Set specified block as an anonymous child to specified container
     *
     * The block will be moved to the container from previous parent after all other elements
     *
     * @param \Magento\Core\Block\AbstractBlock $block
     * @param string $containerName
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    private function _moveBlockToContainer(\Magento\Core\Block\AbstractBlock $block, $containerName)
    {
        $this->getLayout()->setChild($containerName, $block->getNameInLayout(), '');
        return $this;
    }

    /**
     * Controller predispatch method
     *
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    public function preDispatch()
    {
        /** @var $storeManager \Magento\Core\Model\StoreManager */
        $storeManager = $this->_objectManager->get('Magento\Core\Model\StoreManager');
        $storeManager->setCurrentStore('admin');

        $this->_eventManager->dispatch('adminhtml_controller_action_predispatch_start', array());
        parent::preDispatch();
        if (!$this->_processUrlKeys()) {
            return $this;
        }

        if ($this->getRequest()->isDispatched()
            && $this->getRequest()->getActionName() !== 'denied'
            && !$this->_isAllowed()) {
            $this->_forward('denied');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return $this;
        }

        if ($this->_isUrlChecked()) {
            $this->setFlag('', self::FLAG_IS_URLS_CHECKED, true);
        }

        $this->_processLocaleSettings();

        return $this;
    }

    /**
     * Check whether url is checked
     *
     * @return bool
     */
    protected function _isUrlChecked()
    {
        return !$this->getFlag('', self::FLAG_IS_URLS_CHECKED)
            && !$this->getRequest()->getParam('forwarded')
            && !$this->_getSession()->getIsUrlNotice(true)
            && !$this->_objectManager->get('Magento_Core_Model_Config')->getNode('global/can_use_base_url');
    }

    /**
     * Check url keys. If non valid - redirect
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if (\Mage::getSingleton('Magento\Backend\Model\Auth\Session')->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_isValidFormKey = $this->_validateFormKey();
                $_keyErrorMsg = __('Invalid Form Key. Please refresh the page.');
            } elseif (\Mage::getSingleton('Magento\Backend\Model\Url')->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                    'error' => true,
                    'message' => $_keyErrorMsg
                )));
            } else {
                $this->_redirect(\Mage::getSingleton('Magento\Backend\Model\Url')->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }

    /**
     * Set session locale,
     * process force locale set through url params
     *
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _processLocaleSettings()
    {
        $forceLocale = $this->getRequest()->getParam('locale', null);
        if ($this->_objectManager->get('Magento\Core\Model\Locale\Validator')->isValid($forceLocale)) {
            $this->_getSession()->setSessionLocale($forceLocale);
        }

        if (is_null($this->_getSession()->getLocale())) {
            $this->_getSession()->setLocale(\Mage::app()->getLocale()->getLocaleCode());
        }

        return $this;
    }

    /**
     * Fire predispatch events, execute extra logic after predispatch
     *
     * @return void
     */
    protected function _firePreDispatchEvents()
    {
        $this->_initAuthentication();
        parent::_firePreDispatchEvents();
    }

    /**
     * Start authentication process
     *
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _initAuthentication()
    {
        /** @var $auth \Magento\Backend\Model\Auth */
        $auth = \Mage::getSingleton('Magento\Backend\Model\Auth');

        $request = $this->getRequest();

        $requestedActionName = $request->getActionName();
        $openActions = array(
            'forgotpassword',
            'resetpassword',
            'resetpasswordpost',
            'logout',
            'refresh' // captcha refresh
        );
        if (in_array($requestedActionName, $openActions)) {
            $request->setDispatched(true);
        } else {
            if ($auth->getUser()) {
                $auth->getUser()->reload();
            }
            if (!$auth->isLoggedIn()) {
                $this->_processNotLoggedInUser($request);
            }
        }
        $auth->getAuthStorage()->refreshAcl();
        return $this;
    }

    /**
     * Process not logged in user data
     *
     * @param \Magento\Core\Controller\Request\Http $request
     */
    protected function _processNotLoggedInUser(\Magento\Core\Controller\Request\Http $request)
    {
        $isRedirectNeeded = false;
        if ($request->getPost('login') && $this->_performLogin()) {
            $isRedirectNeeded = $this->_redirectIfNeededAfterLogin();
        }
        if (!$isRedirectNeeded && !$request->getParam('forwarded')) {
            if ($request->getParam('isIframe')) {
                $request->setParam('forwarded', true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('deniedIframe')
                    ->setDispatched(false);
            } elseif ($request->getParam('isAjax')) {
                $request->setParam('forwarded', true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('deniedJson')
                    ->setDispatched(false);
            } else {
                $request->setParam('forwarded', true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('login')
                    ->setDispatched(false);
            }
        }
    }

    /**
     * Performs login, if user submitted login form
     *
     * @return boolean
     */
    protected function _performLogin()
    {
        $outputValue = true;
        $postLogin  = $this->getRequest()->getPost('login');
        $username   = isset($postLogin['username']) ? $postLogin['username'] : '';
        $password   = isset($postLogin['password']) ? $postLogin['password'] : '';
        $this->getRequest()->setPost('login', null);

        try {
            \Mage::getSingleton('Magento\Backend\Model\Auth')->login($username, $password);
        } catch (\Magento\Backend\Model\Auth\Exception $e) {
            if (!$this->getRequest()->getParam('messageSent')) {
                $this->_session->addError($e->getMessage());
                $this->getRequest()->setParam('messageSent', true);
                $outputValue = false;
            }
        }
        return $outputValue;
    }

    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     *
     * @return bool
     */
    protected function _redirectIfNeededAfterLogin()
    {
        $requestUri = null;

        /** @var $urlModel \Magento\Backend\Model\Url */
        $urlModel = \Mage::getSingleton('Magento\Backend\Model\Url');

        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($urlModel->useSecretKey()) {
            $requestUri = $urlModel->getUrl('*/*/*', array('_current' => true));
        } elseif ($this->getRequest()) {
            $requestUri = $this->getRequest()->getRequestUri();
        }

        if (!$requestUri) {
            return false;
        }

        $this->getResponse()->setRedirect($requestUri);
        $this->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
        return true;
    }

    public function deniedAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
        if (!\Mage::getSingleton('Magento\Backend\Model\Auth\Session')->isLoggedIn()) {
            $this->_redirect('*/auth/login');
            return;
        }
        $this->loadLayout(array('default', 'adminhtml_denied'));
        $this->renderLayout();
    }

    /**
     * Load layout by handles and verify user ACL
     *
     * @param string|null|bool|array $ids
     * @param bool $generateBlocks
     * @param bool $generateXml
     * @return \Magento\Backend\Controller\ActionAbstract|\Magento\Core\Controller\Varien\Action
     */
    public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true)
    {
        parent::loadLayout($ids, false, $generateXml);
        $this->_objectManager->get('Magento\Core\Model\Layout\Filter\Acl')
            ->filterAclNodes($this->getLayout()->getNode());
        if ($generateBlocks) {
            $this->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;
        }
        $this->_initLayoutMessages('Magento\Backend\Model\Session');
        return $this;
    }

    /**
     * No route action
     *
     * @param null $coreRoute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function norouteAction($coreRoute = null)
    {
        $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
        $this->getResponse()->setHeader('Status', '404 File not found');
        $this->loadLayout(array('default', 'adminhtml_noroute'));
        $this->renderLayout();
    }

    /**
     * Set referrer url for redirect in response
     *
     * Is overridden here to set defaultUrl to admin url
     *
     * @param   string $defaultUrl
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _redirectReferer($defaultUrl = null)
    {
        $defaultUrl = empty($defaultUrl) ? $this->getUrl('*') : $defaultUrl;
        parent::_redirectReferer($defaultUrl);
        return $this;
    }

    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _redirect($path, $arguments=array())
    {
        $this->_getSession()->setIsUrlNotice($this->getFlag('', self::FLAG_IS_URLS_CHECKED));
        $this->getResponse()->setRedirect($this->getUrl($path, $arguments));
        return $this;
    }

    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        $this->_getSession()->setIsUrlNotice($this->getFlag('', self::FLAG_IS_URLS_CHECKED));
        return parent::_forward($action, $controller, $module, $params);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params=array())
    {
        return $this->_getHelper()->getUrl($route, $params);
    }

    /**
     * Validate Secret Key
     *
     * @return bool
     */
    protected function _validateSecretKey()
    {
        if (is_array($this->_publicActions) && in_array($this->getRequest()->getActionName(), $this->_publicActions)) {
            return true;
        }

        $secretKey = $this->getRequest()->getParam(\Magento\Backend\Model\Url::SECRET_KEY_PARAM_NAME, null);
        if (!$secretKey || $secretKey != \Mage::getSingleton('Magento\Backend\Model\Url')->getSecretKey()) {
            return false;
        }
        return true;
    }

    /**
     * Render specified template
     *
     * @param string $tplName
     * @param array $data parameters required by template
     */
    protected function _outTemplate($tplName, $data = array())
    {
        $this->_initLayoutMessages('Magento\Backend\Model\Session');
        $block = $this->getLayout()->createBlock('Magento\Backend\Block\Template')->setTemplate("{$tplName}.phtml");
        foreach ($data as $index => $value) {
            $block->assign($index, $value);
        }
        $html = $block->toHtml();
        $this->_objectManager->get('Magento\Core\Model\Translate')->processResponseBody($html);
        $this->getResponse()->setBody($html);
    }

    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     * that case
     * @param string $contentType
     * @param int $contentLength    explicit content length, if strlen($content) isn't applicable
     * @return \Magento\Backend\Controller\ActionAbstract
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        $session = \Mage::getSingleton('Magento\Backend\Model\Auth\Session');
        if ($session->isFirstPageAfterLogin()) {
            $this->_redirect(\Mage::getSingleton('Magento\Backend\Model\Url')->getStartupPageUrl());
            return $this;
        }
        return parent::_prepareDownloadResponse($fileName, $content, $contentType, $contentLength);
    }
}
