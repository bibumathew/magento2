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
 * admin product edit tabs
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Catalog\Product\Edit;

class Tabs extends \Magento\Adminhtml\Block\Widget\Tabs
{
    const BASIC_TAB_GROUP_CODE = 'basic';
    const ADVANCED_TAB_GROUP_CODE = 'advanced';

    /** @var string */
    protected $_attributeTabBlock = 'Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Attributes';

    /** @var string */
    protected $_template = 'Magento_Catalog::product/edit/tabs.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_info_tabs');
        $this->setDestElementId('product-edit-form-tabs');
    }

    protected function _prepareLayout()
    {
        $product = $this->getProduct();

        if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        if ($setId) {
            $groupCollection = \Mage::getResourceModel('Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection')
                ->setAttributeSetFilter($setId)
                ->setSortOrder()
                ->load();

            $tabAttributesBlock = $this->getLayout()->createBlock(
                $this->getAttributeTabBlock(), $this->getNameInLayout() . '_attributes_tab'
            );
            $advancedGroups = array();
            foreach ($groupCollection as $group) {
                /** @var $group \Magento\Eav\Model\Entity\Attribute\Group*/
                $attributes = $product->getAttributes($group->getId(), true);

                foreach ($attributes as $key => $attribute) {
                    $applyTo = $attribute->getApplyTo();
                    if (!$attribute->getIsVisible()
                        || (!empty($applyTo) && !in_array($product->getTypeId(), $applyTo))
                    ) {
                        unset($attributes[$key]);
                    }
                }

                if ($attributes) {
                    $tabData = array(
                        'label'   => __($group->getAttributeGroupName()),
                        'content' => $this->_translateHtml(
                            $tabAttributesBlock->setGroup($group)
                                ->setGroupAttributes($attributes)
                                ->toHtml()
                        ),
                        'class' => 'user-defined',
                        'group_code' => $group->getTabGroupCode() ?: self::BASIC_TAB_GROUP_CODE
                    );

                    if ($group->getAttributeGroupCode() === 'recurring-profile') {
                        $tabData['parent_tab'] = 'advanced-pricing';
                    }

                    if ($tabData['group_code'] === self::BASIC_TAB_GROUP_CODE) {
                        $this->addTab($group->getAttributeGroupCode(), $tabData);
                    } else {
                        $advancedGroups[$group->getAttributeGroupCode()] = $tabData;
                    }
                }
            }

            /* Don't display website tab for single mode */
            if (!\Mage::app()->isSingleStoreMode()) {
                $this->addTab('websites', array(
                    'label'     => __('Websites'),
                    'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Websites')->toHtml()),
                    'group_code' => self::BASIC_TAB_GROUP_CODE,
                ));
            }

            if (isset($advancedGroups['advanced-pricing'])) {
                $this->addTab('advanced-pricing', $advancedGroups['advanced-pricing']);
                unset($advancedGroups['advanced-pricing']);
            }

            if (\Mage::helper('Magento\Core\Helper\Data')->isModuleEnabled('Magento_CatalogInventory')) {
                $this->addTab('advanced-inventory', array(
                    'label'     => __('Advanced Inventory'),
                    'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Inventory')->toHtml()),
                    'group_code' => self::ADVANCED_TAB_GROUP_CODE,
                ));
            }

            /**
             * Do not change this tab id
             * @see \Magento\Adminhtml\Block\Catalog\Product\Edit\Tabs\Configurable
             * @see \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tabs
             */
            if (!$product->isGrouped()) {
                $this->addTab('customer_options', array(
                    'label' => __('Custom Options'),
                    'url'   => $this->getUrl('*/*/options', array('_current' => true)),
                    'class' => 'ajax',
                    'group_code' => self::ADVANCED_TAB_GROUP_CODE,
                ));
            }

            $this->addTab('related', array(
                'label'     => __('Related Products'),
                'url'       => $this->getUrl('*/*/related', array('_current' => true)),
                'class'     => 'ajax',
                'group_code' => self::ADVANCED_TAB_GROUP_CODE,
            ));

            $this->addTab('upsell', array(
                'label'     => __('Up-sells'),
                'url'       => $this->getUrl('*/*/upsell', array('_current' => true)),
                'class'     => 'ajax',
                'group_code' => self::ADVANCED_TAB_GROUP_CODE,
            ));

            $this->addTab('crosssell', array(
                'label'     => __('Cross-sells'),
                'url'       => $this->getUrl('*/*/crosssell', array('_current' => true)),
                'class'     => 'ajax',
                'group_code' => self::ADVANCED_TAB_GROUP_CODE,
            ));

            if (isset($advancedGroups['design'])) {
                $this->addTab('design', $advancedGroups['design']);
                unset($advancedGroups['design']);
            }

            $alertPriceAllow = \Mage::getStoreConfig('catalog/productalert/allow_price');
            $alertStockAllow = \Mage::getStoreConfig('catalog/productalert/allow_stock');
            if (($alertPriceAllow || $alertStockAllow) && !$product->isGrouped()) {
                $this->addTab('product-alerts', array(
                    'label'     => __('Product Alerts'),
                    'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Alerts', 'admin.alerts.products')
                        ->toHtml()
                    ),
                    'group_code' => self::ADVANCED_TAB_GROUP_CODE,
                ));
            }

            if ($this->getRequest()->getParam('id')) {
                if (\Mage::helper('Magento\Catalog\Helper\Data')->isModuleEnabled('Magento_Review')) {
                    if ($this->_authorization->isAllowed('Magento_Review::reviews_all')){
                        $this->addTab('product-reviews', array(
                            'label' => __('Product Reviews'),
                            'url'   => $this->getUrl('*/*/reviews', array('_current' => true)),
                            'class' => 'ajax',
                            'group_code' => self::ADVANCED_TAB_GROUP_CODE,
                        ));
                    }
                }
            }

            if (isset($advancedGroups['autosettings'])) {
                $this->addTab('autosettings', $advancedGroups['autosettings']);
                unset($advancedGroups['autosettings']);
            }

            foreach ($advancedGroups as $groupCode => $group) {
                $this->addTab($groupCode, $group);
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Check whether active tab belong to advanced group
     *
     * @return bool
     */
    public function isAdvancedTabGroupActive()
    {
        return $this->_tabs[$this->_activeTab]->getGroupCode() == self::ADVANCED_TAB_GROUP_CODE;
    }

    /**
     * Retrieve product object from object if not from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!($this->getData('product') instanceof \Magento\Catalog\Model\Product)) {
            $this->setData('product', \Mage::registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        if (is_null(\Mage::helper('Magento\Adminhtml\Helper\Catalog')->getAttributeTabBlock())) {
            return $this->_attributeTabBlock;
        }
        return \Mage::helper('Magento\Adminhtml\Helper\Catalog')->getAttributeTabBlock();
    }

    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translator->processResponseBody($html);
        return $html;
    }
}
