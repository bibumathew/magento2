<?php
/**
 * Plugin for \Magento\Log\Model\Resource\Log model
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Reports\Model\Plugin;

class Log
{
    /**
     * @var \Magento\Reports\Model\Event
     */
    protected $_reportEvent;

    /**
     * @var \Magento\Reports\Model\Product\Index\Compared
     */
    protected $_comparedProductIdx;

    /**
     * @var \Magento\Reports\Model\Product\Index\Viewed
     */
    protected $_viewedProductIdx;

    /**
     * @param \Magento\Reports\Model\Event $reportEvent
     * @param \Magento\Reports\Model\Product\Index\Compared $comparedProductIdx
     * @param \Magento\Reports\Model\Product\Index\Viewed $viewedProductIdx
     */
    public function __construct(
        \Magento\Reports\Model\Event $reportEvent,
        \Magento\Reports\Model\Product\Index\Compared $comparedProductIdx,
        \Magento\Reports\Model\Product\Index\Viewed $viewedProductIdx
    ) {
        $this->_reportEvent = $reportEvent;
        $this->_comparedProductIdx = $comparedProductIdx;
        $this->_viewedProductIdx = $viewedProductIdx;
    }

    /**
     * Clean events by old visitors
     * after plugin for clean method
     *
     * @see Global Log Clean Settings
     *
     * @param \Magento\Log\Model\Resource\Log $logResourceModel
     * @return \Magento\Log\Model\Resource\Log
     */
    public function afterClean($logResourceModel)
    {
        $this->_reportEvent->clean();
        $this->_comparedProductIdx->clean();
        $this->_viewedProductIdx->clean();
        return $logResourceModel;
    }
}
