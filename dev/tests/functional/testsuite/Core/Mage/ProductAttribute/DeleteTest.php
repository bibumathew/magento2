<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ProductAttribute
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Delete product attributes
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ProductAttribute_DeleteTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to System -> Manage Attributes.</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_attributes');
    }

    /**
     * <p>Delete Product Attributes</p>
     *
     * @param $dataName
     *
     * @test
     * @dataProvider deleteProductAttributeDeletableDataProvider
     * @TestlinkId TL-MAGE-3343
     */
    public function deleteProductAttributeDeletable($dataName)
    {
        //Data
        $attrData = $this->loadDataSet('ProductAttribute', $dataName);
        $searchData = $this->loadDataSet('ProductAttribute', 'attribute_search_data',
            array('attribute_code' => $attrData['attribute_code']));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        $this->clickButtonAndConfirm('delete_attribute', 'delete_confirm_message');
        //Verifying
        $this->assertMessagePresent('success', 'success_deleted_attribute');
    }

    public function deleteProductAttributeDeletableDataProvider()
    {
        return array(
            array('product_attribute_textfield'),
            array('product_attribute_textarea'),
            array('product_attribute_date'),
            array('product_attribute_yesno'),
            array('product_attribute_multiselect'),
            array('product_attribute_dropdown'),
            array('product_attribute_price'),
            array('product_attribute_mediaimage'),
            array('product_attribute_fpt')
        );
    }

    /**
     * <p>Delete system Product Attributes</p>
     *
     * @test
     * @TestlinkId TL-MAGE-3342
     */
    public function deletedSystemAttribute()
    {
        $searchData = $this->loadDataSet('ProductAttribute', 'attribute_search_data',
            array('attribute_code'  => 'description',
                  'attribute_label' => 'Description',
                  'system'          => 'Yes'));
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->assertFalse($this->buttonIsPresent('delete_attribute'),
            '"Delete Attribute" button is present on the page');
    }

    /**
     * Delete attribute that used in Configurable Product
     *
     * @test
     */
    public function deletedDropdownAttributeUsedInConfigurableProduct()
    {
        //Data
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_dropdown_with_options');
        $associatedAttributes = $this->loadDataSet('AttributeSet', 'associated_attributes',
            array('General' => $attrData['attribute_code']));
        $productData = $this->loadDataSet('Product', 'configurable_product_required',
            array('general_configurable_attribute_title' => $attrData['admin_title']));
        $searchData = $this->loadDataSet('ProductAttribute', 'attribute_search_data',
            array('attribute_code'  => $attrData['attribute_code'],
                  'attribute_label' => $attrData['admin_title']));
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        //Steps
        $this->navigate('manage_attribute_sets');
        $this->attributeSetHelper()->openAttributeSet();
        $this->attributeSetHelper()->addAttributeToSet($associatedAttributes);
        $this->saveForm('save_attribute_set');
        //Verifying
        $this->assertMessagePresent('success', 'success_attribute_set_saved');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'configurable');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->navigate('manage_attributes');
        $this->productAttributeHelper()->openAttribute($searchData);
        $this->clickButtonAndConfirm('delete_attribute', 'delete_confirm_message');
        //Verifying
        $this->assertMessagePresent('error', 'attribute_used_in_configurable');
    }
}