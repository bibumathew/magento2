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
 * @category   Enterprise
 * @package    Enterprise_CustomerSegment
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */


class Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Storecredit
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    protected $_inputType = 'numeric';

    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_customer_storecredit');
        $this->setValue(null);
    }

    public function getNewChildSelectOptions()
    {
        return array(array('value' => $this->getType(),
            'label'=>Mage::helper('enterprise_customersegment')->__('Store Credit')));
    }

    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('Customer Store Credit Amount %s %s:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml())
            . $this->getRemoveLinkHtml();
    }

    public function getConditionsSql($customer)
    {
        $table = $this->getResource()->getTable('enterprise_customerbalance/balance');
        $operator = $this->_getSqlOperator();

        $select = $this->getResource()->createSelect();
        $select->from($table, array(new Zend_Db_Expr(1)))
            ->where('customer_id = ?', $customer->getId())
            ->limit(1);

        $select->where("amount {$operator} ?", $this->getValue());

        return $select;
    }
}
