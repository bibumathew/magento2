<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Catalog\Model\Layer\Filter;

/**
 * Layer attribute filter
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Attribute extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    const OPTIONS_ONLY_WITH_RESULTS = 1;

    /**
     * Resource instance
     *
     * @var \Magento\Catalog\Model\Resource\Layer\Filter\Attribute
     */
    protected $_resource;

    /**
     * Magento string lib
     *
     * @var \Magento\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $catalogLayer
     * @param \Magento\Catalog\Model\Resource\Layer\Filter\AttributeFactory $filterAttributeFactory
     * @param \Magento\Stdlib\String $string
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $catalogLayer,
        \Magento\Catalog\Model\Resource\Layer\Filter\AttributeFactory $filterAttributeFactory,
        \Magento\Stdlib\String $string,
        array $data = array()
    ) {
        $this->_resource = $filterAttributeFactory->create();
        $this->string = $string;
        parent::__construct($filterItemFactory, $storeManager, $catalogLayer, $data);
        $this->_requestVar = 'attribute';
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\Resource\Layer\Filter\Attribute
     */
    protected function _getResource()
    {
        return $this->_resource;
    }

    /**
     * Get option text from frontend model by option id
     *
     * @param   int $optionId
     * @return  string|bool
     */
    protected function _getOptionText($optionId)
    {
        return $this->getAttributeModel()->getFrontend()->getOption($optionId);
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   \Zend_Controller_Request_Abstract $request
     * @param   \Magento\Object $filterBlock
     * @return  \Magento\Catalog\Model\Layer\Filter\Attribute
     */
    public function apply(\Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        $text = $this->_getOptionText($filter);
        if ($filter && strlen($text)) {
            $this->_getResource()->applyFilterToCollection($this, $filter);
            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            $this->_items = array();
        }
        return $this;
    }

    /**
     * Check whether specified attribute can be used in LN
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @return bool
     */
    protected function _getIsFilterableAttribute($attribute)
    {
        return $attribute->getIsFilterable();
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $options = $attribute->getFrontend()->getSelectOptions();
        $optionsCount = $this->_getResource()->getCount($this);
        $data = array();
        foreach ($options as $option) {
            if (is_array($option['value'])) {
                continue;
            }
            if ($this->string->strlen($option['value'])) {
                // Check filter type
                if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                    if (!empty($optionsCount[$option['value']])) {
                        $data[] = array(
                            'label' => $option['label'],
                            'value' => $option['value'],
                            'count' => $optionsCount[$option['value']],
                        );
                    }
                }
                else {
                    $data[] = array(
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0,
                    );
                }
            }
        }

        return $data;
    }
}
