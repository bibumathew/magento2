<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Product attribute for `Apply MAP` enable/disable option
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

class Msrp extends \Magento\Catalog\Model\Product\Attribute\Backend\Boolean
{
    /**
     * Catalog data
     *
     * @var Magento_Catalog_Helper_Data
     */
    protected $_catalogData = null;

    /**
     * @param Magento_Catalog_Helper_Data $catalogData
     */
    public function __construct(
        Magento_Catalog_Helper_Data $catalogData
    ) {
        $this->_catalogData = $catalogData;
    }

    /**
     * Disable MAP if it's bundle with dynamic price type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function beforeSave($product)
    {
        if (!($product instanceof \Magento\Catalog\Model\Product)
            || $product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            || $product->getPriceType() != \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
        ) {
            return parent::beforeSave($product);
        }

        parent::beforeSave($product);
        $attributeCode = $this->getAttribute()->getName();
        $value = $product->getData($attributeCode);
        if (empty($value)) {
            $value = $this->_catalogData->isMsrpApplyToAll();
        }
        if ($value) {
            $product->setData($attributeCode, 0);
        }
        return $this;
    }
}
