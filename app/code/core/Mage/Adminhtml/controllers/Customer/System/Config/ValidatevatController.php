<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * VAT validation controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Customer_System_Config_ValidatevatController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Perform customer VAT ID validation
     *
     * @return Varien_Object
     */
    protected function _validate()
    {
        return Mage::helper('Mage_Customer_Helper_Data')->checkVatNumber(
            $this->getRequest()->getParam('country'),
            $this->getRequest()->getParam('vat')
        );
    }

    /**
     * Check whether vat is valid
     *
     * @return void
     */
    public function validateAction()
    {
        $result = $this->_validate();
        $this->getResponse()->setBody((int)$result->getIsValid());
    }

    /**
     * Retrieve validation result as JSON
     *
     * @return void
     */
    public function validateAdvancedAction()
    {
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('Mage_Core_Helper_Data');

        $result = $this->_validate();
        $valid = $result->getIsValid();
        $success = $result->getRequestSuccess();
        // ID of the store where order is placed
        $storeId = $this->getRequest()->getParam('store_id');
        // Sanitize value if needed
        if (!is_null($storeId)) {
            $storeId = (int)$storeId;
        }

        $groupId = Mage::helper('Mage_Customer_Helper_Data')->getCustomerGroupIdBasedOnVatNumber(
            $this->getRequest()->getParam('country'), $result, $storeId
        );

        $body = $coreHelper->jsonEncode(array(
            'valid' => $valid,
            'group' => $groupId,
            'success' => $success
        ));
        $this->getResponse()->setBody($body);
    }
}
