<?php

class Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend_Tax
    extends Mage_Sales_Model_Entity_Quote_Address_Attribute_Backend
{
    public function collectTotals(Mage_Sales_Model_Quote_Address $address)
    {
        $address->setTaxAmount(0);
        
        $tax = Mage::getModel('tax/rate_data')
        	->setRegionId($address->getRegionId())
        	->setPostcode($address->getPostcode())
        	->setCustomerClassId($address->getQuote()->getCustomerTaxClassId());
        
        foreach ($address->getAllItems() as $item) {
        	$tax->setProductClassId($item->getTaxClassId());
            $item->setTaxPercent($tax->getRate());
            $item->calcTaxAmount();
            $address->setTaxAmount($address->getTaxAmount() + $item->getTaxAmount());
        }
        
        $address->setGrandTotal($address->getGrandTotal() + $address->getTaxAmount());
        return $this;
    }

}