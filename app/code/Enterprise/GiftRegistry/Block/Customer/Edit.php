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
class Enterprise_GiftRegistry_Block_Customer_Edit extends Mage_Directory_Block_Data
{
    /**
     * Template container
     *
     * @var array
     */
    protected $_inputTemplates = array();

    /**
     * Return edit form header
     *
     * @return string
     */
    public function getFormHeader()
    {
        if (Mage::registry('enterprise_giftregistry_entity')->getId()) {
            return Mage::helper('Enterprise_GiftRegistry_Helper_Data')->__('Edit Gift Registry');
        } else {
            return Mage::helper('Enterprise_GiftRegistry_Helper_Data')->__('Create Gift Registry');
        }
    }

    /**
     * Getter for post data, stored in session
     *
     * @return array|null
     */
    public function getFormDataPost()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session')->getGiftRegistryEntityFormData(true);
    }

    /**
     * Get array of reordered custom registry attributes
     *
     * @return array
     */
    public function getGroupedRegistryAttributes()
    {
        $attributes = $this->getEntity()->getCustomAttributes();
        return empty($attributes['registry']) ? array() : $this->_groupAttributes($attributes['registry']);
    }

    /**
     * Get array of reordered custom registrant attributes
     *
     * @return array
     */
    public function getGroupedRegistrantAttributes()
    {
        $attributes = $this->getEntity()->getCustomAttributes();
        return empty($attributes['registrant']) ? array() : $this->_groupAttributes($attributes['registrant']);
    }

    /**
     * Fetches type list array
     *
     * @return array
     */
    public function getTypeList()
    {
        $storeId = Mage::app()->getStore()->getId();
        $collection = Mage::getModel('Enterprise_GiftRegistry_Model_Type')
            ->getCollection()
            ->addStoreData($storeId)
            ->applyListedFilter()
            ->applySortOrder();
        $list = $collection->toOptionArray();
        return $list;
    }

    /**
     * Return "create giftregistry" form Add url
     *
     * @return string
     */
    public function getAddActionUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/edit');
    }

    /**
     * Return form back link url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('giftregistry');
    }

    /**
     * Return "create giftregistry" form AddPost url
     *
     * @return string
     */
    public function getAddPostActionUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/addPost');
    }

    /**
     * Return "create giftregistry" form url
     *
     * @return string
     */
    public function getAddGiftRegistryUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/addselect');
    }

    /**
     * Return "create giftregistry" form url
     *
     * @return string
     */
    public function getSaveActionUrl()
    {
        return $this->getUrl('enterprise_giftregistry/index/save');
    }

    /**
     * Setup template from template file as $_inputTemplates['type'] for specified type
     *
     * @param string $type
     * @param string $template
     * @return Enterprise_GiftRegistry_Block_Customer_Edit
     */
    public function addInputTypeTemplate($type, $template)
    {
        $params = array('_relative'=>true);
        $area = $this->getArea();
        if ($area) {
            $params['area'] = $area;
        }
        $templateName = Mage::getDesign()->getFilename($template, $params);

        $this->_inputTemplates[$type] = $templateName;
        return $this;
    }

    /**
     * Return presetted template by type
     * @param string $type
     * @return string
     */
    public function getInputTypeTemplate($type)
    {
        if (isset($this->_inputTemplates[$type])) {
            return $this->_inputTemplates[$type];
        }
        return false;
    }
}