<?php
class Mage_Core_Controller_Varien_Router_Default extends Mage_Core_Controller_Varien_Router_Abstract
{
    public function match(Zend_Controller_Request_Http $request)
    {
        //default route (404)
        $d = explode('/', Mage::getStoreConfig('web/default/no_route'));

        $request->setModuleName(isset($d[0]) ? $d[0] : 'core')
            ->setControllerName(isset($d[1]) ? $d[1] : 'index')
            ->setActionName(isset($d[2]) ? $d[2] : 'index');
        
        return true;
    }
    
    public function getUrl($routeName, $params)
    {
        return 'no-route';
    }
}