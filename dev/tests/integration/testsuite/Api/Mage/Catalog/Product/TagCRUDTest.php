<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/**
 * @magentoApiDataFixture Mage/Catalog/Product/_fixture/TagCRUD.php
 */
class Mage_Catalog_Product_TagCRUDTest extends Magento_Test_TestCase_ApiAbstract
{
    /**
     * Test tag CRUD
     *
     * @return void
     */
    public function testTagCRUD()
    {
        $tagFixture = simplexml_load_file(dirname(__FILE__) . '/_fixture/_data/xml/TagCRUD.xml');
        $data = self::simpleXmlToObject($tagFixture->tagData);
        $expected = self::simpleXmlToObject($tagFixture->expected);

        $data['product_id'] = Magento_Test_TestCase_ApiAbstract::getFixture('productData')->getId();
        $data['customer_id'] = Magento_Test_TestCase_ApiAbstract::getFixture('customerData')->getId();

        // create test
        $createdTags = $this->call('catalogProductTagAdd', array('data' => $data));

        $this->assertCount(3, $createdTags);

        // Invalid product ID exception test
        try {
            $data['product_id'] = mt_rand(10000, 99999);
            $this->call('catalogProductTagAdd', array('data' => $data));
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
            $this->assertEquals('Requested product does not exist.', $e->getMessage());
        }

        // Invalid customer ID exception test
        try {
            $data['product_id'] = Magento_Test_TestCase_ApiAbstract::getFixture('productData')->getId();
            $data['customer_id'] = mt_rand(10000, 99999);
            $this->call('catalogProductTagAdd', array('data' => $data));
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
            $this->assertEquals('Requested customer does not exist.', $e->getMessage());
        }

        // Invalid store ID exception test
        try {
            $data['product_id'] = Magento_Test_TestCase_ApiAbstract::getFixture('productData')->getId();
            $data['customer_id'] = Magento_Test_TestCase_ApiAbstract::getFixture('customerData')->getId();
            $data->store = mt_rand(10000, 99999);
            $this->call('catalogProductTagAdd', array('data' => $data));
            $this->fail("Didn't receive exception!");
        } catch (Exception $e) {
            $this->assertEquals('Requested store does not exist.', $e->getMessage());
        }

        // items list test
        $tagsList = $this->call(
            'catalogProductTagList',
            array(
                'productId' => Magento_Test_TestCase_ApiAbstract::getFixture('productData')->getId(),
                'store' => 0
            )
        );
        $this->assertInternalType('array', $tagsList);
        $this->assertNotEmpty($tagsList, "Can't find added tag in list");
        $this->assertCount((int)$expected['created_tags_count'], $tagsList, "Can't find added tag in list");

        // delete test
        $tagToDelete = array_shift($tagsList);
        $tagDelete = $this->call('catalogProductTagRemove', array('tagId' => $tagToDelete['tag_id']));
        $this->assertTrue((bool)$tagDelete, "Can't delete added tag");

        // Delete exception test
        $this->setExpectedException(self::DEFAULT_EXCEPTION, 'Requested tag does not exist.');
        $this->call('catalogProductTagRemove', array('tagId' => $tagToDelete['tag_id']));
    }
}
