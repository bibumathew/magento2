<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_AdvancedCheckout
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_AdvancedCheckout_Block_Sku_ProductsTest extends PHPUnit_Framework_TestCase
{
    public function testToHtml()
    {
        $block = Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento_Core_Model_Layout')
            ->createBlock('Magento_AdvancedCheckout_Block_Sku_Products')
            ->setTemplate('cart/sku/failed.phtml');
        $this->assertEmpty($block->toHtml());

        $item = array(
            'sku' => 'test',
            'code' => Magento_AdvancedCheckout_Helper_Data::ADD_ITEM_STATUS_FAILED_SKU,
        );
        Magento_TestFramework_Helper_Bootstrap::getObjectManager()->get('Magento_AdvancedCheckout_Helper_Data')
            ->getSession()->setAffectedItems(array(
                Magento_TestFramework_Helper_Bootstrap::getObjectManager()
                    ->get('Magento_Core_Model_StoreManagerInterface')->getStore()->getId() => array($item)
            ));
        $this->assertContains('<form', $block->toHtml());
    }
}
