<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer balance block for order
 *
 */
class Enterprise_GiftWrapping_Block_Sales_Totals extends Magento_Core_Block_Template
{
    /**
     * Initialize gift wrapping and printed card totals for order/invoice/creditmemo
     *
     * @return Enterprise_GiftWrapping_Block_Sales_Totals
     */
    /**
     * Gift wrapping data
     *
     * @var Enterprise_GiftWrapping_Helper_Data
     */
    protected $_giftWrappingData = null;

    /**
     * @param Enterprise_GiftWrapping_Helper_Data $giftWrappingData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Enterprise_GiftWrapping_Helper_Data $giftWrappingData,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_giftWrappingData = $giftWrappingData;
        parent::__construct($coreData, $context, $data);
    }

    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source  = $parent->getSource();
        $totals = $this->_giftWrappingData->getTotals($source);
        foreach ($totals as $total) {
            $this->getParentBlock()->addTotalBefore(new Magento_Object($total), 'tax');
        }
        return $this;
    }
}
