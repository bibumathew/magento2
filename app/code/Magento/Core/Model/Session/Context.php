<?php
/**
 * Core Session Context Model
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Core\Model\Session;

class Context implements \Magento\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Core\Model\Session\Validator
     */
    protected $_validator;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Core\Model\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var \Magento\Core\Helper\Http
     */
    protected $_httpHelper;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var string
     */
    protected $_saveMethod;

    /**
     * @var string
     */
    protected $_savePath;

    /**
     * @var string
     */
    protected $_cacheLimiter;

    /**
     * Mapping between area and SID param name
     *
     * @var array
     */
    protected $sidMap;

    /**
     * Core cookie
     *
     * @var \Magento\Core\Model\Cookie
     */
    protected $_cookie;

    /**
     * Core message
     *
     * @var \Magento\Core\Model\Message
     */
    protected $_message;

    /**
     * Core message collection factory
     *
     * @var \Magento\Core\Model\Message\CollectionFactory
     */
    protected $_messageFactory;

    /**
     * @var \Magento\Core\Controller\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Core\Model\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Dir
     */
    protected $_dir;

    /**
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * @param \Magento\Core\Model\Session\Validator $validator
     * @param \Magento\Logger $logger
     * @param \Magento\Core\Model\Event\Manager $eventManager
     * @param \Magento\Core\Helper\Http $coreHttp
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Message\CollectionFactory $messageFactory
     * @param \Magento\Core\Model\Message $message
     * @param \Magento\Core\Model\Cookie $cookie
     * @param \Magento\Core\Controller\Request\Http $request
     * @param \Magento\Core\Model\App\State $appState
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Dir $dir
     * @param \Magento\Core\Model\Url $url
     * @param $saveMethod
     * @param null $savePath
     * @param null $cacheLimiter
     * @param array $sidMap
     */
    public function __construct(
        \Magento\Core\Model\Session\Validator $validator,
        \Magento\Logger $logger,
        \Magento\Core\Model\Event\Manager $eventManager,
        \Magento\Core\Helper\Http $coreHttp,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Message\CollectionFactory $messageFactory,
        \Magento\Core\Model\Message $message,
        \Magento\Core\Model\Cookie $cookie,
        \Magento\Core\Controller\Request\Http $request,
        \Magento\Core\Model\App\State $appState,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Dir $dir,
        \Magento\Core\Model\Url $url,
        $saveMethod,
        $savePath = null,
        $cacheLimiter = null,
        $sidMap = array()
    ) {
        $this->_validator = $validator;
        $this->_logger = $logger;
        $this->_eventManager = $eventManager;
        $this->_httpHelper = $coreHttp;
        $this->_storeConfig = $coreStoreConfig;
        $this->_saveMethod = $saveMethod;
        $this->_savePath = $savePath;
        $this->_cacheLimiter = $cacheLimiter;
        $this->sidMap = $sidMap;
        $this->_messageFactory = $messageFactory;
        $this->_message = $message;
        $this->_cookie = $cookie;
        $this->_request = $request;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
        $this->_dir = $dir;
        $this->_url = $url;
    }

    /**
     * @return \\Magento\Core\Model\Event\Manager
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \\Magento\Core\Helper\Http
     */
    public function getHttpHelper()
    {
        return $this->_httpHelper;
    }

    /**
     * @return \\Magento\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \\Magento\Core\Model\Store\Config
     */
    public function getStoreConfig()
    {
        return $this->_storeConfig;
    }

    /**
     * @return \\Magento\Core\Model\Session\Validator
     */
    public function getValidator()
    {
        return $this->_validator;
    }

    /**
     * @return string
     */
    public function getCacheLimiter()
    {
        return $this->_cacheLimiter;
    }

    /**
     * @return string
     */
    public function getSaveMethod()
    {
        return $this->_saveMethod;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->_savePath;
    }

    /**
     * @return array
     */
    public function getSidMap()
    {
        return $this->sidMap;
    }

    /**
     * @return \\Magento\Core\Model\App\State
     */
    public function getAppState()
    {
        return $this->_appState;
    }

    /**
     * @return \\Magento\Core\Model\Cookie
     */
    public function getCookie()
    {
        return $this->_cookie;
    }

    /**
     * @return \\Magento\Core\Model\Dir
     */
    public function getDir()
    {
        return $this->_dir;
    }

    /**
     * @return \\Magento\Core\Model\Message
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @return \\Magento\Core\Model\Message\CollectionFactory
     */
    public function getMessageFactory()
    {
        return $this->_messageFactory;
    }

    /**
     * @return \\Magento\Core\Controller\Request\Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return \\Magento\Core\Model\StoreManager
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * @return \\Magento\Core\Model\Url
     */
    public function getUrl()
    {
        return $this->_url;
    }
}
