<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Renderer for service JavaScript code that disables corresponding paypal methods on page load
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Block_Adminhtml_System_Config_Fieldset_Store
    extends Mage_Backend_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Mage_Paypal::system/config/fieldset/store.phtml';

    /**
     * Render service JavaScript code
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = Mage::getModel('Mage_Core_Model_StoreManager')->isSingleStoreMode() ? '' : $this->toHtml();
        return $html;
    }

    /**
     * Returns list of disabled (in the Default or the Website Scope) paypal methods
     *
     * @return array
     */
    protected function getPaypalDisabledMethods()
    {
        // Assoc array that contains info about paypal methods (their IDs and corresponding Config Paths)
        $methods = array(
            'express'   => 'payment/paypal_express/active',
            'wps'       => 'payment/paypal_standard/active',
            'wpp'       => 'payment/paypal_direct/active',
            'wpppe'     => 'payment/paypaluk_direct/active',
            'verisign'  => 'payment/verisign/active',
            'expresspe' => 'payment/paypaluk_express/active'
        );
        // Retrieve a code of the current website
        $website = $this->getRequest()->getParam('website');

        $configRoot = Mage::getConfig()->getNode(null, 'website', $website);

        $disabledMethods = array();
        foreach ($methods as $methodId => $methodPath) {
            $isEnabled = (int) $configRoot->descend($methodPath);
            if ($isEnabled === 0) {
                $disabledMethods[$methodId] = $isEnabled;
            }
        }

        return $disabledMethods;
    }
}