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
 * Subtotal Total Row Renderer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Sales\Block\Adminhtml\Order\Create\Totals;

class Shipping
    extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals
{
    protected $_template = 'sales/order/create/totals/shipping.phtml';

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Adminhtml\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Adminhtml\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\Config $salesConfig,
        array $data = array()
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($salesData, $sessionQuote, $orderCreate, $coreData, $context, $salesConfig, $data);
    }


    /**
     * Check if we need display shipping include and exclude tax
     *
     * @return bool
     */
    public function displayBoth()
    {
        return $this->_taxConfig->displayCartShippingBoth();
    }

    /**
     * Check if we need display shipping include tax
     *
     * @return bool
     */
    public function displayIncludeTax()
    {
        return $this->_taxConfig->displayCartShippingInclTax();
    }

    /**
     * Get shipping amount include tax
     *
     * @return float
     */
    public function getShippingIncludeTax()
    {
        return $this->getTotal()->getAddress()->getShippingAmount() +
            $this->getTotal()->getAddress()->getShippingTaxAmount();
    }

    /**
     * Get shipping amount exclude tax
     *
     * @return float
     */
    public function getShippingExcludeTax()
    {
        return $this->getTotal()->getAddress()->getShippingAmount();
    }

    /**
     * Get label for shipping include tax
     *
     * @return float
     */
    public function getIncludeTaxLabel()
    {
        return __('Shipping Incl. Tax (%1)', $this->escapeHtml($this->getTotal()->getAddress()->getShippingDescription()));
    }

    /**
     * Get label for shipping exclude tax
     *
     * @return float
     */
    public function getExcludeTaxLabel()
    {
        return __('Shipping Excl. Tax (%1)', $this->escapeHtml($this->getTotal()->getAddress()->getShippingDescription()));
    }
}
