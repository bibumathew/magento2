<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Shipping Method Block at RMA page
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Shippingmethod
    extends Enterprise_Rma_Block_Adminhtml_Rma_Edit_Tab_General_Abstract
{

    /**
     * Variable to store RMA instance
     *
     * @var null|Enterprise_Rma_Model_Rma
     */
    protected $_rma = null;

    public function _construct()
    {
        $this->setIsPsl((bool)(
            $this->_getShippingAvailability()
            && $this->getRma()->isAvailableForPrintLabel()));
    }

    /**
     * Declare rma instance
     *
     * @return  Enterprise_Rma_Model_Item
     */
    public function getRma()
    {
        if (is_null($this->_rma)) {
            $this->_rma = Mage::registry('current_rma');
        }
        return $this->_rma;
    }

    /**
     * Defines whether Shipping method settings allow to create shipping label
     *
     * @return bool
     */
    protected function _getShippingAvailability()
    {
        $carriers = Mage::helper('enterprise_rma')->getAllowedShippingCarriers($this->getRma()->getStoreId());
        return !empty($carriers);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return Enterprise_Rma_Model_Shipping
     */
    public function getShipment()
    {
        return Mage::getModel('enterprise_rma/shipping')
            ->getShippingLabelByRma($this->getRma());
    }

    /**
     * Get packed products in packages
     *
     * @return array
     */
    public function getPackages()
    {
        $packages = $this->getShipment()->getPackages();
        if ($packages) {
            $packages = unserialize($packages);
        } else {
            $packages = array();
        }
        return $packages;
    }

    /**
     * Can display customs value
     *
     * @return bool
     */
    public function displayCustomsValue()
    {
        return false;
        $storeId = $this->getShipment()->getStoreId();
        $order = $this->getShipment()->getOrder();
        $carrierCode = $order->getShippingCarrier()->getCarrierCode();
        $address = $order->getShippingAddress();
        $shipperAddressCountryCode = Mage::getStoreConfig('shipping/origin/country_id', $storeId);
        $recipientAddressCountryCode = $address->getCountryId();

        if (($carrierCode == 'fedex' || $carrierCode == 'dhl')
            && $shipperAddressCountryCode != $recipientAddressCountryCode) {
            return true;
        }
        return false;
    }

    /**
     * Get print label button html
     *
     * @return string
     */
    public function getPrintLabelButton()
    {
        $data['id'] = $this->getRma()->getId();
        $url        = $this->getUrl('*/rma/printLabel', $data);

        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('enterprise_rma')->__('Print Shipping Label'),
                'onclick' => 'setLocation(\'' . $url . '\')'
            ))
            ->toHtml();
    }

    /**
     * Show packages button html
     *
     * @return string
     */
    public function getShowPackagesButton()
    {
        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('enterprise_rma')->__('Show Packages'),
                'onclick' => 'showPackedWindow();'
            ))
            ->toHtml();
    }

    /**
     * Print button for creating pdf
     *
     * @return string
     */
    public function getPrintButton()
    {
        $data['id'] = $this->getRma()->getId();
        $url        = $this->getUrl('*/rma/printPackage', $data);

        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('enterprise_rma')->__('Print'),
                'onclick' => 'setLocation(\'' . $url . '\')'
            ))
            ->toHtml();
    }
}