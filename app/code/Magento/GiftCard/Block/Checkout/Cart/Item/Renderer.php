<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GiftCard
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\GiftCard\Block\Checkout\Cart\Item;

class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * Gift card catalog product configuration
     *
     * @var Magento_GiftCard_Helper_Catalog_Product_Configuration
     */
    protected $_giftCardCtlgProdConfigur = null;

    /**
     * @param Magento_GiftCard_Helper_Catalog_Product_Configuration $giftCardCtlgProdConfigur
     * @param Magento_Catalog_Helper_Product_Configuration $ctlgProdConfigur
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Magento_GiftCard_Helper_Catalog_Product_Configuration $giftCardCtlgProdConfigur,
        Magento_Catalog_Helper_Product_Configuration $ctlgProdConfigur,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_giftCardCtlgProdConfigur = $giftCardCtlgProdConfigur;
        parent::__construct($ctlgProdConfigur, $coreData, $context, $data);
    }

    /**
     * Prepare custom option for display, returns false if there's no value
     *
     * @param string $code
     * @return mixed
     */
    protected function _prepareCustomOption($code)
    {
        return $this->_giftCardCtlgProdConfigur
            ->prepareCustomOption($this->getItem(), $code);
    }

    /**
     * Get gift card option list
     *
     * @return array
     */
    protected function _getGiftcardOptions()
    {
        return $this->_giftCardCtlgProdConfigur
            ->getGiftcardOptions($this->getItem());
    }

    /**
     * Return gift card and custom options array
     *
     * @return array
     */
    public function getOptionList()
    {
        return $this->_giftCardCtlgProdConfigur
            ->getOptions($this->getItem());
    }
}
