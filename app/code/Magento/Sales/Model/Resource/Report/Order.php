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
 * Order entity resource model
 */
namespace Magento\Sales\Model\Resource\Report;

class Order extends \Magento\Sales\Model\Resource\Report\AbstractReport
{
    /**
     * @var \Magento\Sales\Model\Resource\Report\Order\CreatedatFactory
     */
    protected $_createDatFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Report\Order\UpdatedatFactory
     */
    protected $_updateDatFactory;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\App\Resource $resource
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Reports\Model\FlagFactory $reportsFlagFactory
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param Order\CreatedatFactory $createDatFactory
     * @param Order\UpdatedatFactory $updateDatFactory
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\App\Resource $resource,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Sales\Model\Resource\Report\Order\CreatedatFactory $createDatFactory,
        \Magento\Sales\Model\Resource\Report\Order\UpdatedatFactory $updateDatFactory
    ) {
        parent::__construct($logger, $resource, $locale, $reportsFlagFactory, $dateTime);
        $this->_createDatFactory = $createDatFactory;
        $this->_updateDatFactory = $updateDatFactory;
    }

    /**
     * Model initialization
     */
    protected function _construct()
    {
        $this->_init('sales_order_aggregated_created', 'id');
    }

    /**
     * Aggregate Orders data
     *
     * @param mixed $from
     * @param mixed $to
     * @return \Magento\Sales\Model\Resource\Report\Order
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_createDatFactory->create()->aggregate($from, $to);
        $this->_updateDatFactory->create()->aggregate($from, $to);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_ORDER_FLAG_CODE);
        return $this;
    }
}
