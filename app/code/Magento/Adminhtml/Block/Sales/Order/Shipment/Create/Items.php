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
 * Adminhtml shipment items grid
 */

namespace Magento\Adminhtml\Block\Sales\Order\Shipment\Create;

class Items extends \Magento\Adminhtml\Block\Sales\Items\AbstractItems
{
    /**
     * Sales data
     *
     * @var Magento_Sales_Helper_Data
     */
    protected $_salesData = null;

    /**
     * @param Magento_Sales_Helper_Data $salesData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Sales_Helper_Data $salesData,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_salesData = $salesData;
        parent::__construct($coreData, $context, $registry, $data);
    }

    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Prepare child blocks
     */
    protected function _beforeToHtml()
    {
        $this->addChild('submit_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('Submit Shipment'),
            'class'     => 'save submit-button',
            'onclick'   => 'submitShipment(this);',
        ));

        return parent::_beforeToHtml();
    }

    /**
     * Format given price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getShipment()->getOrder()->formatPrice($price);
    }

    /**
     * Retrieve HTML of update button
     *
     * @return string
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    /**
     * Get url for update
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/updateQty', array('order_id'=>$this->getShipment()->getOrderId()));
    }

    /**
     * Check possibility to send shipment email
     *
     * @return bool
     */
    public function canSendShipmentEmail()
    {
        return $this->_salesData->canSendNewShipmentEmail($this->getOrder()->getStore()->getId());
    }

    /**
     * Checks the possibility of creating shipping label by current carrier
     *
     * @return bool
     */
    public function canCreateShippingLabel()
    {
        $shippingCarrier = $this->getOrder()->getShippingCarrier();
        return $shippingCarrier && $shippingCarrier->isShippingLabelsAvailable();
    }
}
