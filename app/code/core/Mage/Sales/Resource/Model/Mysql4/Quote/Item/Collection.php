<?php

class Mage_Sales_Resource_Model_Mysql4_Quote_Item_Collection extends Varien_Data_Collection_Db
{
    static protected $_itemTable = null;

    public function __construct() 
    {
        parent::__construct(Mage::registry('resources')->getConnection('sales_read'));
        self::$_itemTable = Mage::registry('resources')->getTableName('sales', 'quote_item');
        $this->_sqlSelect->from(self::$_itemTable);
        $this->setItemObjectClass(Mage::getConfig()->getResourceModelClassName('sales', 'quote_item'));
    }
    
    public function loadByQuoteId($quoteId)
    {
        $this->addFilter('quote_id', (int)$quoteId, 'and');
        $this->load();
        return $this;
    }
    
    public function getByItemId($itemId)
    {
        foreach ($this->getItems() as $item) {
            if ($item->getQuoteItemId()==$itemId) {
                return $item;
            }
        }
    }
}