<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Tag
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml pending tags grid block
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Block_Adminhtml_Pending extends Mage_Adminhtml_Block_Template
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tag/index.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('tagsGrid', $this->getLayout()->createBlock('Mage_Tag_Block_Adminhtml_Grid_Pending'));
        return parent::_prepareLayout();
    }

    public function getCreateButtonHtml()
    {
        return '';
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('tagsGrid');
    }

    public function getHeaderHtml()
    {
        return Mage::helper('Mage_Tag_Helper_Data')->__('Pending Tags');
    }
}