<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/** @var $this \Magento\Catalog\Model\Resource\Setup */

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'category_ids',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'image',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'weight',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'msrp_enabled',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Msrp\Enabled'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'msrp_display_actual_price_type',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Msrp\Price'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'available_sort_by',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\Available'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'default_sort_by',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\DefaultSortby'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'filter_price_range',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Category\Helper\Pricestep'
);
