<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Webapi
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test Webapi filter ACL attribute resource collection model
 *
 * @category   Mage
 * @package    Mage_Webapi
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Webapi_Model_Resource_Acl_Filter_Attribute_CollectionTest extends Magento_TestCase
{
    /**
     * Get fixture data
     *
     * @return array
     */
    protected function _getFixture()
    {
        return require realpath(dirname(__FILE__) . '/../../../..') . '/Acl/_fixture/_data/attribute_data.php';
    }

    /**
     * Test collection
     */
    public function testCollection()
    {
        $data = $this->_getFixture();
        $cnt = 3;
        $ids = array();
        for ($i = $cnt; $i > 0; $i--) {
            /** @var $model Mage_Webapi_Model_Acl_Filter_Attribute */
            $model = Mage::getModel('Mage_Webapi_Model_Acl_Filter_Attribute');
            $setData = $data['create'];
            $setData['resource_id'] .= $i;
            $this->addModelToDelete($model);
            $model->setData($setData);
            $model->save();
            $ids[] = $model->getId();
        }

        /** @var $model Mage_Webapi_Model_Acl_Filter_Attribute */
        $model = Mage::getModel('Mage_Webapi_Model_Acl_Filter_Attribute');
        $collection = $model->getCollection();
        $collection->addFilter('main_table.entity_id', array('in' => $ids), 'public');
        $this->assertEquals($cnt, $collection->count());
        $this->assertInstanceOf(get_class($model), $collection->getFirstItem());
    }
}
