<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Backend\App\Action;

/**
 * Backend Controller context
 */
class Context extends \Magento\App\Action\Context
{
    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var bool
     */
    protected $_canUseBaseUrl;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\App\ResponseInterface $response
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\FrontController $frontController
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Core\Model\Url $url
     * @param \Magento\HTTP\Url $appUrl
     * @param \Magento\App\Request\Redirect $redirect
     * @param \Magento\App\ActionFlag $actionFlag
     * @param \Magento\View\Action\LayoutServiceInterface $layoutService
     * @param \Magento\AuthorizationInterface $authorization
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\Backend\Model\Url $backendUrl
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\App\Action\Title $title
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param bool $canUseBaseUrl
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\App\ResponseInterface $response,
        \Magento\ObjectManager $objectManager,
        \Magento\App\FrontController $frontController,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session $session,
        \Magento\Core\Model\Url $url,
        \Magento\HTTP\Url $appUrl,
        \Magento\App\Request\Redirect $redirect,
        \Magento\App\ActionFlag $actionFlag,
        \Magento\View\Action\LayoutServiceInterface $layoutService,
        \Magento\AuthorizationInterface $authorization,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Helper\Data $helper,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\App\Action\Title $title,
        \Magento\Core\Model\LocaleInterface $locale,
        $canUseBaseUrl = false
    ) {
        parent::__construct(
            $request,
            $response,
            $objectManager,
            $frontController,
            $eventManager,
            $storeManager,
            $session,
            $url,
            $appUrl,
            $redirect,
            $actionFlag,
            $layoutService
        );

        $this->_authorization = $authorization;
        $this->_auth = $auth;
        $this->_helper = $helper;
        $this->_backendUrl = $backendUrl;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_title = $title;
        $this->_locale = $locale;
        $this->_canUseBaseUrl = $canUseBaseUrl;
    }

    /**
     * @return \Magento\Backend\Model\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @return \Magento\AuthorizationInterface
     */
    public function getAuthorization()
    {
        return $this->_authorization;
    }

    /**
     * @return \Magento\Backend\Model\Url
     */
    public function getBackendUrl()
    {
        return $this->_backendUrl;
    }

    /**
     * @return boolean
     */
    public function getCanUseBaseUrl()
    {
        return $this->_canUseBaseUrl;
    }

    /**
     * @return \Magento\Core\App\Action\FormKeyValidator
     */
    public function getFormKeyValidator()
    {
        return $this->_formKeyValidator;
    }

    /**
     * @return \Magento\Backend\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return \Magento\Core\Model\LocaleInterface
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * @return \Magento\App\Action\Title
     */
    public function getTitle()
    {
        return $this->_title;
    }
}