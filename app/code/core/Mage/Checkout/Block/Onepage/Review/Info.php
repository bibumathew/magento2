<?php
/**
 * One page checkout status
 *
 * @package    Mage
 * @subpackage Checkout
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Checkout_Block_Onepage_Review_Info extends Mage_Checkout_Block_Onepage_Abstract
{    
    public function getItems()
    {
        $itemsFilter = new Varien_Filter_Object_Grid();
        $itemsFilter->addFilter(new Varien_Filter_Sprintf('%d'), 'qty');
        $itemsFilter->addFilter(new Varien_Filter_Sprintf('$%s', 2), 'price');
        $itemsFilter->addFilter(new Varien_Filter_Sprintf('$%s', 2), 'row_total');
        return $itemsFilter->filter($this->getQuote()->getAllItems());
    }
    
    public function getTotals()
    {
        $totalsFilter = new Varien_Filter_Object_Grid();
        $totalsFilter->addFilter(new Varien_Filter_Sprintf('$%s', 2), 'value');
        return $totalsFilter->filter($this->getQuote()->getTotals());
    }
}