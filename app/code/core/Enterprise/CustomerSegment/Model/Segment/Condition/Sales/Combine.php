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


class Enterprise_CustomerSegment_Model_Segment_Condition_Sales_Combine
    extends Enterprise_CustomerSegment_Model_Segment_Condition_Combine
{
    /**
     * Intialize model
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_sales_combine');
    }

    /**
     * Return options for check new condition elemtnt
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = array();
        $conditions[] = array('value'=>$this->getType(), 'label'=>Mage::helper('enterprise_customersegment')->__('Conditions Combination'));
        $conditions[] = Mage::getModel('enterprise_customersegment/segment_condition_sales_salesamount')->getNewChildSelectOptions();
        $conditions[] = Mage::getModel('enterprise_customersegment/segment_condition_sales_ordersnumber')->getNewChildSelectOptions();
        $conditions[] = Mage::getModel('enterprise_customersegment/segment_condition_sales_purchasedquantity')->getNewChildSelectOptions();
        $conditions = array_merge_recursive(Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }
}
