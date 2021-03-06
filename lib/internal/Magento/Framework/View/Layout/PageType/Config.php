<?php
/**
 * Page layout config model
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Layout\PageType;

class Config
{
    /**
     * Available page types
     *
     * @var array
     */
    protected $_pageTypes = null;

    /**
     * Data storage
     *
     * @var  \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Initialize page types list
     *
     * @return $this
     */
    protected function _initPageTypes()
    {
        if ($this->_pageTypes === null) {
            $this->_pageTypes = [];
            foreach ($this->_dataStorage->get(null) as $pageTypeId => $pageTypeConfig) {
                $pageTypeConfig['label'] = __($pageTypeConfig['label']);
                $this->_pageTypes[$pageTypeId] = new \Magento\Framework\Object($pageTypeConfig);
            }
        }
        return $this;
    }

    /**
     * Retrieve available page types
     *
     * @return \Magento\Framework\Object[]
     */
    public function getPageTypes()
    {
        $this->_initPageTypes();
        return $this->_pageTypes;
    }
}
