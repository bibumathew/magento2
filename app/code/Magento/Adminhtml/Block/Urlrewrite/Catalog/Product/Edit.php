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
 * Block for Catalog Category URL rewrites editing
 *
 * @method \Magento\Catalog\Model\Category getCategory()
 * @method \Magento\Adminhtml\Block\Urlrewrite\Catalog\Product\Edit setCategory(\Magento\Catalog\Model\Category $category)
 * @method \Magento\Catalog\Model\Product getProduct()
 * @method \Magento\Adminhtml\Block\Urlrewrite\Catalog\Product\Edit setProduct(\Magento\Catalog\Model\Product $product)
 * @method bool getIsCategoryMode()
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Urlrewrite\Catalog\Product;

class Edit extends \Magento\Adminhtml\Block\Urlrewrite\Edit
{
    /**
     * Prepare layout for URL rewrite creating for product
     */
    protected function _prepareLayoutFeatures()
    {
        /** @var $helper \Magento\Adminhtml\Helper\Data */
        $helper = \Mage::helper('Magento\Adminhtml\Helper\Data');

        if ($this->_getUrlRewrite()->getId()) {
            $this->_headerText = __('Edit URL Rewrite for a Product');
        } else {
            $this->_headerText = __('Add URL Rewrite for a Product');
        }

        if ($this->_getProduct()->getId()) {
            $this->_addProductLinkBlock($this->_getProduct());
        }

        if ($this->_getCategory()->getId()) {
            $this->_addCategoryLinkBlock();
        }

        if ($this->_getProduct()->getId()) {
            if ($this->_getCategory()->getId() || !$this->getIsCategoryMode()) {
                $this->_addEditFormBlock();
                $this->_updateBackButtonLink(
                    $helper->getUrl('*/*/edit', array('product' => $this->_getProduct()->getId())) . 'category'
                );
            } else {
                // categories selector & skip categories button
                $this->_addCategoriesTreeBlock();
                $this->_addSkipCategoriesBlock();
                $this->_updateBackButtonLink($helper->getUrl('*/*/edit') . 'product');
            }
        } else {
            $this->_addUrlRewriteSelectorBlock();
            $this->_addProductsGridBlock();
        }
    }

    /**
     * Get or create new instance of product
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function _getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setProduct(\Mage::getModel('Magento\Catalog\Model\Product'));
        }
        return $this->getProduct();
    }

    /**
     * Get or create new instance of category
     *
     * @return \Magento\Catalog\Model\Category
     */
    private function _getCategory()
    {
        if (!$this->hasData('category')) {
            $this->setCategory(\Mage::getModel('Magento\Catalog\Model\Category'));
        }
        return $this->getCategory();
    }

    /**
     * Add child product link block
     */
    private function _addProductLinkBlock()
    {
        /** @var $helper \Magento\Adminhtml\Helper\Data */
        $helper = \Mage::helper('Magento\Adminhtml\Helper\Data');
        $this->addChild('product_link', 'Magento\Adminhtml\Block\Urlrewrite\Link', array(
            'item_url'  => $helper->getUrl('*/*/*') . 'product',
            'item_name' => $this->_getProduct()->getName(),
            'label'     => __('Product:')
        ));
    }

    /**
     * Add child category link block
     */
    private function _addCategoryLinkBlock()
    {
        /** @var $helper \Magento\Adminhtml\Helper\Data */
        $helper = \Mage::helper('Magento\Adminhtml\Helper\Data');
        $this->addChild('category_link', 'Magento\Adminhtml\Block\Urlrewrite\Link', array(
            'item_url'  => $helper->getUrl('*/*/*', array('product' => $this->_getProduct()->getId())) . 'category',
            'item_name' => $this->_getCategory()->getName(),
            'label'     => __('Category:')
        ));
    }

    /**
     * Add child products grid block
     */
    private function _addProductsGridBlock()
    {
        $this->addChild('products_grid', 'Magento\Adminhtml\Block\Urlrewrite\Catalog\Product\Grid');
    }

    /**
     * Add child Categories Tree block
     */
    private function _addCategoriesTreeBlock()
    {
        $this->addChild('categories_tree', 'Magento\Adminhtml\Block\Urlrewrite\Catalog\Category\Tree');
    }

    /**
     * Add child Skip Categories block
     */
    private function _addSkipCategoriesBlock()
    {
        /** @var $helper \Magento\Adminhtml\Helper\Data */
        $helper = \Mage::helper('Magento\Adminhtml\Helper\Data');
        $this->addChild('skip_categories', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label' => __('Skip Category Selection'),
            'onclick' => 'window.location = \''
                . $helper->getUrl('*/*/*', array('product' => $this->_getProduct()->getId())) . '\'',
            'class' => 'save',
            'level' => -1
        ));
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\Adminhtml\Block\Urlrewrite\Catalog\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock('Magento\Adminhtml\Block\Urlrewrite\Catalog\Edit\Form', '', array(
            'data' => array(
                'product'     => $this->_getProduct(),
                'category'    => $this->_getCategory(),
                'url_rewrite' => $this->_getUrlRewrite()
            )
        ));
    }
}
