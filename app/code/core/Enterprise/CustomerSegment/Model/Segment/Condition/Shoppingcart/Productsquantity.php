<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_CustomerSegment
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Shopping cart product qty condition
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Shoppingcart_Productsquantity
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    protected $_inputType = 'numeric';

    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_shoppingcart_productsquantity');
        $this->setValue(null);
    }

    /**
     * Get array of event names where segment with such conditions combine can be matched
     *
     * @return array
     */
    public function getMatchedEvents()
    {
        return array('sales_quote_save_commit_after');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return array('value' => $this->getType(),
            'label' => Mage::helper('enterprise_customersegment')->__('Products Quantity'));
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('Shopping Cart Products Qty %s %s:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    /**
     * Get SQL select for matching shopping cart products count
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($customer, $website)
    {
        $table = $this->getResource()->getTable('sales/quote');
        $operator = $this->getResource()->getSqlOperator($this->getOperator());

        $select = $this->getResource()->createSelect();
        $select->from($table, array(new Zend_Db_Expr(1)))
            ->limit(1);

        $select->where("items_qty {$operator} ?", $this->getValue());
        $select->where($this->_createCustomerFilter($customer, 'customer_id'));

        return $select;
    }
}
