<?php
/**
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */

namespace Magento\Integration\Model;

/**
 * Integration model.
 *
 * @method \string getName()
 * @method Integration setName(\string $name)
 * @method \string getEmail()
 * @method Integration setEmail(\string $email)
 * @method \int getStatus()
 * @method Integration setStatus(\int $value)
 * @method \int getType()
 * @method Integration setType(\int $value)
 * @method \string getEndpoint()
 * @method Integration setEndpoint(\string $endpoint)
 * @method \string getCreatedAt()
 * @method Integration setCreatedAt(\string $createdAt)
 * @method \string getUpdatedAt()
 * @method Integration setUpdatedAt(\string $createdAt)
 */
class Integration extends \Magento\Core\Model\AbstractModel
{
    /**#@+
     * Integration data value constants.
     */
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const TYPE_MANUAL = 0;
    const TYPE_CONFIG = 1;

    /**#@+
     * Integration data key constants.
     */
    const NAME = 'name';
    const EMAIL = 'email';
    const ENDPOINT = 'endpoint_url';
    const TYPE = 'type';
    /**#@-*/

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Integration\Model\Resource\Integration');
    }

    /**
     * Prepare data to be saved to database
     *
     * @return \Magento\Integration\Model\Integration
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($this->_dateTime->formatDate(true));
        }
        $this->setUpdatedAt($this->_dateTime->formatDate(true));
        return $this;
    }
}
