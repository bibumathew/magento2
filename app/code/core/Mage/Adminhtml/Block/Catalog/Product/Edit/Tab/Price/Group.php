<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Adminhtml group price item renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group_Abstract
{
    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('catalog/product/edit/price/group.phtml');
    }

    /**
     * Sort values
     *
     * @param array $data
     * @return array
     */
    protected function _sortValues($data)
    {
        usort($data, array($this, '_sortGroupPrices'));
        return $data;
    }

    /**
     * Sort group price values callback method
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortGroupPrices($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? -1 : 1;
        }
        if ($a['cust_group'] != $b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group']) < $this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        return 0;
    }

    /**
     * Prepare global layout
     *
     * Add "Add Group Price" button to layout
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Button')
            ->setData(array(
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Add Group Price'),
                'onclick' => 'return groupPriceControl.addItem()',
                'class' => 'add'
            ));
        $button->setName('add_group_price_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

}