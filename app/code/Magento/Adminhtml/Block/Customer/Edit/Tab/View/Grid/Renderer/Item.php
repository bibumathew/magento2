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
 * Adminhtml customers wishlist grid item renderer for name/options cell
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Customer\Edit\Tab\View\Grid\Renderer;

class Item
    extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Returns helper for product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
     */
    protected function _getProductHelper($product)
    {
        // Retrieve whole array of renderers
        $productHelpers = $this->getProductHelpers();
        if (!is_array($productHelpers)) {
            $column = $this->getColumn();
            if ($column) {
                $grid = $column->getGrid();
                if ($grid) {
                    $productHelpers = $grid->getProductConfigurationHelpers();
                    $this->setProductHelpers($productHelpers ? $productHelpers : array());
                }
            }
        }

        // Check whether we have helper for our product
        $productType = $product->getTypeId();
        if (isset($productHelpers[$productType])) {
            $helperName = $productHelpers[$productType];
        } else if (isset($productHelpers['default'])) {
            $helperName = $productHelpers['default'];
        } else {
            $helperName = 'Magento\Catalog\Helper\Product\Configuration';
        }

        $helper = \Mage::helper($helperName);
        if (!($helper instanceof \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface)) {
            \Mage::throwException(__("Helper for options rendering doesn't implement required interface."));
        }

        return $helper;
    }

    /*
     * Returns product associated with this block
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Returns list of options and their values for product configuration
     *
     * @return array
     */
    protected function getOptionList()
    {
        $item = $this->getItem();
        $product = $item->getProduct();
        $helper = $this->_getProductHelper($product);
        return $helper->getOptions($item);
    }

    /**
     * Returns formatted option value for an item
     *
     * @param Magento_Wishlist_Item_Option
     * @return array
     */
    protected function getFormattedOptionValue($option)
    {
        $params = array(
            'max_length' => 55
        );
        return \Mage::helper('Magento\Catalog\Helper\Product\Configuration')->getFormattedOptionValue($option, $params);
    }

    /*
     * Renders item product name and its configuration
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return string
     */
    public function render(\Magento\Object $item)
    {
        $this->setItem($item);
        $product = $this->getProduct();
        $options = $this->getOptionList();
        return $options ? $this->_renderItemOptions($product, $options) : $this->escapeHtml($product->getName());
    }

    /**
     * Render product item with options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $options
     * @return string
     */
    protected function _renderItemOptions(\Magento\Catalog\Model\Product $product, array $options)
    {
        $html = '<div class="bundle-product-options">'
            . '<strong>' . $this->escapeHtml($product->getName()) . '</strong>'
            . '<dl>';
        foreach ($options as $option) {
            $formattedOption = $this->getFormattedOptionValue($option);
            $html .= '<dt>' . $this->escapeHtml($option['label']) . '</dt>';
            $html .= '<dd>' . $this->escapeHtml($formattedOption['value']) . '</dd>';
        }
        $html .= '</dl></div>';

        return $html;
    }
}
