<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Customer order totals xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Customer_Order_Totals extends Mage_Sales_Block_Order_Totals
{
    /**
     * Add order totals rendered to XML object
     * (get from template: order/totals.phtml)
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $orderXmlObj
     * @return null
     */
    public function addTotalsToXmlObject(Mage_XmlConnect_Model_Simplexml_Element $orderXmlObj)
    {
        // all Enterprise renderers from layout update into array an realize checking of their using
        $enterpriseBlocks = array(
            'reward.sales.order.total'  => array(
                'module'    => 'Enterprise_Reward',
                'block'     => 'Enterprise_Reward_Block_Sales_Order_Total'
            ),
            'customerbalance'  => array(
                'module'    => 'Enterprise_CustomerBalance',
                'block'     => 'Mage_XmlConnect_Block_Customer_Order_Totals_Customerbalance',
                'template'  => 'order/customerbalance.phtml'
            ),
            'customerbalance_total_refunded'  => array(
                'module'    => 'Enterprise_CustomerBalance',
                'block'     => 'Mage_XmlConnect_Block_Customer_Order_Totals_Customerbalance_Refunded',
                'template'  => 'order/customerbalance_refunded.phtml',
                'after'     => '-',
                'action'    => array(
                    'method'    => 'setAfterTotal',
                    'value'     => 'grand_total'
                )
            ),
            'giftwrapping'  => array(
                'module'    => 'Enterprise_GiftWrapping',
                'block'     => 'Enterprise_GiftWrapping_Block_Sales_Totals'
            ),
            'giftcards'  => array(
                'module'    => 'Enterprise_GiftCardAccount',
                'block'     => 'Mage_XmlConnect_Block_Customer_Order_Totals_Giftcards',
                'template'  => 'order/giftcards.phtml'
            ),
        );

        foreach ($enterpriseBlocks as $name => $block) {
            // create blocks only for Enterprise/Pro modules which is in system
            if (is_object(Mage::getConfig()->getNode('modules/' . $block['module']))) {
                $blockInstance = $this->getLayout()->createBlock($block['block'], $name);
                $this->setChild($name, $blockInstance);
                if (isset($block['action']['method']) && isset($block['action']['value'])) {
                    $method = $block['action']['method'];
                    $blockInstance->$method($block['action']['value']);
                }
            }
        }

        $this->_beforeToHtml();

        $totalsXml = $orderXmlObj->addChild('totals');
        foreach ($this->getTotals() as $total) {
            if ($total->getValue()) {
                $total->setValue(strip_tags($total->getValue()));
            }
            if ($total->getBlockName()) {
                $block = $this->getLayout()->getBlock($total->getBlockName());
                if (is_object($block)) {
                    if (method_exists($block, 'addToXmlObject')) {
                        $block->setTotal($total)->addToXmlObject($totalsXml);
                    } else {
                        $this->_addTotalToXml($total, $totalsXml);
                    }
                }
            } else {
                $this->_addTotalToXml($total, $totalsXml);
            }
        }
    }

    /**
     * Add total to totals XML
     *
     * @param Varien_Object $total
     * @param Mage_XmlConnect_Model_Simplexml_Element $totalsXml
     * @return null
     */
    private function _addTotalToXml($total, Mage_XmlConnect_Model_Simplexml_Element $totalsXml)
    {
        if ($total instanceof Varien_Object && $total->getCode() && $total->getLabel() && $total->hasData('value')) {
            $totalsXml->addCustomChild(preg_replace('@[\W]+@', '_', trim($total->getCode())),
                $this->_formatPrice($total), array('label' => strip_tags($total->getLabel()))
            );
        }
    }

    /**
     * Format price using order currency
     *
     * @param   Varien_Object $total
     * @return  string
     */
    protected function _formatPrice($total)
    {
        if (!$total->getIsFormated()) {
            return Mage::helper('Mage_XmlConnect_Helper_Customer_Order')->formatPrice($this, $total->getValue());
        }
        return $total->getValue();
    }
}
