<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_SalesRule_Model_Resource_Report_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_SalesRule_Model_Resource_Report_Collection
     */
    private $_collection;

    protected function setUp()
    {
        $this->_collection = Mage::getResourceModel('Mage_SalesRule_Model_Resource_Report_Collection');
        $this->_collection
            ->setPeriod('day')
            ->setDateRange(null, null)
            ->addStoreFilter(array(1))
        ;
    }

    /**
     * @magentoDataFixture Mage/SalesRule/_files/order_with_coupon.php
     * @magentoDataFixture Mage/SalesRule/_files/report_coupons.php
     */
    public function testGetItems()
    {
        $expectedResult = array(
            array(
                'coupon_code' => '1234567890',
                'coupon_uses' => 1,
            ),
        );
        $actualResult = array();
        /** @var Mage_Adminhtml_Model_Report_Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
