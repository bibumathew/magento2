<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Core_Model_Resource_Layout_Update_CollectionTest extends Mage_Core_Model_Resource_Layout_AbstractTestCase
{
    /**
     * Retrieve layout update collection instance
     *
     * @param Zend_Db_Select $select
     * @return Mage_Core_Model_Resource_Layout_Update_Collection
     */
    protected function _getCollection(Zend_Db_Select $select)
    {
        return new Mage_Core_Model_Resource_Layout_Update_Collection($this->_getResource($select));
    }

    public function testAddThemeFilter()
    {
        $themeId = 1;
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $select->expects($this->once())
            ->method('where')
            ->with('link.theme_id = ?', $themeId);

        $collection = $this->_getCollection($select);
        $collection->addThemeFilter($themeId);
    }

    public function testAddStoreFilter()
    {
        $storeId = 1;
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $select->expects($this->once())
            ->method('where')
            ->with('link.store_id = ?', $storeId);

        $collection = $this->_getCollection($select);
        $collection->addStoreFilter($storeId);
    }

    /**
     * @covers Mage_Core_Model_Resource_Layout_Update_Collection::_joinWithLink
     */
    public function testJoinWithLink()
    {
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $select->expects($this->once())
            ->method('join')
            ->with(
                array('link' => 'core_layout_link'),
                'link.layout_update_id = main_table.layout_update_id',
                $this->isType('array')
            );

        $collection = $this->_getCollection($select);
        $collection->addStoreFilter(1);
        $collection->addThemeFilter(1);
    }

    public function testAddNoLinksFilter()
    {
        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);
        $select->expects($this->once())
            ->method('joinLeft')
            ->with(
                array('link' => 'core_layout_link'),
                'link.layout_update_id = main_table.layout_update_id',
                array(array())
            );
        $select->expects($this->once())
            ->method('where')
            ->with(self::TEST_WHERE_CONDITION);

        $collection = $this->_getCollection($select);

        /** @var $connection PHPUnit_Framework_MockObject_MockObject */
        $connection = $collection->getResource()->getReadConnection();
        $connection->expects($this->once())
            ->method('prepareSqlCondition')
            ->with('link.layout_update_id', array('null' => true))
            ->will($this->returnValue(self::TEST_WHERE_CONDITION));

        $collection->addNoLinksFilter();
    }
}