<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Log
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Prepare Log Online Visitors Model
 *
 * @method \Magento\Log\Model\Resource\Visitor\Online getResource()
 * @method string getVisitorType()
 * @method \Magento\Log\Model\Visitor\Online setVisitorType(string $value)
 * @method int getRemoteAddr()
 * @method \Magento\Log\Model\Visitor\Online setRemoteAddr(int $value)
 * @method string getFirstVisitAt()
 * @method \Magento\Log\Model\Visitor\Online setFirstVisitAt(string $value)
 * @method string getLastVisitAt()
 * @method \Magento\Log\Model\Visitor\Online setLastVisitAt(string $value)
 * @method int getCustomerId()
 * @method \Magento\Log\Model\Visitor\Online setCustomerId(int $value)
 * @method string getLastUrl()
 * @method \Magento\Log\Model\Visitor\Online setLastUrl(string $value)
 *
 * @category    Magento
 * @package     Magento_Log
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model\Visitor;

class Online extends \Magento\Core\Model\AbstractModel
{
    const XML_PATH_ONLINE_INTERVAL      = 'customer/online_customers/online_minutes_interval';
    const XML_PATH_UPDATE_FREQUENCY     = 'log/visitor/online_update_frequency';

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Visitor\Online');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Log\Model\Resource\Visitor\Online
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Prepare Online visitors collection
     *
     * @return \Magento\Log\Model\Visitor\Online
     */
    public function prepare()
    {
        $this->_getResource()->prepare($this);
        return $this;
    }

    /**
     * Retrieve last prepare at timestamp
     *
     * @return int
     */
    public function getPrepareAt()
    {
        return $this->_cacheManager->load('log_visitor_online_prepare_at');
    }

    /**
     * Set Prepare at timestamp (if time is null, set current timestamp)
     *
     * @param int $time
     * @return \Magento\Log\Model\Resource\Visitor\Online
     */
    public function setPrepareAt($time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        $this->_cacheManager->save($time, 'log_visitor_online_prepare_at');
        return $this;
    }

    /**
     * Retrieve data update Frequency in second
     *
     * @return int
     */
    public function getUpdateFrequency()
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_UPDATE_FREQUENCY);
    }

    /**
     * Retrieve Online Interval (in minutes)
     *
     * @return int
     */
    public function getOnlineInterval()
    {
        $value = intval($this->_coreStoreConfig->getConfig(self::XML_PATH_ONLINE_INTERVAL));
        if (!$value) {
            $value = \Magento\Log\Model\Visitor::DEFAULT_ONLINE_MINUTES_INTERVAL;
        }
        return $value;
    }
}
