<?php
/**
 * Customer create wizard
 *
 * @package     Mage
 * @subpackage  Admin
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Admin_Block_Customer_Wizard extends Varien_Object
{
    /**
     * Request object
     *
     * @var Mage_Core_Controller_Zend_Request
     */
    protected $_request;
    
    public function __construct() 
    {
        $this->_request = Mage::registry('action')->getRequest();
    }
    
    /**
     * Render wizard step content
     *
     * @return string
     */
    public function getStepContent()
    {
        $step = $this->_request->getParam('step', 1);
        
        $cardStruct = array();
        $cardStruct['error'] = 0;
        switch ($step) {
            // Account form
            case 1:
                $customer = Mage::getModel('customer/customer');
                $form = new Mage_Admin_Block_Customer_Form($customer);
                
                $cardStruct['tabs'][] = array(
                    'name'  => 'general',
                    'title' => __('Account Information'),
                    'type'  => 'form',
                    'form'  => $form->toArray()
                );
                $cardStruct['nextPoint']['url'] = Mage::getUrl('admin', array('controller'=>'customer', 'action'=>'wizard', 'step'=>2));
                break;
            // Address form
            case 2:
                if (!$this->_receiveAccountData()) {
                    $cardStruct['error'] = 1;
                    $cardStruct['errorMessage'] = 'Account validation error';
                }
                else {
                    $address = Mage::getModel('customer/address');
                    $form = new Mage_Admin_Block_Customer_Address_Form($address);
                    $form->addFieldNamePrefix('address');
                    
                    $cardStruct['tabs'][] = array(
                        'name'  => 'general',
                        'title' => __('Customer address'),
                        'type'  => 'form',
                        'form'  => $form->toArray()
                    );
                    $cardStruct['nextPoint']['url'] = Mage::getUrl('admin', array('controller'=>'customer', 'action'=>'wizard', 'step'=>3));
                }
                break;
            // Create preview
            case 3:
                if (!$this->_receiveAddressData()) {
                    $cardStruct['error'] = 1;
                    $cardStruct['errorMessage'] = 'Address validation error';
                }
                else {
                    $previewBlock = Mage::getSingleton('core/layout')->createBlock('core/template')
                        ->setTemplate('admin/customer/preview.phtml');
                    $cardStruct['tabs'][] = array(
                        'name'  => 'preview',
                        'title' => __('New customer create information'),
                        'type'  => 'view',
                        'content' => $previewBlock->toHtml()
                    );
                    $cardStruct['saveUrl']  = Mage::getUrl('admin', array('controller'=>'customer', 'action'=>'create'));
                    $cardStruct['btnFinish']= true;
                }
                break;
        }
        
        $cardStruct['title'] = __('New Customer');
        return Zend_Json::encode($cardStruct);
    }
    
    protected function _receiveAccountData()
    {
        $data = $this->_request->getPost();
        return true;
    }
    
    protected function _receiveAddressData()
    {
        $data = $this->_request->getPost('address');
        return true;
    }
    
    public function getContent()
    {
        return $this->getStepContent();
    }
}
