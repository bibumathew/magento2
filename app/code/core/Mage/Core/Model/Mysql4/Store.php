<?php

class Mage_Core_Model_Mysql4_Store extends Mage_Core_Model_Resource_Abstract
{
    protected function _construct()
    {
        $this->_init('core/resource', 'store_id');
    }
}