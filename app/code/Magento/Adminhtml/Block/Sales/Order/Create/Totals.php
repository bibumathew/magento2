<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml sales order create totals block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Magento_Adminhtml_Block_Sales_Order_Create_Totals extends Magento_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_totalRenderers;
    protected $_defaultRenderer = 'Magento_Adminhtml_Block_Sales_Order_Create_Totals_Default';

    /**
     * Sales data
     *
     * @var Magento_Sales_Helper_Data
     */
    protected $_salesData = null;

    /**
     * @var Magento_Sales_Model_Config
     */
    protected $_salesConfig;

    /**
     * @param Magento_Sales_Helper_Data $salesData
     * @param Magento_Adminhtml_Model_Session_Quote $sessionQuote
     * @param Magento_Adminhtml_Model_Sales_Order_Create $orderCreate
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Sales_Model_Config $salesConfig
     * @param array $data
     */
    public function __construct(
        Magento_Sales_Helper_Data $salesData,
        Magento_Adminhtml_Model_Session_Quote $sessionQuote,
        Magento_Adminhtml_Model_Sales_Order_Create $orderCreate,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Sales_Model_Config $salesConfig,
        array $data = array()
    ) {
        $this->_salesData = $salesData;
        $this->_salesConfig = $salesConfig;
        parent::__construct($sessionQuote, $orderCreate, $coreData, $context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_totals');
    }

    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }

    public function getHeaderText()
    {
        return __('Order Totals');
    }

    public function getHeaderCssClass()
    {
        return 'head-money';
    }

    protected function _getTotalRenderer($code)
    {
        $blockName = $code.'_total_renderer';
        $block = $this->getLayout()->getBlock($blockName);
        if (!$block) {
            $configRenderer = $this->_salesConfig->getTotalsRenderer('quote', 'totals', $code, 'adminhtml');
            if (empty($configRenderer)) {
                $block = $this->_defaultRenderer;
            } else {
                $block = $configRenderer;
            }

            $block = $this->getLayout()->createBlock($block, $blockName);
        }
        /**
         * Transfer totals to renderer
         */
        $block->setTotals($this->getTotals());
        return $block;
    }

    public function renderTotal($total, $area = null, $colspan = 1)
    {
        return $this->_getTotalRenderer($total->getCode())
            ->setTotal($total)
            ->setColspan($colspan)
            ->setRenderingArea(is_null($area) ? -1 : $area)
            ->toHtml();
    }

    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        foreach ($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }

    public function canSendNewOrderConfirmationEmail()
    {
        return $this->_salesData->canSendNewOrderConfirmationEmail($this->getQuote()->getStoreId());
    }
}
