<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Reward rate grid
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Block_Adminhtml_Reward_Rate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rewardRatesGrid');
    }

    /**
     * Prepare grid collection object
     *
     * @return Enterprise_Reward_Block_Adminhtml_Reward_Rate_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Enterprise_Reward_Model_Resource_Reward_Rate_Collection */
        $collection = Mage::getModel('Enterprise_Reward_Model_Reward_Rate')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_Reward_Block_Adminhtml_Reward_Rate_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rate_id', array(
            'header' => Mage::helper('Enterprise_Reward_Helper_Data')->__('ID'),
            'align'  => 'left',
            'index'  => 'rate_id',
            'width'  => 1,
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'  => Mage::helper('Enterprise_Reward_Helper_Data')->__('Website'),
                'index'   => 'website_id',
                'type'    => 'options',
                'options' => Mage::getModel('Enterprise_Reward_Model_Source_Website')->toOptionArray()
            ));
        }

        $this->addColumn('customer_group_id', array(
            'header'  => Mage::helper('Enterprise_Reward_Helper_Data')->__('Customer Group'),
            'index'   => 'customer_group_id',
            'type'    => 'options',
            'options' => Mage::getModel('Enterprise_Reward_Model_Source_Customer_Groups')->toOptionArray()
        ));

        $this->addColumn('rate', array(
            'getter'   => array($this, 'getRateText'),
            'header'   => Mage::helper('Enterprise_Reward_Helper_Data')->__('Rate'),
            'filter'   => false,
            'sortable' => false,
            'html_decorators' => 'nobr',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('rate_id' => $row->getId()));
    }

    /**
     * Rate text getter
     *
     * @param Varien_Object $row
     * @return string|null
     */
    public function getRateText($row)
    {
        $websiteId = $row->getWebsiteId();
        return Enterprise_Reward_Model_Reward_Rate::getRateText($row->getDirection(), $row->getPoints(),
            $row->getCurrencyAmount(),
            0 == $websiteId ? null : Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode()
        );
    }
}