<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Attributes Model
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleShopping\Model;

class Attribute extends \Magento\Core\Model\AbstractModel
{
    /**
     * Default ignored attribute codes
     *
     * @var array
     */
    protected $_ignoredAttributeCodes = array(
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'gift_message_available',
        'giftcard_amounts',
        'news_from_date',
        'news_to_date',
        'options_container',
        'price_view',
        'sku_type',
        'use_config_is_redeemable',
        'use_config_allow_message',
        'use_config_lifetime',
        'use_config_email_template',
        'tier_price',
        'minimal_price',
        'recurring_profile',
        'shipment_type'
    );

    /**
     * Default ignored attribute types
     *
     * @var array
     */
    protected $_ignoredAttributeTypes = array('hidden', 'media_image', 'image', 'gallery');

    protected function _construct()
    {
        $this->_init('Magento\GoogleShopping\Model\Resource\Attribute');
    }

    /**
     * Get array with allowed product attributes (for mapping) by selected attribute set
     *
     * @param int $setId attribute set id
     * @return array
     */
    public function getAllowedAttributes($setId)
    {
        $attributes = \Mage::getModel('Magento\Catalog\Model\Product')->getResource()
                ->loadAllAttributes()
                ->getSortedAttributes($setId);

        $titles = array();
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            if ($attribute->isInSet($setId) && $this->_isAllowedAttribute($attribute)) {
                $list[$attribute->getAttributeId()] = $attribute;
                $titles[$attribute->getAttributeId()] = $attribute->getFrontendLabel();
            }
        }
        asort($titles);
        $result = array();
        foreach ($titles as $attributeId => $label) {
            $result[$attributeId] = $list[$attributeId];
        }
        return $result;
    }

    /**
     * Check if attribute allowed
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param array $attributes
     * @return boolean
     */
    protected function _isAllowedAttribute($attribute)
    {
        return !in_array($attribute->getFrontendInput(), $this->_ignoredAttributeTypes)
               && !in_array($attribute->getAttributeCode(), $this->_ignoredAttributeCodes)
               && $attribute->getFrontendLabel() != "";
    }
}
