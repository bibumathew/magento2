<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Product
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Product creation with custom options tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Product_Create_CustomOptionsTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions</p>
     * <p>Navigate to Catalog->Manage Products</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_products');
    }

    /**
     * <p>Create product with custom options</p>
     *
     * @TestlinkId TL-MAGE-3382
     * @test
     */
    public function productWithAllTypesCustomOption()
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $productData['custom_options_data'] = $this->loadDataSet('Product', 'custom_options_data');
        $productSearch =
            $this->loadDataSet('Product', 'product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($productData);
    }

    /**
     * <p>Create product with empty required field in custom options</p>
     *
     * @param $emptyCustomField
     *
     * @dataProvider emptyFieldInCustomOptionDataProvider
     *
     * @TestlinkId TL-MAGE-3376
     * @test
     */
    public function emptyFieldInCustomOption($emptyCustomField)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $productData['custom_options_data'][] =
            $this->loadDataSet('Product', 'custom_options_empty', array($emptyCustomField => '%noValue%'));
        //Steps
        $this->productHelper()->createProduct($productData, 'simple', false);
        //Verifying
        $this->assertTrue($this->controlIsVisible('button', 'save_disabled'));
//        if ($emptyCustomField == 'custom_options_general_title') {
//            $this->addFieldIdToMessage('field', $emptyCustomField);
//            $this->assertMessagePresent('validation', 'empty_required_field');
//        } else {
//            $this->assertMessagePresent('validation', 'select_type_of_option');
//        }
//        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function emptyFieldInCustomOptionDataProvider()
    {
        return array(
            array('custom_options_general_title'),
            array('custom_options_general_input_type')
        );
    }

    /**
     * <p>Create product with CustomOption: Empty field 'option row Title' if 'Input Type'='Select' type</p>
     *
     * @param string $optionDataName
     *
     * @dataProvider emptyOptionRowTitleInCustomOptionDataProvider
     *
     * @TestlinkId TL-MAGE-3377
     * @test
     */
    public function emptyOptionRowTitleInCustomOption($optionDataName)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $productData['custom_options_data'][] =
            $this->loadDataSet('Product', $optionDataName, array('custom_options_title' => '%noValue%'));
        //Steps
        $this->productHelper()->createProduct($productData, 'simple', false);
        //Verifying
        $this->assertTrue($this->controlIsVisible('button', 'save_disabled'));
//        $this->addFieldIdToMessage('field', 'custom_options_title');
//        $this->assertMessagePresent('validation', 'empty_required_field');
//        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function emptyOptionRowTitleInCustomOptionDataProvider()
    {
        return array(
            array('custom_options_dropdown'),
            array('custom_options_radiobutton'),
            array('custom_options_checkbox'),
            array('custom_options_multipleselect')
        );
    }

    /**
     * <p>Create product with invalid "Sort Order" into custom options</p>
     *
     * @param $invalidData
     *
     * @dataProvider invalidNumericValueDataProvider
     *
     * @TestlinkId TL-MAGE-3379
     * @test
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function invalidSortOrderInCustomOption($invalidData)
    {
        //Data
        $invalidSortOrder = array('custom_options_general_sort_order' => $invalidData,
                                  'custom_options_sort_order'         => $invalidData);
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $productData['custom_options_data'][] =
            $this->loadDataSet('Product', 'custom_options_multipleselect', $invalidSortOrder);
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        foreach ($invalidSortOrder as $key => $value) {
            $this->addFieldIdToMessage('field', $key);
            $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        }
        $this->assertTrue($this->verifyMessagesCount(2), $this->getParsedMessages());
    }

    /**
     * <p>Create product custom option: use invalid value for field 'Max Characters'</p>
     *
     * @param $invalidData
     *
     * @dataProvider invalidNumericValueDataProvider
     *
     * @TestlinkId TL-MAGE-3378
     * @test
     */
    public function invalidMaxCharInCustomOption($invalidData)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $productData['custom_options_data'][] = $this->loadDataSet('Product', 'custom_options_field',
            array('custom_options_max_characters' => $invalidData));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_max_characters');
        $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function invalidNumericValueDataProvider()
    {
        return array(
            array($this->generate('string', 9, ':punct:')),
            array($this->generate('string', 9, ':alpha:')),
            array('g3648GJTest'),
            array('-128')
        );
    }

    /**
     * <p>Create product with Custom Option: Use special symbols for filling field 'Price'</p>
     *
     * @param $optionDataName
     *
     * @dataProvider invalidCustomOptionDataProvider
     *
     * @TestlinkId TL-MAGE-3383
     * @test
     */
    public function specialSymbolsInCustomOptionsPrice($optionDataName)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $string = $this->generate('string', 9, ':punct:');
        $productData['custom_options_data'][] =
            $this->loadDataSet('Product', $optionDataName, array('custom_options_price' => $string));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_price');
        if ($optionDataName == 'custom_options_file') {
            $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        } else {
            $this->assertMessagePresent('validation', 'enter_valid_number');
        }
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Create product with Custom Option: Use text value for filling field 'Price'</p>
     *
     * @param $optionDataName
     *
     * @dataProvider invalidCustomOptionDataProvider
     *
     * @TestlinkId TL-MAGE-3384
     * @test
     */
    public function textValueInCustomOptionsPrice($optionDataName)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $string = $this->generate('string', 9, ':alpha:');
        $productData['custom_options_data'][] =
            $this->loadDataSet('Product', $optionDataName, array('custom_options_price' => $string));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_price');
        if ($optionDataName == 'custom_options_file') {
            $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        } else {
            $this->assertMessagePresent('validation', 'enter_valid_number');
        }
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function invalidCustomOptionDataProvider()
    {
        return array(
            array('custom_options_field'),
            array('custom_options_area'),
            array('custom_options_file'),
            array('custom_options_date'),
            array('custom_options_date_time'),
            array('custom_options_time'),
            array('custom_options_dropdown'),
            array('custom_options_radiobutton'),
            array('custom_options_checkbox'),
            array('custom_options_multipleselect')
        );
    }

    /**
     * <p>Create product with Custom Option: Use negative number for filling field 'Price'</p>
     *
     * @param $optionName
     *
     * @dataProvider negativeNumberInCustomOptionsPriceNegDataProvider
     *
     * @TestlinkId TL-MAGE-3380
     * @test
     */
    public function negativeNumberInCustomOptionsPriceNeg($optionName)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $productData['custom_options_data'][] =
            $this->loadDataSet('Product', $optionName, array('custom_options_price' => -123));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->addFieldIdToMessage('field', 'custom_options_price');
        $this->assertMessagePresent('validation', 'enter_zero_or_greater');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    public function negativeNumberInCustomOptionsPriceNegDataProvider()
    {
        return array(
            array('custom_options_file')
        );
    }

    /**
     * <p>Create product with Custom Option: Use negative number for filling field 'Price'</p>
     *
     * @param $optionName
     *
     * @dataProvider negativeNumberInCustomOptionsPricePosDataProvider
     *
     * @TestlinkId TL-MAGE-3381
     * @test
     */
    public function negativeNumberInCustomOptionsPricePos($optionName)
    {
        //Data
        $productData = $this->loadDataSet('Product', 'simple_product_required');
        $productData['custom_options_data'][] =
            $this->loadDataSet('Product', $optionName, array('custom_options_price' => -123));
        $productSearch =
            $this->loadDataSet('Product', 'product_search', array('product_sku' => $productData['general_sku']));
        //Steps
        $this->productHelper()->createProduct($productData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->productHelper()->openProduct($productSearch);
        //Verifying
        $this->productHelper()->verifyProductInfo($productData);
    }

    public function negativeNumberInCustomOptionsPricePosDataProvider()
    {
        return array(
            array('custom_options_field'),
            array('custom_options_area'),
            array('custom_options_dropdown'),
            array('custom_options_radiobutton'),
            array('custom_options_checkbox'),
            array('custom_options_multipleselect'),
            array('custom_options_date'),
            array('custom_options_date_time'),
            array('custom_options_time')
        );
    }
}