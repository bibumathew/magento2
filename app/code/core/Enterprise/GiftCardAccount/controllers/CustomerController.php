<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCardAccount
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_GiftCardAccount_CustomerController extends Mage_Core_Controller_Front_Action
{
    /**
     * Only logged in users can use this functionality,
     * this function checks if user is logged in before all other actions
     *
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('Mage_Customer_Model_Session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Redeem gift card
     *
     */
    public function indexAction()
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                if (!Mage::helper('Enterprise_CustomerBalance_Helper_Data')->isEnabled()) {
                    Mage::throwException($this->__('Redemption functionality is disabled.'));
                }
                Mage::getModel('Enterprise_GiftCardAccount_Model_Giftcardaccount')->loadByCode($code)
                    ->setIsRedeemed(true)->redeem();
                Mage::getSingleton('Mage_Customer_Model_Session')->addSuccess(
                    $this->__('Gift Card "%s" was redeemed.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($code))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('Mage_Customer_Model_Session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Customer_Model_Session')->addException($e, $this->__('Cannot redeem Gift Card.'));
            }
            $this->_redirect('*/*/*');
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->loadLayoutUpdates();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('Gift Card'));
        }
        $this->renderLayout();
    }
}