<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Oauth
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * oAuth token model
 *
 * @category    Magento
 * @package     Magento_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method string getName() Consumer name (joined from consumer table)
 * @method Magento_Oauth_Model_Resource_Token_Collection getCollection()
 * @method Magento_Oauth_Model_Resource_Token_Collection getResourceCollection()
 * @method Magento_Oauth_Model_Resource_Token getResource()
 * @method Magento_Oauth_Model_Resource_Token _getResource()
 * @method int getConsumerId()
 * @method Magento_Oauth_Model_Token setConsumerId() setConsumerId(int $consumerId)
 * @method int getAdminId()
 * @method Magento_Oauth_Model_Token setAdminId() setAdminId(int $adminId)
 * @method int getCustomerId()
 * @method Magento_Oauth_Model_Token setCustomerId() setCustomerId(int $customerId)
 * @method string getType()
 * @method Magento_Oauth_Model_Token setType() setType(string $type)
 * @method string getVerifier()
 * @method Magento_Oauth_Model_Token setVerifier() setVerifier(string $verifier)
 * @method string getCallbackUrl()
 * @method Magento_Oauth_Model_Token setCallbackUrl() setCallbackUrl(string $callbackUrl)
 * @method string getCreatedAt()
 * @method Magento_Oauth_Model_Token setCreatedAt() setCreatedAt(string $createdAt)
 * @method string getToken()
 * @method Magento_Oauth_Model_Token setToken() setToken(string $token)
 * @method string getSecret()
 * @method Magento_Oauth_Model_Token setSecret() setSecret(string $tokenSecret)
 * @method int getRevoked()
 * @method Magento_Oauth_Model_Token setRevoked() setRevoked(int $revoked)
 * @method int getAuthorized()
 * @method Magento_Oauth_Model_Token setAuthorized() setAuthorized(int $authorized)
 */
class Magento_Oauth_Model_Token extends Magento_Core_Model_Abstract
{
    /**#@+
     * Token types
     */
    const TYPE_REQUEST = 'request';
    const TYPE_ACCESS  = 'access';
    /**#@- */

    /**#@+
     * Lengths of token fields
     */
    const LENGTH_TOKEN    = 32;
    const LENGTH_SECRET   = 32;
    const LENGTH_VERIFIER = 32;
    /**#@- */

    /**#@+
     * Customer types
     */
    const USER_TYPE_ADMIN    = 'admin';
    const USER_TYPE_CUSTOMER = 'customer';
    /**
     * Oauth data
     *
     * @var Magento_Oauth_Helper_Data
     */
    protected $_oauthData = null;

    /**
     * Url Validator
     *
     * @var Magento_Core_Model_Url_Validator
     */
    protected $_urlValidator = null;

    /**
     * Consumer Factory
     *
     * @var Magento_Oauth_Model_ConsumerFactory
     */
    protected $_consumerFactory = null;

    /**
     * KeyLength Factory
     *
     * @var Magento_Oauth_Model_Consumer_Validator_KeyLengthFactory
     */
    protected $_keyLengthFactory = null;

    /**
     * @param Magento_Oauth_Helper_Data $oauthData
     * @param Magento_Core_Model_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_Core_Model_Url_Validator $urlValidator
     * @param Magento_Oauth_Model_ConsumerFactory $consumerFactory
     * @param Magento_Oauth_Model_Consumer_Validator_KeyLengthFactory $keyLengthFactory
     * @param Magento_Core_Model_Resource_Abstract $resource
     * @param Magento_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Magento_Oauth_Helper_Data $oauthData,
        Magento_Core_Model_Context $context,
        Magento_Core_Model_Registry $registry,
        Magento_Core_Model_Url_Validator $urlValidator,
        Magento_Oauth_Model_ConsumerFactory $consumerFactory,
        Magento_Oauth_Model_Consumer_Validator_KeyLengthFactory $keyLengthFactory,
        Magento_Core_Model_Resource_Abstract $resource = null,
        Magento_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_oauthData = $oauthData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_urlValidator = $urlValidator;
        $this->_consumerFactory = $consumerFactory;
        $this->_keyLengthFactory = $keyLengthFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento_Oauth_Model_Resource_Token');
    }

    /**
     * "After save" actions
     *
     * @return Magento_Oauth_Model_Token
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        //Cleanup old entries
        if ($this->_oauthData->isCleanupProbability()) {
            $this->_getResource()->deleteOldEntries($this->_oauthData->getCleanupExpirationPeriod());
        }
        return $this;
    }

    /**
     * Authorize token
     *
     * @param int $userId Authorization user identifier
     * @param string $userType Authorization user type
     * @return Magento_Oauth_Model_Token
     * @throws Magento_Core_Exception
     */
    public function authorize($userId, $userType)
    {
        if (!$this->getId() || !$this->getConsumerId()) {
            throw new Magento_Core_Exception('Token is not ready to be authorized');
        }
        if ($this->getAuthorized()) {
            throw new Magento_Core_Exception('Token is already authorized');
        }
        if (self::USER_TYPE_ADMIN == $userType) {
            $this->setAdminId($userId);
        } elseif (self::USER_TYPE_CUSTOMER == $userType) {
            $this->setCustomerId($userId);
        } else {
            throw new Magento_Core_Exception('User type is unknown');
        }

        $this->setVerifier($this->_oauthData->generateVerifier());
        $this->setAuthorized(1);
        $this->save();

        $this->getResource()->cleanOldAuthorizedTokensExcept($this);

        return $this;
    }

    /**
     * Convert token to access type
     *
     * @return Magento_Oauth_Model_Token
     * @throws Magento_Core_Exception
     */
    public function convertToAccess()
    {
        if (Magento_Oauth_Model_Token::TYPE_REQUEST != $this->getType()) {
            throw new Magento_Core_Exception('Can not convert due to token is not request type');
        }

        $this->setType(self::TYPE_ACCESS);
        $this->setToken($this->_oauthData->generateToken());
        $this->setSecret($this->_oauthData->generateTokenSecret());
        $this->save();

        return $this;
    }

    /**
     * Generate and save request token
     *
     * @param int $consumerId Consumer identifier
     * @param string $callbackUrl Callback URL
     * @return Magento_Oauth_Model_Token
     */
    public function createRequestToken($consumerId, $callbackUrl)
    {
        $this->setData(array(
            'consumer_id'  => $consumerId,
            'type'         => self::TYPE_REQUEST,
            'token'        => $this->_oauthData->generateToken(),
            'secret'       => $this->_oauthData->generateTokenSecret(),
            'callback_url' => $callbackUrl
        ));
        $this->save();

        return $this;
    }

    /**
     * Get OAuth user type
     *
     * @return string
     * @throws Magento_Core_Exception
     */
    public function getUserType()
    {
        if ($this->getAdminId()) {
            return self::USER_TYPE_ADMIN;
        } elseif ($this->getCustomerId()) {
            return self::USER_TYPE_CUSTOMER;
        } else {
            throw new Magento_Core_Exception('User type is unknown');
        }
    }

    /**
     * Get string representation of token
     *
     * @param string $format
     * @return string
     */
    public function toString($format = '')
    {
        return http_build_query(array('oauth_token' => $this->getToken(), 'oauth_token_secret' => $this->getSecret()));
    }

    /**
     * Before save actions
     *
     * @return Magento_Oauth_Model_Consumer
     */
    protected function _beforeSave()
    {
        $this->validate();

        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt(Magento_Date::now());
        }
        parent::_beforeSave();
        return $this;
    }

    /**
     * Validate data
     *
     * @return array|bool
     * @throws Magento_Core_Exception
     */
    public function validate()
    {
        if (Magento_Oauth_Model_Server::CALLBACK_ESTABLISHED != $this->getCallbackUrl()
            && !$this->_urlValidator->isValid($this->getCallbackUrl())
        ) {
            $messages = $this->_urlValidator->getMessages();
            throw new Magento_Core_Exception(array_shift($messages));
        }

        $validatorLength = $this->_keyLengthFactory->create();
        $validatorLength->setLength(self::LENGTH_SECRET);
        $validatorLength->setName('Token Secret Key');
        if (!$validatorLength->isValid($this->getSecret())) {
            $messages = $validatorLength->getMessages();
            throw new Magento_Core_Exception(array_shift($messages));
        }

        $validatorLength->setLength(self::LENGTH_TOKEN);
        $validatorLength->setName('Token Key');
        if (!$validatorLength->isValid($this->getToken())) {
            $messages = $validatorLength->getMessages();
            throw new Magento_Core_Exception(array_shift($messages));
        }

        if (null !== ($verifier = $this->getVerifier())) {
            $validatorLength->setLength(self::LENGTH_VERIFIER);
            $validatorLength->setName('Verifier Key');
            if (!$validatorLength->isValid($verifier)) {
                $messages = $validatorLength->getMessages();
                throw new Magento_Core_Exception(array_shift($messages));
            }
        }
        return true;
    }

    /**
     * Get Token Consumer
     *
     * @return Magento_Oauth_Model_Consumer
     */
    public function getConsumer()
    {
        if (!$this->getData('consumer')) {
            $consumer = $this->_consumerFactory->create();
            $consumer->load($this->getConsumerId());
            $this->setData('consumer', $consumer);
        }

        return $this->getData('consumer');
    }
}
