<?php
/**
 * Represents an endpoint to which messages can be sent
 *
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   {copyright}
 * @license     {license_link}
 *
 * @method string getName()
 * @method Mage_Webhook_Model_Endpoint setName(string $value)
 * @method Mage_Webhook_Model_Endpoint setEndpointUrl(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Webhook_Model_Endpoint setUpdatedAt(string $value)
 * @method string getAlias()
 * @method Mage_Webhook_Model_Endpoint setAlias(string $value)
 * @method Mage_Webhook_Model_Endpoint setFormat(string $value)
 * @method string getApiUserId()
 * @method Mage_Webhook_Model_Endpoint setApiUserId(string $value)
 * @method Mage_Webhook_Model_Endpoint setAuthenticationType(string $value)
 * @method Mage_Webhook_Model_Endpoint setTimeoutInSecs(string $value)
 */
class Mage_Webhook_Model_Endpoint extends Mage_Core_Model_Abstract implements Magento_Outbound_EndpointInterface
{
    /**
     * Used to create a User abstraction from a given webapi user associated with this subscription.
     * @var Mage_Webhook_Model_User_Factory
     */
    private $_userFactory;

    /**
     * @param Mage_Webhook_Model_User_Factory $userFactory
     * @param Mage_Core_Model_Context $context
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Magento_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Mage_Webhook_Model_User_Factory $userFactory,
        Mage_Core_Model_Context $context,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Magento_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->_userFactory = $userFactory;
    }

    /**
     * Initialize model
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Resource_Endpoint');
    }

    /**
     * Return subscription endpoint url for compatibility with interface
     *
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->getData('endpoint_url');
    }

    /**
     * Return subscription timeout in secs for compatibility with interface
     *
     * @return string
     */
    public function getTimeoutInSecs()
    {
        return $this->getData('timeout_in_secs');
    }

    /**
     * Prepare data to be saved to database
     *
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Webhook_Exception
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->hasAuthenticationType()) {
            $this->setAuthenticationType(Magento_Outbound_EndpointInterface::AUTH_TYPE_NONE);
        }

        if ($this->hasDataChanges()) {
            $this->setUpdatedAt($this->_getResource()->formatDate(time()));
        }

        return $this;
    }

    /**
     * Returns the format this message should be sent in (JSON, XML, etc.)
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->getData('format');
    }

    /**
     * Returns the user abstraction associated with this subscription or null if no user has been associated yet.
     *
     * @return Magento_Outbound_UserInterface|null
     */
    public function getUser()
    {
        if ($this->getApiUserId() === null) {
            return null;
        }
        return $this->_userFactory->create($this->getApiUserId());
    }

    /**
     * Returns the type of authentication to use when attaching authentication to a message
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return $this->getData('authentication_type');
    }
}
