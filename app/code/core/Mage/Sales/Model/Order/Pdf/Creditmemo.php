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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment method abstract model
 *
 * @author Yuriy Scherbina <yuriy.scherbina@varien.com>
 */
class Mage_Sales_Model_Order_Pdf_Creditmemo extends Mage_Sales_Model_Order_Pdf_Abstract
{
    public function getPdf($creditmemos = array())
    {
        $pdf = new Zend_Pdf();
        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        foreach ($creditmemos as $creditmemo) {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

             $order = $creditmemo->getOrder();

            /* Add image */
            $this->insertLogo($page);

            /* Add address */
            $this->insertAddress($page);

            /* Add head */
            $this->insertOrder($page, $order);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 7);
            $page->drawText(Mage::helper('sales')->__('Credit Memo # ') . $creditmemo->getIncrementId(), 35, 780);

            /* Add table head */
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $page->drawText(Mage::helper('sales')->__('QTY'), 35, $this->y);
            $page->drawText(Mage::helper('sales')->__('Products'), 60, $this->y);
            $page->drawText(Mage::helper('sales')->__('SKU'), 280, $this->y);
            $page->drawText(Mage::helper('sales')->__('Tax'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Discount'), 430, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Total(ex)'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Total(inc)'), 530, $this->y, 'UTF-8');

            $this->y -=15;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
            foreach ($creditmemo->getAllItems() as $item){
                $shift = 10;
                if ($this->y<20) {
                    /* Add new table head */
                    $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                    $pdf->pages[] = $page;
                    $this->y = 800;

                    $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 7);
                    $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
                    $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
                    $page->setLineWidth(0.5);
                    $page->drawRectangle(25, $this->y, 570, $this->y-15);
                    $this->y -=10;

                    $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
                    $page->drawText(Mage::helper('sales')->__('QTY'), 35, $this->y);
                    $page->drawText(Mage::helper('sales')->__('Products'), 60, $this->y);
                    $page->drawText(Mage::helper('sales')->__('SKU'), 280, $this->y);
                    $page->drawText(Mage::helper('sales')->__('Tax'), 380, $this->y, 'UTF-8');
                    $page->drawText(Mage::helper('sales')->__('Discount'), 430, $this->y, 'UTF-8');
                    $page->drawText(Mage::helper('sales')->__('Total(ex)'), 480, $this->y, 'UTF-8');
                    $page->drawText(Mage::helper('sales')->__('Total(inc)'), 530, $this->y, 'UTF-8');

                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $this->y -=20;
                }

                /* Add products */
                $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 7);
                $page->drawText($item->getQty()*1, 35, $this->y);
                $page->drawText($item->getName(), 60, $this->y);
                $page->drawText($item->getName(), 60, $this->y);
                foreach (explode('</li>', $item->getDescription()) as $description){
                    $page->drawText(strip_tags($description), 65, $this->y-$shift);
                    $shift += 10;
                }

                $page->drawText($item->getSku(), 280, $this->y);

                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $page->setFont($font, 7);
                $page->drawText($order->formatPriceTxt($item->getTaxAmount()), 380, $this->y, 'UTF-8');
                $page->drawText($order->formatPriceTxt(-$item->getDiscountAmount()), 430, $this->y, 'UTF-8');
                $page->drawText($order->formatPriceTxt($item->getRowTotal()), 480, $this->y, 'UTF-8');

                $row_total = $order->formatPriceTxt($item->getRowTotal()+$item->getTaxAmount()-$item->getDiscountAmount());

                $page->drawText($row_total, 565-$this->widthForStringUsingFontSize($row_total, $font, 7), $this->y, 'UTF-8');
                $this->y -=$shift;
            }

            /* Add totals */
            $this->insertTotals($page, $creditmemo);
        }
        return $pdf;
    }

}