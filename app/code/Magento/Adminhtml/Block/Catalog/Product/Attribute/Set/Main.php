<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml Catalog Attribute Set Main Block
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Adminhtml_Block_Catalog_Product_Attribute_Set_Main extends Magento_Adminhtml_Block_Template
{
    protected $_template = 'catalog/product/attribute/set/main.phtml';

    /**
     * Catalog product
     *
     * @var Magento_Catalog_Helper_Product
     */
    protected $_catalogProduct = null;

    /**
     * Core registry
     *
     * @var Magento_Core_Model_Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Magento_Catalog_Helper_Product $catalogProduct
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Backend_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param array $data
     */
    public function __construct(
        Magento_Catalog_Helper_Product $catalogProduct,
        Magento_Core_Helper_Data $coreData,
        Magento_Backend_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_catalogProduct = $catalogProduct;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Prepare Global Layout
     *
     * @return Magento_Adminhtml_Block_Catalog_Product_Attribute_Set_Main
     */
    protected function _prepareLayout()
    {
        $setId = $this->_getSetId();

        $this->addChild('group_tree', 'Magento_Adminhtml_Block_Catalog_Product_Attribute_Set_Main_Tree_Group');

        $this->addChild('edit_set_form', 'Magento_Adminhtml_Block_Catalog_Product_Attribute_Set_Main_Formset');

        $this->addChild('delete_group_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('Delete Selected Group'),
            'onclick'   => 'editSet.submit();',
            'class'     => 'delete'
        ));

        $this->addChild('add_group_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('Add New'),
            'onclick'   => 'editSet.addGroup();',
            'class'     => 'add'
        ));

        $this->addChild('back_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('Back'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/').'\')',
            'class'     => 'back'
        ));

        $this->addChild('reset_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('Reset'),
            'onclick'   => 'window.location.reload()'
        ));

        $this->addChild('save_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('Save Attribute Set'),
            'onclick'   => 'editSet.save();',
            'class'     => 'save'
        ));

        $this->addChild('delete_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('Delete Attribute Set'),
            'onclick'   => 'deleteConfirm(\''. $this->jsQuoteEscape(__('You are about to delete all products in this set. Are you sure you want to delete this attribute set?')) . '\', \'' . $this->getUrl('*/*/delete', array('id' => $setId)) . '\')',
            'class'     => 'delete'
        ));

        $this->addChild('rename_button', 'Magento_Adminhtml_Block_Widget_Button', array(
            'label'     => __('New Set Name'),
            'onclick'   => 'editSet.rename()'
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Attribute Set Group Tree HTML
     *
     * @return string
     */
    public function getGroupTreeHtml()
    {
        return $this->getChildHtml('group_tree');
    }

    /**
     * Retrieve Attribute Set Edit Form HTML
     *
     * @return string
     */
    public function getSetFormHtml()
    {
        return $this->getChildHtml('edit_set_form');
    }

    /**
     * Retrieve Block Header Text
     *
     * @return string
     */
    protected function _getHeader()
    {
        return __("Edit Attribute Set '%1'", $this->_getAttributeSet()->getAttributeSetName());
    }

    /**
     * Retrieve Attribute Set Save URL
     *
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('*/catalog_product_set/save', array('id' => $this->_getSetId()));
    }

    /**
     * Retrieve Attribute Set Group Save URL
     *
     * @return string
     */
    public function getGroupUrl()
    {
        return $this->getUrl('*/catalog_product_group/save', array('id' => $this->_getSetId()));
    }

    /**
     * Retrieve Attribute Set Group Tree as JSON format
     *
     * @return string
     */
    public function getGroupTreeJson()
    {
        $items = array();
        $setId = $this->_getSetId();

        /* @var $groups Magento_Eav_Model_Resource_Entity_Attribute_Group_Collection */
        $groups = Mage::getModel('Magento_Eav_Model_Entity_Attribute_Group')
            ->getResourceCollection()
            ->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->load();

        $configurable = Mage::getResourceModel('Magento_Catalog_Model_Resource_Product_Type_Configurable_Attribute')
            ->getUsedAttributes($setId);

        $unassignableAttributes = $this->_catalogProduct->getUnassignableAttributes();

        /* @var $node Magento_Eav_Model_Entity_Attribute_Group */
        foreach ($groups as $node) {
            $item = array();
            $item['text']       = $node->getAttributeGroupName();
            $item['id']         = $node->getAttributeGroupId();
            $item['cls']        = 'folder';
            $item['allowDrop']  = true;
            $item['allowDrag']  = true;

            $nodeChildren = Mage::getResourceModel('Magento_Catalog_Model_Resource_Product_Attribute_Collection')
                ->setAttributeGroupFilter($node->getId())
                ->addVisibleFilter()
                ->load();

            if ($nodeChildren->getSize() > 0) {
                $item['children'] = array();
                foreach ($nodeChildren->getItems() as $child) {
                    /* @var $child Magento_Eav_Model_Entity_Attribute */

                    $isUnassignable = !in_array($child->getAttributeCode(), $unassignableAttributes);

                    $attr = array(
                        'text'              => $child->getAttributeCode(),
                        'id'                => $child->getAttributeId(),
                        'cls'               => $isUnassignable ? 'leaf' : 'system-leaf',
                        'allowDrop'         => false,
                        'allowDrag'         => true,
                        'leaf'              => true,
                        'is_user_defined'   => $child->getIsUserDefined(),
                        'is_configurable'   => (int)in_array($child->getAttributeId(), $configurable),
                        'is_unassignable'   => $isUnassignable,
                        'entity_id'         => $child->getEntityAttributeId()
                    );

                    $item['children'][] = $attr;
                }
            }

            $items[] = $item;
        }

        return $this->_coreData->jsonEncode($items);
    }

    /**
     * Retrieve Unused in Attribute Set Attribute Tree as JSON
     *
     * @return string
     */
    public function getAttributeTreeJson()
    {
        $items = array();
        $setId = $this->_getSetId();

        $collection = Mage::getResourceModel('Magento_Catalog_Model_Resource_Product_Attribute_Collection')
            ->setAttributeSetFilter($setId)
            ->load();

        $attributesIds = array('0');
        /* @var $item Magento_Eav_Model_Entity_Attribute */
        foreach ($collection->getItems() as $item) {
            $attributesIds[] = $item->getAttributeId();
        }

        $attributes = Mage::getResourceModel('Magento_Catalog_Model_Resource_Product_Attribute_Collection')
            ->setAttributesExcludeFilter($attributesIds)
            ->addVisibleFilter()
            ->load();

        foreach ($attributes as $child) {
            $attr = array(
                'text'              => $child->getAttributeCode(),
                'id'                => $child->getAttributeId(),
                'cls'               => 'leaf',
                'allowDrop'         => false,
                'allowDrag'         => true,
                'leaf'              => true,
                'is_user_defined'   => $child->getIsUserDefined(),
                'is_configurable'   => false,
                'entity_id'         => $child->getEntityId()
            );

            $items[] = $attr;
        }

        if (count($items) == 0) {
            $items[] = array(
                'text'      => __('Empty'),
                'id'        => 'empty',
                'cls'       => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            );
        }

        return $this->_coreData->jsonEncode($items);
    }

    /**
     * Retrieve Back Button HTML
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve Reset Button HTML
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve Save Button HTML
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve Delete Button HTML
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        if ($this->getIsCurrentSetDefault()) {
            return '';
        }
        return $this->getChildHtml('delete_button');
    }

    /**
     * Retrieve Delete Group Button HTML
     *
     * @return string
     */
    public function getDeleteGroupButton()
    {
        return $this->getChildHtml('delete_group_button');
    }

    /**
     * Retrieve Add New Group Button HTML
     *
     * @return string
     */
    public function getAddGroupButton()
    {
        return $this->getChildHtml('add_group_button');
    }

    /**
     * Retrieve Rename Button HTML
     *
     * @return string
     */
    public function getRenameButton()
    {
        return $this->getChildHtml('rename_button');
    }

    /**
     * Retrieve current Attribute Set object
     *
     * @return Magento_Eav_Model_Entity_Attribute_Set
     */
    protected function _getAttributeSet()
    {
        return $this->_coreRegistry->registry('current_attribute_set');
    }

    /**
     * Retrieve current attribute set Id
     *
     * @return int
     */
    protected function _getSetId()
    {
        return $this->_getAttributeSet()->getId();
    }

    /**
     * Check Current Attribute Set is a default
     *
     * @return bool
     */
    public function getIsCurrentSetDefault()
    {
        $isDefault = $this->getData('is_current_set_default');
        if (is_null($isDefault)) {
            $defaultSetId = Mage::getModel('Magento_Eav_Model_Entity_Type')
                ->load($this->_coreRegistry->registry('entityType'))
                ->getDefaultAttributeSetId();
            $isDefault = $this->_getSetId() == $defaultSetId;
            $this->setData('is_current_set_default', $isDefault);
        }
        return $isDefault;
    }

    /**
     * Prepare HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('adminhtml_catalog_product_attribute_set_main_html_before', array('block' => $this));
        return parent::_toHtml();
    }
}
