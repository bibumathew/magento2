<?php
/**
 * Newsletter status options
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Magento_Newsletter_Block_Subscribe_Grid_Options_Status implements Magento_Core_Model_Option_ArrayInterface
{
    /**
     * @var Magento_Newsletter_Helper_Data
     */
    protected $_helper;

    /**
     * @param Magento_Newsletter_Helper_Data $newsletterHelper
     */
    public function __construct(Magento_Newsletter_Helper_Data $newsletterHelper)
    {
        $this->_helper = $newsletterHelper;
    }

    /**
     * Return status column options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            Magento_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => $this->_helper->__('Not Activated'),
            Magento_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => $this->_helper->__('Subscribed'),
            Magento_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => $this->_helper->__('Unsubscribed'),
            Magento_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED => $this->_helper->__('Unconfirmed'),
        );
    }
}
