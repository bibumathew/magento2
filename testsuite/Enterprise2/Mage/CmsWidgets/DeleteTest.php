<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_CmsWidgets
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Delete Widget Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Enterprise2_Mage_CmsWidgets_DeleteTest extends Mage_Selenium_TestCase
{
    protected function assertPreconditions()
    {
        $this->loginAdminUser();
    }

    /**
     * @return array
     * @test
     */
    public function preconditionsForTests()
    {
        return $this->productHelper()->createSimpleProduct(true);
    }

    /**
     * <p>Creates All Types of widgets with required fields only and delete them</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields filled</p>
     * <p>3. Open newly created widget</p>
     * <p>4. Delete opened widget</p>
     * <p>Expected result</p>
     * <p>Widgets are created and deleted successfully</p>
     *
     * @param array $dataWidgetType
     * @param array $testData
     *
     * @test
     * @dataProvider widgetTypesReqDataProvider
     * @depends preconditionsForTests
     * @TestlinkId TL-MAGE-3232
     */
    public function deleteAllTypesOfWidgets($dataWidgetType, $testData)
    {
        //Data
        $override = array();
        if ($dataWidgetType == 'catalog_product_link') {
            $override = array('filter_sku'    => $testData['simple']['product_sku'],
                              'category_path' => $testData['category']['path']);
        } elseif ($dataWidgetType == 'catalog_category_link') {
            $override = array('category_path' => $testData['category']['path']);
        }
        $widgetData = $this->loadDataSet('CmsWidget', $dataWidgetType . '_widget_req', $override);
        $widgetToDelete = array('filter_type'  => $widgetData['settings']['type'],
                                'filter_title' => $widgetData['frontend_properties']['widget_instance_title']);
        //Steps
        $this->navigate('manage_cms_widgets');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        $this->assertMessagePresent('success', 'successfully_saved_widget');
        $this->cmsWidgetsHelper()->deleteWidget($widgetToDelete);
        $this->assertMessagePresent('success', 'successfully_deleted_widget');
    }

    public function widgetTypesReqDataProvider()
    {
        return array(
            array('banner_rotator'),
            array('catalog_events_carousel'),
            array('giftregistry_search'),
            array('wishlist_search'),
        );
    }
}