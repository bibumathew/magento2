<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_XmlConnect_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('xmlconnect/history', 'history_id');

    }

    /**
     * Serialization for 'params' variable
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setParams(serialize($object->getParams()));
        return parent::_beforeSave($object);
    }

    /**
     * Deserialization for 'params' variable
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setParams(unserialize($object->getParams()));
        return parent::_afterLoad($object);
    }

    /**
     * Returns array of existing images
     *
     * @param int $id   -  application instance Id
     *
     * @return array
     */
    public function getLastParams($id)
    {
        $paramArray = array();
        $idFieldName = Mage::getModel('xmlconnect/application')->getIdFieldName();
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), 'params')
            ->where($idFieldName . '=?', $id)
            ->order(array('created_at DESC'));

        $params = $this->_getReadAdapter()->fetchOne($select);

        if (isset($params)) {
            $paramArray = unserialize($params);
        }
        return $paramArray;
    }

    /**
     * Fetches websiteId for entity
     *
     * @param int
     * @return string
     */
    public function getParams2($entityId)
    {
        return $this->_getReadAdapter()->fetchOne(
            $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), 'website_id')
                ->where($this->getIdFieldName() . ' = ?', $entityId));
    }

//    /**
//     * Save Application submition history
//     *
//     * @param Mage_XmlConnect_Model_Application $application
//     * @param string $code
//     * @return Mage_XmlConnect_Model_Mysql4_Application
//     */
//    public function saveHistory(Mage_XmlConnect_Model_Application $application)
//    {
//        $data['application_id'] = $application->getId();
//        $data['result'] = $application->getResult();
//        $data['success'] = $application->getSuccess();
//        $data['created_at'] = Mage::getModel('core/date')->date();
//        $this->_getWriteAdapter()->insert($this->_historyTable, $data);
//    }
//
//    /**
//     * Load application history
//     *
//     * @param Mage_XmlConnect_Model_Application $application
//     *
//     * @return array
//     */
//    public function fetchHistory(Mage_XmlConnect_Model_Application $application)
//    {
//        $select = $this->_getReadAdapter()->select()
//            ->from($this->_historyTable, array('success', 'created_at'))
//            ->where('application_id=?', $application->getId())
//            ->where('success=1');
//
//        return $this->_getReadAdapter()->fetchAssoc($select, '*');
//    }
}
