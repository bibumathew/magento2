<?php
/**
 * Core Session Abstract model
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Core\Model\Session;

/**
 * Session Manager
 */
class AbstractSession
{
    /**
     * Configuration path to log exception file
     */
    const XML_PATH_LOG_EXCEPTION_FILE = 'dev/log/exception_file';

    /**
     * Session key for list of hosts
     */
    const HOST_KEY = '_session_hosts';

    /**
     * Default options when a call destroy()
     *
     * - send_expire_cookie: whether or not to send a cookie expiring the current session cookie
     * - clear_storage: whether or not to empty the storage object of any stored values
     *
     * @var array
     */
    protected $defaultDestroyOptions = array(
        'send_expire_cookie' => true,
        'clear_storage'      => true,
    );

    /**
     * URL host cache
     *
     * @var array
     */
    protected static $_urlHostCache = array();

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Session\ValidatorInterface
     */
    protected $_validator;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Core message
     *
     * @var \Magento\Message\Factory
     */
    protected $messageFactory;

    /**
     * Core message collection factory
     *
     * @var \Magento\Message\CollectionFactory
     */
    protected $messagesFactory;

    /**
     * @var \Magento\Message\Collection
     */
    protected $_messages;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var \Magento\Session\Config\ConfigInterface
     */
    protected $_sessionConfig;

    /**
     * @var \Magento\Session\SaveHandlerInterface
     */
    protected $saveHandler;

    /**
     * @var \Magento\Session\StorageInterface
     */
    protected $storage;

    /**
     * @param \Magento\Core\Model\Session\Context $context
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param \Magento\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Session\ValidatorInterface $validator
     * @param \Magento\Session\StorageInterface $storage
     */
    public function __construct(
        \Magento\Core\Model\Session\Context $context,
        \Magento\Session\SidResolverInterface $sidResolver,
        \Magento\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Session\SaveHandlerInterface $saveHandler,
        \Magento\Session\ValidatorInterface $validator,
        \Magento\Session\StorageInterface $storage
    ) {
        $this->_validator = $validator;
        $this->_eventManager = $context->getEventManager();
        $this->_logger = $context->getLogger();
        $this->_coreStoreConfig = $context->getStoreConfig();
        $this->messagesFactory = $context->getMessagesFactory();
        $this->messageFactory = $context->getMessageFactory();
        $this->_request = $context->getRequest();
        $this->_storeManager = $context->getStoreManager();
        $this->_sidResolver = $sidResolver;
        $this->_sessionConfig = $sessionConfig;
        $this->saveHandler = $saveHandler;
        $this->storage = $storage;
    }

    /**
     * This method needs to support sessions with APC enabled
     */
    public function writeClose()
    {
        session_write_close();
    }

    /**
     * Storage accessor method
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __call($method, $args)
    {
        if (!in_array(substr($method, 0, 3), array('get', 'set', 'uns', 'has'))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid method %s::%s(%s)', get_class($this), $method, print_r($args, 1))
            );
        }
        return call_user_func_array(array($this->storage, $method), $args);
    }

    /**
     * Configure session handler and start session
     *
     * @param string $sessionName
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function start($sessionName = null)
    {
        if (!$this->isSessionExists()) {
            \Magento\Profiler::start('session_start');
            if (!empty($sessionName)) {
                $this->setName($sessionName);
            }
            $this->registerSaveHandler();

            // potential custom logic for session id (ex. switching between hosts)
            $this->setSessionId($this->_sidResolver->getSid($this));
            session_start();
            $this->_validator->validate($this);

            register_shutdown_function(array($this, 'writeClose'));

            $this->_addHost();
            \Magento\Profiler::stop('session_start');
        }
        $this->storage->init($_SESSION);
        return $this;
    }

    /**
     * Register save handler
     *
     * @return bool
     */
    protected function registerSaveHandler()
    {
        return session_set_save_handler(
            array($this->saveHandler, 'open'),
            array($this->saveHandler, 'close'),
            array($this->saveHandler, 'read'),
            array($this->saveHandler, 'write'),
            array($this->saveHandler, 'destroy'),
            array($this->saveHandler, 'gc')
        );
    }

    /**
     * Does a session exist
     *
     * @return bool
     */
    public function isSessionExists()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            return false;
        }
        return true;
    }

    /**
     * Additional get data with clear mode
     *
     * @param string $key
     * @param bool $clear
     * @return mixed
     */
    public function getData($key = '', $clear = false)
    {
        $data = $this->storage->getData($key);
        if ($clear && $data) {
            $this->storage->unsetData($key);
        }
        return $data;
    }

    /**
     * Retrieve session Id
     *
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * Retrieve session name
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Set session name
     *
     * @param string $name
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function setName($name)
    {
        session_name($name);
        return $this;
    }

    /**
     * Destroy/end a session
     *
     * @param  array $options
     */
    public function destroy(array $options = null)
    {
        if (null === $options) {
            $options = $this->defaultDestroyOptions;
        } else {
            $options = array_merge($this->defaultDestroyOptions, $options);
        }

        if ($options['clear_storage']) {
            $this->clearStorage();
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        session_destroy();
        if ($options['send_expire_cookie']) {
            $this->expireSessionCookie();
        }
    }

    /**
     * Unset all session data
     *
     * @return $this
     */
    public function clearStorage()
    {
        $this->unsetData();
        return $this;
    }

    /**
     * Retrieve Cookie domain
     *
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->_sessionConfig->getCookieDomain();
    }

    /**
     * Retrieve cookie path
     *
     * @return string
     */
    public function getCookiePath()
    {
        return $this->_sessionConfig->getCookiePath();
    }

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     */
    public function getCookieLifetime()
    {
        return $this->_sessionConfig->getCookieLifetime();
    }

    /**
     * Retrieve messages from session
     *
     * @param bool $clear
     * @return \Magento\Message\Collection
     */
    public function getMessages($clear = false)
    {
        if (!$this->_messages) {
            $this->_messages = $this->messagesFactory->create();
        }
        if ($clear) {
            $messages = clone $this->_messages;
            $this->_messages->clear();
            $this->_eventManager->dispatch('core_session_abstract_clear_messages');
            return $messages;
        }
        return $this->_messages;
    }

    /**
     * Not Magento exception handling
     *
     * @param \Exception $exception
     * @param string $alternativeText
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function addException(\Exception $exception, $alternativeText)
    {
        // log exception to exceptions log
        $message = sprintf('Exception message: %s%sTrace: %s',
            $exception->getMessage(),
            "\n",
            $exception->getTraceAsString());
        $file = $this->_coreStoreConfig->getConfig(self::XML_PATH_LOG_EXCEPTION_FILE);
        $this->_logger->logFile($message, \Zend_Log::DEBUG, $file);

        $this->addMessage(
            $this->messageFactory->create(\Magento\Message\MessageInterface::TYPE_ERROR, $alternativeText)
        );
        return $this;
    }

    /**
     * Adding new message to message collection
     *
     * @param   \Magento\Message\AbstractMessage $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addMessage(\Magento\Message\AbstractMessage $message)
    {
        $this->getMessages()->addMessage($message);
        $this->_eventManager->dispatch('core_session_abstract_add_message');
        return $this;
    }

    /**
     * Adding new error message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addError($message)
    {
        $this->addMessage($this->messageFactory->create(\Magento\Message\MessageInterface::TYPE_ERROR, $message));
        return $this;
    }

    /**
     * Adding new warning message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addWarning($message)
    {
        $this->addMessage($this->messageFactory->create(\Magento\Message\MessageInterface::TYPE_WARNING, $message));
        return $this;
    }

    /**
     * Adding new notice message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addNotice($message)
    {
        $this->addMessage($this->messageFactory->create(\Magento\Message\MessageInterface::TYPE_NOTICE, $message));
        return $this;
    }

    /**
     * Adding new success message
     *
     * @param   string $message
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addSuccess($message)
    {
        $this->addMessage($this->messageFactory->create(\Magento\Message\MessageInterface::TYPE_SUCCESS, $message));
        return $this;
    }

    /**
     * Adding messages array to message collection
     *
     * @param   array $messages
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function addMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->addMessage($message);
            }
        }
        return $this;
    }

    /**
     * Adds messages array to message collection, but doesn't add duplicates to it
     *
     * @param array|string|\Magento\Message\AbstractMessage $messages
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function addUniqueMessages($messages)
    {
        if (!is_array($messages)) {
            $messages = array($messages);
        }
        if (!$messages) {
            return $this;
        }

        $messagesAlready = array();
        $items = $this->getMessages()->getItems();
        foreach ($items as $item) {
            if ($item instanceof \Magento\Message\AbstractMessage) {
                $text = $item->getText();
            } elseif (is_string($item)) {
                $text = $item;
            } else {
                continue; // Some unknown object, do not put it in already existing messages
            }
            $messagesAlready[$text] = true;
        }

        foreach ($messages as $message) {
            if ($message instanceof \Magento\Message\AbstractMessage) {
                $text = $message->getText();
            } elseif (is_string($message)) {
                $text = $message;
            } else {
                $text = null; // Some unknown object, add it anyway
            }

            // Check for duplication
            if ($text !== null) {
                if (isset($messagesAlready[$text])) {
                    continue;
                }
                $messagesAlready[$text] = true;
            }
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * Specify session identifier
     *
     * @param   string|null $sessionId
     * @return  \Magento\Core\Model\Session\AbstractSession
     */
    public function setSessionId($sessionId)
    {
        $this->_addHost();
        if (!is_null($sessionId) && preg_match('#^[0-9a-zA-Z,-]+$#', $sessionId)) {
            session_id($sessionId);
        }
        return $this;
    }

    /**
     * If session cookie is not applicable due to host or path mismatch - add session id to query
     *
     * @param string $urlHost can be host or url
     * @return string {session_id_key}={session_id_encrypted}
     */
    public function getSessionIdForHost($urlHost)
    {
        $httpHost = $this->_request->getHttpHost();
        if (!$httpHost) {
            return '';
        }

        $urlHostArr = explode('/', $urlHost, 4);
        if (!empty($urlHostArr[2])) {
            $urlHost = $urlHostArr[2];
        }
        $urlPath = empty($urlHostArr[3]) ? '' : $urlHostArr[3];

        if (!isset(self::$_urlHostCache[$urlHost])) {
            $urlHostArr = explode(':', $urlHost);
            $urlHost = $urlHostArr[0];
            $sessionId = $httpHost !== $urlHost && !$this->isValidForHost($urlHost)
                ? $this->getSessionId() : '';
            self::$_urlHostCache[$urlHost] = $sessionId;
        }

        return $this->isValidForPath($urlPath) ? self::$_urlHostCache[$urlHost] : $this->getSessionId();
    }

    /**
     * Check if session is valid for given hostname
     *
     * @param string $host
     * @return bool
     */
    public function isValidForHost($host)
    {
        $hostArr = explode(':', $host);
        $hosts = $this->_getHosts();
        return (!empty($hosts[$hostArr[0]]));
    }

    /**
     * Check if session is valid for given path
     *
     * @param string $path
     * @return bool
     */
    public function isValidForPath($path)
    {
        $cookiePath = trim($this->getCookiePath(), '/') . '/';
        if ($cookiePath == '/') {
            return true;
        }

        $urlPath = trim($path, '/') . '/';
        return strpos($urlPath, $cookiePath) === 0;
    }

    /**
     * Register request host name as used with session
     *
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    protected function _addHost()
    {
        $host = $this->_request->getHttpHost();
        if (!$host) {
            return $this;
        }

        $hosts = $this->_getHosts();
        $hosts[$host] = true;
        $_SESSION[self::HOST_KEY] = $hosts;
        return $this;
    }

    /**
     * Get all host names where session was used
     *
     * @return array
     */
    protected function _getHosts()
    {
        return isset($_SESSION[self::HOST_KEY]) ? $_SESSION[self::HOST_KEY] : array();
    }

    /**
     * Clean all host names that were registered with session
     *
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    protected function _cleanHosts()
    {
        unset($_SESSION[self::HOST_KEY]);
        return $this;
    }

    /**
     * Renew session id and update session cookie
     *
     * @param bool $deleteOldSession
     * @return \Magento\Core\Model\Session\AbstractSession
     */
    public function regenerateId($deleteOldSession = true)
    {
        if ($this->isSessionExists()) {
            return $this;
        }
        session_regenerate_id($deleteOldSession);

        if ($this->_sessionConfig->getUseCookies()) {
            $this->clearSubDomainSessionCookie();
        }
        return $this;
    }

    /**
     * Expire the session cookie for sub domains
     */
    protected function clearSubDomainSessionCookie()
    {
        foreach (array_keys($this->_getHosts()) as $host) {
            // Delete cookies with the same name for parent domains
            if (strpos($this->_sessionConfig->getCookieDomain(), $host) > 0) {
                setcookie(
                    $this->getName(),
                    '',
                    0,
                    $this->_sessionConfig->getCookiePath(),
                    $host,
                    $this->_sessionConfig->getCookieSecure(),
                    $this->_sessionConfig->getCookieHttpOnly()
                );
            }
        }
    }

    /**
     * Expire the session cookie
     *
     * Sends a session cookie with no value, and with an expiry in the past.
     */
    public function expireSessionCookie()
    {
        if (!$this->_sessionConfig->getUseCookies()) {
            return;
        }

        setcookie(
            $this->getName(),                 // session name
            '',                               // value
            0,                                // TTL for cookie
            $this->_sessionConfig->getCookiePath(),
            $this->_sessionConfig->getCookieDomain(),
            $this->_sessionConfig->getCookieSecure(),
            $this->_sessionConfig->getCookieHttpOnly()
        );
        $this->clearSubDomainSessionCookie();
    }
}
