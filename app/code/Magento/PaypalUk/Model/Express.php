<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_PaypalUk
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * PayPalUk Express Module
 */
namespace Magento\PaypalUk\Model;

class Express extends \Magento\Paypal\Model\Express
{
    protected $_code = \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS;
    protected $_formBlockType = 'Magento\PaypalUk\Block\Express\Form';
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'Magento\PaypalUk\Model\Pro';

    /**
     * Express Checkout payment method instance
     *
     * @var \Magento\Paypal\Model\Express
     */
    protected $_ecInstance = null;

    /**
     * @var \Magento\Paypal\Model\InfoFactory
     */
    protected $_paypalInfoFactory;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param \Magento\Paypal\Model\InfoFactory $paypalInfoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\UrlInterface $urlBuilder,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Paypal\Model\InfoFactory $paypalInfoFactory,
        array $data = array()
    ) {
        parent::__construct(
            $eventManager,
            $paymentData,
            $coreStoreConfig,
            $logAdapterFactory,
            $proTypeFactory,
            $storeManager,
            $urlBuilder,
            $cartFactory,
            $data
        );
        $this->_paypalInfoFactory = $paypalInfoFactory;
    }

    /**
     * EC PE won't be available if the EC is available
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (!parent::isAvailable($quote)) {
            return false;
        }
        if (!$this->_ecInstance) {
            $this->_ecInstance = $this->_paymentData
                ->getMethodInstance(\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS);
        }
        if ($quote && $this->_ecInstance) {
            $this->_ecInstance->setStore($quote->getStoreId());
        }
        return $this->_ecInstance ? !$this->_ecInstance->isAvailable() : false;
    }

    /**
     * Import payment info to payment
     *
     * @param \Magento\Paypal\Model\Api\Nvp
     * @param \Magento\Sales\Model\Order\Payment
     */
    protected function _importToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getPaypalTransactionId())->setIsTransactionClosed(0)
            ->setAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_REDIRECT,
                $api->getRedirectRequired() || $api->getRedirectRequested()
            )
            ->setIsTransactionPending($api->getIsPaymentPending())
            ->setTransactionAdditionalInfo(\Magento\PaypalUk\Model\Pro::TRANSPORT_PAYFLOW_TXN_ID,
                $api->getTransactionId())
        ;
        $payment->setPreparedMessage(__('Payflow PNREF: #%1.', $api->getTransactionId()));
        $this->_paypalInfoFactory->create()->importToPayment($api, $payment);
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see \Magento\Sales\Model\Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypaluk/express/start');
    }
}
