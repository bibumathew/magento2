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
 * Product Stores tab
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

class Websites extends \Magento\Backend\Block\Store\Switcher
{
    protected $_storeFromHtml;

    protected $_template = 'catalog/product/edit/websites.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Website\Factory $websiteFactory
     * @param \Magento\Core\Model\Store\Group\Factory $storeGroupFactory
     * @param \Magento\Core\Model\StoreFactory $storeFactory
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Website\Factory $websiteFactory,
        \Magento\Core\Model\Store\Group\Factory $storeGroupFactory,
        \Magento\Core\Model\StoreFactory $storeFactory,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct(
            $coreData, $context, $websiteFactory, $storeGroupFactory, $storeFactory, $data
        );
    }

    /**
     * Retrieve edited product model instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get store ID of current product
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getProduct()->getStoreId();
    }

    /**
     * Get ID of current product
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Retrieve array of website IDs of current product
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->getProduct()->getWebsiteIds();
    }

    /**
     * Returns whether product associated with website with $websiteId
     *
     * @param int $websiteId
     * @return bool
     */
    public function hasWebsite($websiteId)
    {
        return in_array($websiteId, $this->getProduct()->getWebsiteIds());
    }

    /**
     * Check websites block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getWebsitesReadonly();
    }

    /**
     * Retrieve store name by its ID
     *
     * @param int $storeId
     * @return null|string
     */
    public function getStoreName($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getName();
    }

    /**
     * Get HTML of store chooser
     *
     * @param \Magento\Core\Model\Store $storeTo
     * @return string
     */
    public function getChooseFromStoreHtml($storeTo)
    {
        if (!$this->_storeFromHtml) {
            $this->_storeFromHtml = '<select name="copy_to_stores[__store_identifier__]" disabled="disabled">';
            $this->_storeFromHtml.= '<option value="0">'.__('Default Values').'</option>';
            foreach ($this->getWebsiteCollection() as $_website) {
                if (!$this->hasWebsite($_website->getId())) {
                    continue;
                }
                $optGroupLabel = $this->escapeHtml($_website->getName());
                $this->_storeFromHtml .= '<optgroup label="' . $optGroupLabel . '"></optgroup>';
                foreach ($this->getGroupCollection($_website) as $_group) {
                    $optGroupName = $this->escapeHtml($_group->getName());
                    $this->_storeFromHtml .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $optGroupName . '">';
                    foreach ($this->getStoreCollection($_group) as $_store) {
                        $this->_storeFromHtml .= '<option value="' . $_store->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;';
                        $this->_storeFromHtml .= $this->escapeHtml($_store->getName()) . '</option>';
                    }
                }
                $this->_storeFromHtml .= '</optgroup>';
            }
            $this->_storeFromHtml .= '</select>';
        }
        return str_replace('__store_identifier__', $storeTo->getId(), $this->_storeFromHtml);
    }
}
