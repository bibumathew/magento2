<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Configuration class for totals
 */
namespace Magento\Sales\Model\Order\Total\Config;

class Base extends \Magento\Sales\Model\Config\Ordered
{
    /**
     * Cache key for collectors
     *
     * @var string
     */
    protected $_collectorsCacheKey = 'sorted_collectors';

    /**
     * Total models list
     *
     * @var array
     */
    protected $_totalModels = array();

    /**
     * Configuration path where to collect registered totals
     *
     * @var string
     */
    protected $_configGroup = 'totals';

    /**
     * @var \Magento\Sales\Model\Order\TotalFactory
     */
    protected $_orderTotalFactory;

    /**
     * @param \Magento\Core\Model\Cache\Type\Config $configCacheType
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Sales\Model\Order\TotalFactory $orderTotalFactory
     * @param \Magento\Sales\Model\Config $salesConfig,
     * @param null $sourceData
     */
    public function __construct(
        \Magento\Core\Model\Cache\Type\Config $configCacheType,
        \Magento\Core\Model\Logger $logger,
        \Magento\Sales\Model\Order\TotalFactory $orderTotalFactory,
        \Magento\Sales\Model\Config $salesConfig,
        $sourceData = null
    ) {
        parent::__construct($configCacheType, $logger, $salesConfig, $sourceData);
        $this->_orderTotalFactory = $orderTotalFactory;
    }

    /**
     * Init model class by configuration
     *
     * @param string $class
     * @param string $totalCode
     * @param array $totalConfig
     * @return \Magento\Sales\Model\Order\Total\AbstractTotal
     * @throws \Magento\Core\Exception
     */
    protected function _initModelInstance($class, $totalCode, $totalConfig)
    {
        $model = $this->_orderTotalFactory->create($class);
        if (!$model instanceof \Magento\Sales\Model\Order\Total\AbstractTotal) {
            throw new \Magento\Core\Exception(
                __('The total model should be extended from \Magento\Sales\Model\Order\Total\AbstractTotal.')
            );
        }

        $model->setCode($totalCode);
        $model->setTotalConfigNode($totalConfig);
        $this->_modelsConfig[$totalCode] = $this->_prepareConfigArray($totalCode, $totalConfig);
        $this->_modelsConfig[$totalCode] = $model->processConfigArray($this->_modelsConfig[$totalCode]);
        return $model;
    }

    /**
     * Retrieve total calculation models
     *
     * @return array
     */
    public function getTotalModels()
    {
        if (empty($this->_totalModels)) {
            $this->_initModels();
            $this->_initCollectors();
            $this->_totalModels = $this->_collectors;
        }
        return $this->_totalModels;
    }
}
