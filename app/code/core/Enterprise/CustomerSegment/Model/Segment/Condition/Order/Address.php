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

class Enterprise_CustomerSegment_Model_Segment_Condition_Order_Address
    extends Enterprise_CustomerSegment_Model_Segment_Condition_Combine
{
    protected $_inputType = 'select';

    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_order_address');
    }

    public function getNewChildSelectOptions()
    {
        $conditions = array(array(
                'value' => 'enterprise_customersegment/segment_condition_order_address_combine',
                'label' => Mage::helper('enterprise_customersegment')->__('Conditions Combination')),
            Mage::getModel('enterprise_customersegment/segment_condition_order_address_attributes')
                ->getNewChildSelectOptions()
        );
        $conditions = array_merge_recursive(Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions(), $conditions);
        return $conditions;
    }

    public function loadValueOptions()
    {
        $this->setValueOption(array(
            'any'  => Mage::helper('enterprise_customersegment')->__('Any'),
            'all'  => Mage::helper('enterprise_customersegment')->__('All'),
            'billing'  => Mage::helper('enterprise_customersegment')->__('Billing'),
            'shipping'  => Mage::helper('enterprise_customersegment')->__('Shipping'),
        ));
        return $this;
    }

    public function getValueElementType()
    {
        return 'select';
    }

    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('If Order %s Address(es) match %s of these Conditions:',
                $this->getValueElementHtml(), $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }
}
