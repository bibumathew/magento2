<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Usa
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Source model for DHL shipping methods for documentation
 *
 * @category   Mage
 * @package    Mage_Usa
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Usa_Model_Shipping_Carrier_Dhl_International_Source_Method_Freedoc
    extends Mage_Usa_Model_Shipping_Carrier_Dhl_International_Source_Method_Abstract
{
    /**
     * Carrier Product Type Indicator
     *
     * @var string $_contentType
     */
    protected $_contentType = Mage_Usa_Model_Shipping_Carrier_Dhl_International::DHL_CONTENT_TYPE_DOC;

    /**
     * Show 'none' in methods list or not;
     *
     * @var bool
     */
    protected $_noneMethod = true;
}