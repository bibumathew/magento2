<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer giftregistry list block
 *
 * @category   Enterprise
 * @package    Enterprise_GiftRegistry
 */
class Enterprise_GiftRegistry_Block_Customer_Edit_Registrants extends  Enterprise_GiftRegistry_Block_Customer_Edit_Abstract
{
    /**
     * Scope Selector 'registry/registrant'
     *
     * @var string
     */
    protected $_prefix = 'registrant';

    /**
     * Retrieve Max Recipients
     *
     * @param int $store
     * @return int
     */
    public function getMaxRegistrant()
    {
        return Mage::helper('Enterprise_GiftRegistry_Helper_Data')->getMaxRegistrant();
    }

    /**
     * Return array of attributes groupped by group
     *
     * @return array
     */
    public function getGroupedRegistrantAttributes()
    {
        return $this->getGroupedAttributes();
    }

    /**
     * Return registrant collection
     *
     * @return Enterprise_GiftRegistry_Model_Resource_Person_Collection
     */
    public function getRegistrantList() {
        return $this->getEntity->getRegistrantCollection();
    }

    /**
     * Reorder attributes array  by group
     *
     * @param array $attributes
     * @return array
     */
    protected function _groupAttributes($attributes)
    {
        $grouped = array();
        if (is_array($attributes)) {
            foreach ($attributes as $field => $fdata){
                if (is_array($fdata)) {
                    $grouped[$field] = $fdata;
                    $grouped[$field]['id'] = $this->_getElementId($field);
                    $grouped[$field]['name'] = $this->_getElementName($field);
                }
            }
        }
        return $grouped;
    }

    /**
     * Prepare html element name
     *
     * @param string $code
     * @return string
     */
    protected function _getElementName($code)
    {
        $custom = ($this->isAttributeStatic($code)) ? '' : '[custom]';
        return $this->_prefix . '[_index_]'. $custom . '[' . $code . ']';
    }

    /**
     * Prepare html element id
     *
     * @param string $code
     * @return string
     */
    protected function _getElementId($code)
    {
        $custom = ($this->isAttributeStatic($code)) ? '' : 'custom:';
        return $this->_prefix . ':'. $custom . $code . '_index_';
    }

    /**
     * Get current registrant info , formatted in php array of JSON data
     *
     * @param int - id of the giftregistry entity
     * @return array
     */
    public function getRegistrantPresets($entityId)
    {
        $data = array();
        $registrantCollection = $this->getEntity()->getRegistrantsCollection();
        foreach ($registrantCollection->getItems() as $registrant) {
            $data[] = $registrant->unserialiseCustom()->getData();
        }
        return $data;
    }
}