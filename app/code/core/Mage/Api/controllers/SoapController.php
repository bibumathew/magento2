<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Api
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * SOAP API controller.
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_SoapController extends Mage_Api_Controller_Action
{
    public function indexAction()
    {
        $handlerName = 'soap_v2';
        /* @var $server Mage_Api_Model_Server */
        $this->_getServer()->init($this, $handlerName, $handlerName)->run();
    }
}