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
 * Customer attributes condition
 */
class Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Attributes
    extends Enterprise_CustomerSegment_Model_Condition_Abstract
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_customer_attributes');
        $this->setValue(null);
    }

    /**
     * Get array of event names where segment with such conditions combine can be matched
     *
     * @return array
     */
    public function getMatchedEvents()
    {
        return array('customer_save_commit_after');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array('value' => $this->getType() . '|' . $code, 'label' => $label);
        }

        return $conditions;
    }

    /**
     * Retrieve attribute object
     *
     * @return Mage_Eav_Model_Entity_Attribute
     */
    public function getAttributeObject()
    {
        return Mage::getSingleton('eav/config')->getAttribute('customer', $this->getAttribute());
    }

    /**
     * Load condition options for castomer attributes
     *
     * @return Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Attributes
     */
    public function loadAttributeOptions()
    {
        $productAttributes = Mage::getResourceSingleton('customer/customer')
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = array();

        foreach ($productAttributes as $attribute) {
            if ($attribute->getIsUsedForCustomerSegment()) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }

    /**
     * Retrieve select option values
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        if (!$this->getData('value_select_options') && is_object($this->getAttributeObject())) {

            if ($this->getAttributeObject()->usesSource()) {
                if ($this->getAttributeObject()->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $optionsArr = $this->getAttributeObject()->getSource()->getAllOptions($addEmptyOption);
                $this->setData('value_select_options', $optionsArr);
               }

            if ($this->_isCurrentAttributeDefaultAddress()) {
                $optionsArr = $this->_getOptionsForAttributeDefaultAddress();
                $this->setData('value_select_options', $optionsArr);
            }

        }
        return $this->getData('value_select_options');
    }

    /**
     * Get input type for attribute operators.
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'string';
        }
    }

    /**
     * Get attribute value input element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'text';
        }
    }

    /**
     * Retrieve value element
     *
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                    break;
            }
        }
        return $element;
    }

    /**
     * Chechk if attribute value should be explicit
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
            }
        }
        return false;
    }

    /**
     * Retrieve attribute element
     *
     * @return Varien_Form_Element_Abstract
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * Get attribute operator html
     *
     * @return string
     */
    public function getOperatorElementHtml()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return '';
        }
        return parent::getOperatorElementHtml();
    }

    /**
     * Check if current condition attribute is default billing or shipping address
     *
     * @return bool
     */
    protected function _isCurrentAttributeDefaultAddress()
    {
        $code = $this->getAttributeObject()->getAttributeCode();
        return $code == 'default_billing' || $code == 'default_shipping';
    }

    /**
     * Get options for customer default address attributes value select
     *
     * @return array
     */
    protected function _getOptionsForAttributeDefaultAddress()
    {
        return array(
            array(
                'value' => 'is_exists',
                'label' => Mage::helper('enterprise_customersegment')->__('exists')
            ),
            array(
                'value' => 'is_not_exists',
                'label' => Mage::helper('enterprise_customersegment')->__('does not exist')
            ),
        );
    }

    /**
     * Customer attributes are standalone conditions, hence they must be self-sufficient
     *
     * @return string
     */
    public function asHtml()
    {
        return Mage::helper('enterprise_customersegment')->__('Customer %s', parent::asHtml());
    }

    /**
     * Create SQL condition select for customer attribute
     *
     * @param $customer
     * @param $website
     * @return Varien_Db_Select
     */
    public function getConditionsSql($customer, $website)
    {
        $attribute = $this->getAttributeObject();
        $table = $attribute->getBackendTable();
        $addressTable = $this->getResource()->getTable('customer/address_entity');

        $select = $this->getResource()->createSelect();
        $select->from(array('main'=>$table), array(new Zend_Db_Expr(1)))
            ->limit(1);
        $select->where($this->_createCustomerFilter($customer, 'main.entity_id'));

        if (!in_array($attribute->getAttributeCode(), array('default_billing', 'default_shipping')) ) {
            if ($attribute->isStatic()) {
                $condition = $this->getResource()->createConditionSql(
                    "main.{$attribute->getAttributeCode()}", $this->getOperator(), $this->getValue()
                );
            } else {
                $select->where('main.attribute_id = ?', $attribute->getId());
                $condition = $this->getResource()->createConditionSql(
                    'main.value', $this->getOperator(), $this->getValue()
                );
            }
            $select->where($condition);
        } else {
            $joinFunction = 'joinLeft';
            if ($this->getValue() == 'is_exists') {
                $joinFunction = 'joinInner';
            } else {
                $select->where('address.entity_id IS NULL');
            }
            $select->$joinFunction(array('address'=>$addressTable), 'address.entity_id = main.value', array());
            $select->where('main.attribute_id = ?', $attribute->getId());
        }
        return $select;
    }
}
