<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Compare Products tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CompareProducts_CompareProductsTest extends Mage_Selenium_TestCase
{
    //Id of the compare pop-up window to close.
    protected static $_popupId = null;

    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('Tax/default_tax_config');
    }

    protected function assertPreConditions()
    {
        self::$_popupId = null;
        $this->frontend('about_us');
        $this->assertTrue($this->compareProductsHelper()->frontClearAll());
    }

    protected function tearDownAfterTest()
    {
        if (self::$_popupId) {
            $this->compareProductsHelper()->frontCloseComparePopup(self::$_popupId);
        }
    }

    /**
     * @return array
     * @test
     * @skipTearDown
     */
    public function preconditionsForTests()
    {
        //Data
        $category = $this->loadDataSet('Category', 'sub_category_required');
        $path = $category['parent_category'] . '/' . $category['name'];
        $simple = $this->loadDataSet('CompareProducts', 'compare_simple_product', array('categories' => $path));
        $virtual = $this->loadDataSet('CompareProducts', 'compare_virtual_product', array('categories' => $path));
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($category);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_category');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->createProduct($virtual, 'virtual');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->reindexInvalidedData();
        $this->flushCache();
        return array('catName' => $category['name'],
                     'names'   => array($simple['general_name'], $virtual['general_name']),
                     'verify'  => array('product_1_name' => $simple['general_name'],
                                        'product_1_sku'  => $simple['general_sku'],
                                        'product_2_name' => $virtual['general_name'],
                                        'product_2_sku'  => $virtual['general_sku']));
    }

    /**
     * <p>Adds a product to Compare Products from Product Details page.</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to Compare Products</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed.</p>
     * <p>Product is displayed in Compare Products pop-up window on About Us page</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-3234
     */
    public function addProductToCompareListFromProductPage($data)
    {
        $verify = $this->loadDataSet('CompareProducts', 'verify_compare_data', null, $data['verify']);
        //Steps and Verifying
        foreach ($data['names'] as $value) {
            $this->compareProductsHelper()->frontAddToCompareFromProductPage($value);
            $this->assertMessagePresent('success', 'product_added_to_comparison');
            $this->frontend('about_us');
            $this->assertTrue($this->controlIsPresent('link', 'compare_product_link'),
                'Product is not available in Compare widget');
        }
        //Steps
        self::$_popupId = $this->compareProductsHelper()->frontOpenComparePopup();
        //Verifying
        $this->compareProductsHelper()->frontVerifyProductDataInComparePopup($verify);
    }

    /**
     * <p>Adds a products to Compare Products from Category page.</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to Compare Products</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed.Products displayed in Compare Products pop-up window</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3233
     */
    public function addProductToCompareListFromCatalogPage($data)
    {
        //Steps and Verifying
        foreach ($data['names'] as $value) {
            $this->compareProductsHelper()->frontAddToCompareFromCatalogPage($value, $data['catName']);
            $this->assertMessagePresent('success', 'product_added_to_comparison');
            $this->assertTrue($this->controlIsPresent('link', 'compare_product_link'),
                'Product is not available in Compare widget');
            self::$_popupId = $this->compareProductsHelper()->frontOpenComparePopup();
            $this->assertTrue($this->controlIsPresent('link', 'product_title'),
                'There is no expected product in Compare Products popup');
            $this->compareProductsHelper()->frontCloseComparePopup(self::$_popupId);
        }
        self::$_popupId = null;
    }

    /**
     * <p>Remove a product from CompareProducts block</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add two products to CompareProducts</p>
     * <p>3. Remove products from Compare Products</p>
     * <p>Expected result:</p>
     * <p>Success message is displayed</p>
     * <p>Products should not be displayed in the Compare Products pop-up</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId    TL-MAGE-3236
     */
    public function removeProductFromCompareBlockList($data)
    {
        //Steps and Verifying
        foreach ($data['names'] as $value) {
            $this->compareProductsHelper()->frontAddToCompareFromCatalogPage($value, $data['catName']);
            $this->assertMessagePresent('success', 'product_added_to_comparison');
            $this->compareProductsHelper()->frontRemoveProductFromCompareBlock($value);
            $this->assertMessagePresent('success', 'product_removed_from_comparison');
            $this->assertFalse($this->controlIsPresent('link', 'compare_product_link'),
                'There is unexpected product in Compare Products widget');
        }
    }

    /**
     * <p>Compare Products block is not displayed without products</p>
     * <p>Steps:</p>
     * <p>1. Open product</p>
     * <p>2. Add product to Compare Products</p>
     * <p>3. Remove product from Compare Products</p>
     * <p>Expected result:</p>
     * <p>Compare Products block should be empty</p>
     *
     * @param array $data
     *
     * @test
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-3235
     */
    public function emptyCompareListIsNotAvailable($data)
    {
        //Steps
        $this->compareProductsHelper()->frontAddToCompareFromCatalogPage($data['names'][0], $data['catName']);
        //Verifying
        $this->assertMessagePresent('success', 'product_added_to_comparison');
        $this->assertTrue($this->controlIsPresent('link', 'compare_product_link'),
            'Product is not available in Compare widget');
        //Steps
        $this->compareProductsHelper()->frontClearAll();
        $this->assertMessagePresent('success', 'compare_list_cleared');
        //Verifying
        $this->assertTrue($this->controlIsPresent('pageelement', 'compare_block_empty'),
            'There is unexpected product(s) in Compare Products widget');
    }
}