<?php
/**
 * Base router
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Core\App\Router;

class Base extends \Magento\App\Router\AbstractRouter
{
    /**
     * @var array
     */
    protected $_modules = array();

    /**
     * @var array
     */
    protected $_dispatchData = array();

    /**
     * List of required request parameters
     * Order sensitive
     * @var array
     */
    protected $_requiredParams = array(
        'moduleFrontName',
        'controllerName',
        'actionName',
    );

    /**
     * @var array
     */
    protected $_routes;

    /**
     * Url security information.
     *
     * @var \Magento\Core\Model\Url\SecurityInfoInterface
     */
    protected $_urlSecurityInfo;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * Core config
     *
     * @var \Magento\Core\Model\Config
     */
    protected $_config = null;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\App\DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * @param \Magento\App\ActionFactory $actionFactory
     * @param \Magento\App\DefaultPathInterface $defaultPath
     * @param \Magento\App\ResponseFactory $responseFactory
     * @param \Magento\App\Route\Config $routeConfig
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\Url|\Magento\UrlInterface $url
     * @param \Magento\Core\Model\StoreManager|\Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Core\Model\Url\SecurityInfoInterface $urlSecurityInfo
     * @param $routerId
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\App\ActionFactory $actionFactory,
        \Magento\App\DefaultPathInterface $defaultPath,
        \Magento\App\ResponseFactory $responseFactory,
        \Magento\App\Route\Config $routeConfig,
        \Magento\App\State $appState,
        \Magento\UrlInterface $url,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Core\Model\Url\SecurityInfoInterface $urlSecurityInfo,
        $routerId
    ) {
        parent::__construct($actionFactory);

        $this->_responseFactory = $responseFactory;
        $this->_defaultPath     = $defaultPath;
        $this->_routes          = $routeConfig->getRoutes($routerId);
        $this->_urlSecurityInfo = $urlSecurityInfo;
        $this->_storeConfig     = $storeConfig;
        $this->_url             = $url;
        $this->_storeManager    = $storeManager;
        $this->_appState        = $appState;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\Core\Controller\Front\Action|null
     */
    public function match(\Magento\App\RequestInterface $request)
    {
        $params = $this->_parseRequest($request);

        return $this->_matchController($request, $params);
    }

    /**
     * Parse request URL params
     *
     * @param \Magento\App\RequestInterface $request
     * @return array
     */
    protected function _parseRequest(\Magento\App\RequestInterface $request)
    {
        $output = array();

        $path = trim($request->getPathInfo(), '/');

        $params = explode('/', ($path ? $path : $this->_getDefaultPath()));
        foreach ($this->_requiredParams as $paramName) {
            $output[$paramName] = array_shift($params);
        }

        for ($i = 0, $l = sizeof($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i+1]) ? urldecode($params[$i + 1]) : '';
        }
        return $output;
    }

    /**
     * Match module front name
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $param
     * @return string|null
     */
    protected function _matchModuleFrontName(\Magento\App\RequestInterface $request, $param)
    {
        // get module name
        if ($request->getModuleName()) {
            $moduleFrontName = $request->getModuleName();
        } else {
            if (!empty($param)) {
                $moduleFrontName = $param;
            } else {
                $moduleFrontName = $this->_defaultPath->getPart('module');
                $request->setAlias(\Magento\Core\Model\Url\Rewrite::REWRITE_REQUEST_PATH_ALIAS, '');
            }
        }
        if (!$moduleFrontName) {
            return null;
        }
        return $moduleFrontName;
    }

    /**
     * Match controller name
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $param
     * @return string
     */
    protected function _matchControllerName(\Magento\App\RequestInterface $request,  $param)
    {
        if ($request->getControllerName()) {
            $controller = $request->getControllerName();
        } else {
            if (!empty($param)) {
                $controller = $param;
            } else {
                $controller = $this->_defaultPath->getPart('controller');
                $request->setAlias(
                    \Magento\Core\Model\Url\Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                    ltrim($request->getOriginalPathInfo(), '/')
                );
            }
        }
        return $controller;
    }

    /**
     * Match controller name
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $param
     * @return string
     */
    protected function _matchActionName(\Magento\App\RequestInterface $request, $param)
    {
        if (empty($action)) {
            if ($request->getActionName()) {
                $action = $request->getActionName();
            } else {
                $action = !empty($param) ? $param : $this->_defaultPath->getPart('action');
            }
        } else {
            $action = $param;
        }

        return $action;
    }

    /**
     * Get not found controller instance
     *
     * @param $currentModuleName
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\Core\Controller\Varien\Action|null
     */
    protected function _getNotFoundControllerInstance($currentModuleName, \Magento\App\RequestInterface $request)
    {
        $controllerInstance = null;

        if ($this->_noRouteShouldBeApplied()) {
            $controller = 'index';
            $action = 'noroute';

            $controllerClassName = $this->getControllerClassName($currentModuleName, $controller);
            if (false == $controllerClassName) {
                return null;
            }

            if (false == method_exists($controllerClassName, $action . 'Action')) {
                return null;
            }

            // instantiate controller class
            $controllerInstance = $this->_actionFactory->createController($controllerClassName,
                array('request' => $request)
            );
        } else {
            return null;
        }

        return $controllerInstance;
    }

    /**
     * Create matched controller instance
     *
     * @param \Magento\App\RequestInterface $request
     * @param array $params
     * @return \Magento\Core\Controller\Front\Action|null
     */
    protected function _matchController(\Magento\App\RequestInterface $request, array $params)
    {
        $moduleFrontName = $this->_matchModuleFrontName($request, $params['moduleFrontName']);
        if (empty($moduleFrontName)) {
            return null;
        }

        /**
         * Searching router args by module name from route using it as key
         */
        $modules = $this->getModulesByFrontName($moduleFrontName);

        if (empty($modules) === true) {
            return null;
        }

        /**
         * Going through modules to find appropriate controller
         */
        $currentModuleName = null;
        $controller = null;
        $action = null;
        $controllerInstance = null;

        $request->setRouteName($this->getRouteByFrontName($moduleFrontName));
        $controller = $this->_matchControllerName($request, $params['controllerName']);
        $action = $this->_matchActionName($request, $params['actionName']);
        $this->_checkShouldBeSecure($request, '/' . $moduleFrontName . '/' . $controller . '/' . $action);

        foreach ($modules as $moduleName) {
            $currentModuleName = $moduleName;

            $controllerClassName = $this->getControllerClassName($moduleName, $controller);
            if (!$controllerClassName || false === method_exists($controllerClassName, $action . 'Action')) {
                continue;
            }

            $controllerInstance = $this->_actionFactory->createController($controllerClassName,
                array('request' => $request)
            );
            break;
        }

        if (null == $controllerInstance) {
            $controllerInstance = $this->_getNotFoundControllerInstance($currentModuleName, $request);
            if (is_null($controllerInstance)) {
                return null;
            }
        }

        // set values only after all the checks are done
        $request->setModuleName($moduleFrontName);
        $request->setControllerName($controller);
        $request->setActionName($action);
        $request->setControllerModule($currentModuleName);
        if (isset($params['variables'])) {
            $request->setParams($params['variables']);
        }
        return $controllerInstance;
    }

    /**
     * Get router default request path
     * @return string
     */
    protected function _getDefaultPath()
    {
        return $this->_storeConfig->getConfig('web/default/front');
    }

    /**
     * Allow to control if we need to enable no route functionality in current router
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return false;
    }

    /**
     * Retrieve list of modules subscribed to given frontName
     *
     * @param string $frontName
     * @return array
     */
    public function getModulesByFrontName($frontName)
    {
        $modules = array();

        foreach ($this->_routes as $routeData) {
            if ($routeData['frontName'] == $frontName && isset($routeData['modules'])) {
                $modules = $routeData['modules'];
                break;
            }
        }

        return array_unique($modules);
    }

    /**
     * Get route frontName by id
     * @param string $routeId
     * @return string
     */
    public function getFrontNameByRoute($routeId)
    {
        if (isset($this->_routes[$routeId])) {
            return $this->_routes[$routeId]['frontName'];
        }

        return false;
    }

    /**
     * Get route Id by route frontName
     *
     * @param string $frontName
     * @return string
     */
    public function getRouteByFrontName($frontName)
    {
        foreach ($this->_routes as $routeId => $routeData) {
            if ($routeData['frontName'] == $frontName) {
                return $routeId;
            }
        }

        return false;
    }

    /**
     * Build controller class name
     *
     * @param string $realModule
     * @param string $controller
     * @return string
     */
    public function getControllerClassName($realModule, $controller)
    {
        $class = str_replace('_', \Magento\Autoload\IncludePath::NS_SEPARATOR, $realModule) .
            \Magento\Autoload\IncludePath::NS_SEPARATOR . 'Controller' .
            \Magento\Autoload\IncludePath::NS_SEPARATOR .
            str_replace('_','\\', uc_words(str_replace('_', ' ', $controller)));
        return $class;
    }

    /**
     * Check that request uses https protocol if it should.
     * Function redirects user to correct URL if needed.
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $path
     * @return void
     */
    protected function _checkShouldBeSecure(\Magento\App\RequestInterface $request, $path = '')
    {
        if (!$this->_appState->isInstalled() || $request->getPost()) {
            return;
        }

        if ($this->_shouldBeSecure($path) && !$request->isSecure()) {
            $url = $this->_getCurrentSecureUrl($request);
            if ($this->_shouldRedirectToSecure()) {
                $url = $this->_url->getRedirectUrl($url);
            }

            $this->_responseFactory->create()
                ->setRedirect($url)
                ->sendResponse();
            exit;
        }
    }

    /**
     * Check whether redirect url should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return $this->_url->getUseSession();
    }

    /**
     * Retrieve secure url for current request
     *
     * @param \Magento\App\RequestInterface $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        $alias = $request->getAlias(\Magento\Core\Model\Url\Rewrite::REWRITE_REQUEST_PATH_ALIAS);
        if ($alias) {
            return $this->_storeManager->getStore()->getBaseUrl('link', true) . ltrim($alias, '/');
        }

        return $this->_storeManager->getStore()->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * Check whether given path should be secure according to configuration security requirements for URL
     * "Secure" should not be confused with https protocol, it is about web/secure/*_url settings usage only
     *
     * @param string $path
     * @return bool
     */
    protected function _shouldBeSecure($path)
    {
        return substr($this->_storeConfig->getConfig('web/unsecure/base_url'), 0, 5) === 'https'
            || $this->_storeConfig->getConfigFlag('web/secure/use_in_frontend')
                && substr($this->_storeConfig->getConfig('web/secure/base_url'), 0, 5) == 'https'
                && $this->_urlSecurityInfo->isSecure($path);
    }
}
