<?php
/* 
 * {license_notice}
 * 
 * @category   Mage
 * @package    Mage_LoadTest
 * @copyright  {copyright}
 * @license    {license_link}
 */


abstract class Mage_LoadTest_Model_Renderer_Sales_Item_Type_Abstract {

    protected $_products = array();
    protected $_product = null;
    protected $_typeInstance = null;

    public function __construct($params = array())
    {
	/*$product_id = $params['product']->getId();
	if(!isset($this->_products[$product_id]))
	    $this->_products[$product_id] = $params['product'];
	$this->_product = $this->_products[$product_id];
	$this->_typeInstance = $this->_product->getTypeInstance();*/
    }

    protected function _getAllowedQty()
    {
	$qty = 1;
	if ($max = $this->_product->getStockItem()->getQty()) {
	    $qty = rand(1, $max);
        }
	return $qty;
    }

    abstract public function prepareRequestForCart($_product);
}


