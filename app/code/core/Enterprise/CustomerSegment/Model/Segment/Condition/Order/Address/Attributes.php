<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @category   Mage
 * @package    Mage_CustomerSegment
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Enterprise_CustomerSegment_Model_Segment_Condition_Order_Address_Attributes extends Mage_Rule_Model_Condition_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_order_address_attributes');
        $this->setValue(null);
    }
	
	
    public function getNewChildSelectOptions()
    {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = array();
        foreach ($attributes as $code => $label) {
            $conditions[] = array('value'=> $this->getType() . '|' . $code, 'label'=>$label);
        }

        return $conditions;
    }

    /**
     * Load attribute options
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function loadAttributeOptions()
    {
    	$productAttributes = Mage::getResourceSingleton('sales/order_address')
            ->loadAllAttributes()
            ->getAttributesByCode();
            
        $attributes = array();
        foreach ($productAttributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
/*            if (!$attribute->isAllowedForRuleCondition() || !$attribute->getIsUsedForPriceRules()) {
                continue;
            }
*/          $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        	//$attributes[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
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
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'region':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
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

    
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'country_id': case 'region':
                return 'select';
        }
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id': case 'region':
                return 'select';
        }
        return 'text';
    }
    

}
