<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_SalesRule_Model_Rule_Condition_Product_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('Mage_SalesRule_Model_Rule_Condition_Product_Combine');
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('Mage_SalesRule_Model_Rule_Condition_Product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code=>$label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = array(
                    'value' => 'Mage_SalesRule_Model_Rule_Condition_Product|' . $code, 'label' => $label
                );
            } else {
                $pAttributes[] =
                    array('value' => 'Mage_SalesRule_Model_Rule_Condition_Product|' . $code, 'label' => $label);
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'Mage_SalesRule_Model_Rule_Condition_Product_Combine',
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Conditions Combination')
            ),
            array('label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Cart Item Attribute'),
                'value' => $iAttributes
            ),
            array('label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Product Attribute'),
                'value' => $pAttributes
            ),
        ));
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}