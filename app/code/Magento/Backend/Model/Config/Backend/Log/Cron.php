<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Log Cron Backend Model
 */
namespace Magento\Backend\Model\Config\Backend\Log;

class Cron extends \Magento\Core\Model\Config\Value
{
    const CRON_STRING_PATH  = 'crontab/jobs/log_clean/schedule/cron_expr';
    const CRON_MODEL_PATH   = 'crontab/jobs/log_clean/run/model';

    /**
     * @var \Magento\Core\Model\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Config\ValueFactory $configValueFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Config\ValueFactory $configValueFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        $runModelPath = '',
        array $data = array()
    ) {
        $this->_configValueFactory = $configValueFactory;
        $this->_runModelPath = $runModelPath;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Cron settings after save
     *
     * @return \Magento\Backend\Model\Config\Backend\Log\Cron
     * @throws \Magento\Core\Exception
     */
    protected function _afterSave()
    {
        $enabled    = $this->getData('groups/log/fields/enabled/value');
        $time       = $this->getData('groups/log/fields/time/value');
        $frequency   = $this->getData('groups/log/fields/frequency/value');

        $frequencyWeekly    = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
        $frequencyMonthly   = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;

        if ($enabled) {
            $cronExprArray = array(
                intval($time[1]),                                   # Minute
                intval($time[0]),                                   # Hour
                ($frequency == $frequencyMonthly) ? '1' : '*',       # Day of the Month
                '*',                                                # Month of the Year
                ($frequency == $frequencyWeekly) ? '1' : '*',        # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            /** @var $configValue \Magento\Core\Model\Config\Value */
            $configValue = $this->_configValueFactory->create();
            $configValue->load(self::CRON_STRING_PATH, 'path');
            $configValue->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();

            /** @var $configValue \Magento\Core\Model\Config\Value */
            $configValue = $this->_configValueFactory->create();
            $configValue->load(self::CRON_MODEL_PATH, 'path');
            $configValue->setValue($this->_runModelPath)
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (\Exception $e) {
            throw new \Magento\Core\Exception(__('We can\'t save the Cron expression.'));
        }
    }
}
