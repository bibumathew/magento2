<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product alerts tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Victor Tihonchuk <victor@varien.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/tab/alert.phtml');
    }

    protected function _prepareLayout()
    {
        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')
            ->setId('productAlerts');
        /* @var $accordion Mage_Adminhtml_Block_Widget_Accordion */

        $alertPriceAllow = Mage::getStoreConfig('catalog/productalert/allow_price');
        $alertStockAllow = Mage::getStoreConfig('catalog/productalert/allow_stock');

        if ($alertPriceAllow) {
            $accordion->addItem('price', array(
                'title'     => Mage::helper('adminhtml')->__('Sign up for an alert when the product price changes'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_alerts_price'),
                'open'      => true
            ));
        }
        if ($alertStockAllow) {
            $accordion->addItem('stock', array(
                'title'     => Mage::helper('adminhtml')->__('Sign up to alert when product comes back in stock'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_alerts_stock'),
                'open'      => true
            ));
        }

        $this->setChild('accordion', $accordion);

        return parent::_prepareLayout();
    }

    public function getAccordionHtml()
    {
        return $this->getChildHtml('accordion');
    }
}