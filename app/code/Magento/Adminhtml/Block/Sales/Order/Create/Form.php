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
 * Adminhtml sales order create sidebar
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Magento_Adminhtml_Block_Sales_Order_Create_Form extends Magento_Adminhtml_Block_Sales_Order_Create_Abstract
{
    public function __construct(Magento_Backend_Block_Template_Context $context, array $data = array())
    {
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_form');
    }

    /**
     * Retrieve url for loading blocks
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('*/*/loadBlock');
    }

    /**
     * Retrieve url for form submiting
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    public function getCustomerSelectorDisplay()
    {
        $customerId = $this->getCustomerId();
        if (is_null($customerId)) {
            return 'block';
        }
        return 'none';
    }

    public function getStoreSelectorDisplay()
    {
        $storeId    = $this->getStoreId();
        $customerId = $this->getCustomerId();
        if (!is_null($customerId) && !$storeId) {
            return 'block';
        }
        return 'none';
    }

    public function getDataSelectorDisplay()
    {
        $storeId    = $this->getStoreId();
        $customerId = $this->getCustomerId();
        if (!is_null($customerId) && $storeId) {
            return 'block';
        }
        return 'none';
    }

    public function getOrderDataJson()
    {
        $data = array();
        if (!is_null($this->getCustomerId())) {
            $data['customer_id'] = $this->getCustomerId();
            $data['addresses'] = array();

            /* @var $addressForm Magento_Customer_Model_Form */
            $addressForm = Mage::getModel('Magento_Customer_Model_Form')
                ->setFormCode('adminhtml_customer_address')
                ->setStore($this->getStore());
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $data['addresses'][$address->getId()] = $addressForm->setEntity($address)
                    ->outputData(Magento_Customer_Model_Attribute_Data::OUTPUT_FORMAT_JSON);
            }
        }
        if (!is_null($this->getStoreId())) {
            $data['store_id'] = $this->getStoreId();
            $currency = Mage::app()->getLocale()->currency($this->getStore()->getCurrentCurrencyCode());
            $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
            $data['currency_symbol'] = $symbol;
            $data['shipping_method_reseted'] = !(bool)$this->getQuote()->getShippingAddress()->getShippingMethod();
            $data['payment_method'] = $this->getQuote()->getPayment()->getMethod();
        }
        return $this->_coreData->jsonEncode($data);
    }
}
