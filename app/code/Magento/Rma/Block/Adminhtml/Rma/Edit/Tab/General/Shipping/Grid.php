<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Grid of packaging shipment
 *
 * @category    Magento
 * @package     Magento_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rma\Block\Adminhtml\Rma\Edit\Tab\General\Shipping;

class Grid extends \Magento\Backend\Block\Template
{
    /**
     * Rma data
     *
     * @var Magento_Rma_Helper_Data
     */
    protected $_rmaData = null;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Rma_Helper_Data $rmaData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Rma_Helper_Data $rmaData,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_rmaData = $rmaData;
        $this->_coreRegistry = $registry;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Return collection of shipment items
     *
     * @return array
     */
    public function getCollection()
    {
        return $this->_coreRegistry->registry('current_rma')->getShippingMethods(true);
    }

    /**
     * Can display customs value
     *
     * @return bool
     */
    public function displayCustomsValue()
    {
        $storeId = $this->_coreRegistry->registry('current_rma')->getStoreId();
        $order = $this->_coreRegistry->registry('current_rma')->getOrder();
        $address = $order->getShippingAddress();
        $shippingSourceCountryCode = $address->getCountryId();

        $shippingDestinationInfo = $this->_rmaData->getReturnAddressModel($storeId);
        $shippingDestinationCountryCode = $shippingDestinationInfo->getCountryId();

        if ($shippingSourceCountryCode != $shippingDestinationCountryCode) {
            return true;
        }
        return false;
    }

    /**
     * Format price
     *
     * @param   decimal $value
     * @return  double
     */
    public function formatPrice($value)
    {
        return sprintf('%.2F', $value);
    }
}
