<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_Rma_Block_Return_Tracking extends Mage_Core_Block_Template
{
    /**
     * Get whether rma is allowed for PSL
     *
     * @var bool|null
     */
    protected $_isRmaAvailableForPrintLabel = null;

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('return/tracking.phtml');
        $this->setRma(Mage::registry('current_rma'));
    }

    /**
     * Get collection of tracking numbers of RMA
     *
     * @return Enterprise_Rma_Model_Resource_Shipping_Collection
     */
    public function getTrackingNumbers()
    {
        return $this->getRma()->getTrackingNumbers();
    }

    /**
     * Get url for delete label action
     *
     * @return string
     */
    public function getDeleteLabelUrl()
    {
        return $this->getUrl('*/*/delLabel/', array('entity_id' => $this->getRma()->getEntityId()));
    }

    /**
     * Get messages on AJAX errors
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $message = Mage::getSingleton('Mage_Core_Model_Session')->getErrorMessage();
        Mage::getSingleton('Mage_Core_Model_Session')->unsErrorMessage();
        return $message;
    }

    /**
     * Get whether rma is allowed for PSL
     *
     * @return bool
     */
    public function isPrintShippingLabelAllowed()
    {
        if (is_null($this->_isRmaAvailableForPrintLabel)) {
            $this->_isRmaAvailableForPrintLabel = $this->getRma()->isAvailableForPrintLabel();
        }
        return $this->_isRmaAvailableForPrintLabel;
    }
}
