<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCard
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_GiftCard_Block_Sales_Order_Item_Renderer_Noquote extends Enterprise_GiftCard_Block_Sales_Order_Item_Renderer
{
    /**
     * Prepare custom option for display, returns false if there's no value
     *
     * @param string $code
     * @return mixed
     */
    protected function _prepareCustomOption($code)
    {
        if ($option = $this->getOrderItem()->getProductOptionByCode($code)) {
            return $option;
        }
        return false;
    }

    /**
     * Prepare a string containing name and email
     *
     * @param string $name
     * @param string $email
     * @return mixed
     */
    protected function _getNameEmailString($name, $email)
    {
        return "$name <{$email}>";
    }
}
