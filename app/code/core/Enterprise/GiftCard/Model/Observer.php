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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_GiftCard
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Enterprise_GiftCard_Model_Observer extends Mage_Core_Model_Abstract
{
    const ATTRIBUTE_CODE = 'giftcard_amounts';

    /**
     * Set attribute renderer on catalog product edit page
     *
     * @param Varien_Event_Observer $observer
     */
    public function setAmountsRendererInForm(Varien_Event_Observer $observer)
    {
        //adminhtml_catalog_product_edit_prepare_form

        $form = $observer->getEvent()->getForm();
        $product = $observer->getEvent()->getProduct();

        if ($elem = $form->getElement(self::ATTRIBUTE_CODE)) {
            $elem->setRenderer(
                Mage::app()->getLayout()->createBlock('enterprise_giftcard/adminhtml_renderer_amount')
            );
        }
    }


    /**
     * Set giftcard amounts field as not used in mass update
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateExcludedFieldList(Varien_Event_Observer $observer)
    {
        //adminhtml_catalog_product_form_prepare_excluded_field_list

        $block = $observer->getEvent()->getObject();
        $list = $block->getFormExcludedFieldList();
        $list[] = self::ATTRIBUTE_CODE;
        $block->setFormExcludedFieldList($list);
    }

    /**
     * Append gift card additional data to order item options
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftCard_Model_Observer
     */
    public function appendGiftcardAdditionalData(Varien_Event_Observer $observer)
    {
        //sales_convert_quote_item_to_order_item

        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $keys = array(
            'giftcard_sender_name',
            'giftcard_sender_email',
            'giftcard_recipient_name',
            'giftcard_recipient_email',
            'giftcard_message',
        );
        $productOptions = $orderItem->getProductOptions();
        foreach ($keys as $key) {
            if ($option = $quoteItem->getProduct()->getCustomOption($key)) {
                $productOptions[$key] = $option->getValue();
            }
        }

        $product = $quoteItem->getProduct();
        // set lifetime
        $lifetime = 0;
        if ($product->getUseConfigLifetime()) {
            $lifetime = Mage::getStoreConfig(Enterprise_GiftCard_Model_Giftcard::XML_PATH_LIFETIME, $orderItem->getStore());
        } else {
            $lifetime = $product->getLifetime();
        }
        $productOptions['giftcard_lifetime'] = $lifetime;

        // set is_redeemable
        $isRedeemable = 0;
        if ($product->getUseConfigIsRedeemable()) {
            $isRedeemable = Mage::getStoreConfigFlag(Enterprise_GiftCard_Model_Giftcard::XML_PATH_IS_REDEEMABLE, $orderItem->getStore());
        } else {
            $isRedeemable = (int) $product->getIsRedeemable();
        }
        $productOptions['giftcard_is_redeemable'] = $isRedeemable;

        // set email_template
        $emailTemplate = 0;
        if ($product->getUseConfigEmailTemplate()) {
            $emailTemplate = Mage::getStoreConfig(Enterprise_GiftCard_Model_Giftcard::XML_PATH_EMAIL_TEMPLATE, $orderItem->getStore());
        } else {
            $emailTemplate = $product->getEmailTemplate();
        }
        $productOptions['giftcard_email_template'] = $emailTemplate;
        $productOptions['giftcard_type'] = $product->getGiftcardType();

        $orderItem->setProductOptions($productOptions);

        return $this;
    }

    /**
     * Generate gift card accounts after order save
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_GiftCard_Model_Observer
     */
    public function generateGiftCardAccounts(Varien_Event_Observer $observer)
    {
        // sales_order_save_after

        $order = $observer->getEvent()->getOrder();
        $requiredStatus = Mage::getStoreConfig(Enterprise_GiftCard_Model_Giftcard::XML_PATH_ORDER_ITEM_STATUS, $order->getStore());

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Enterprise_GiftCard_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD) {
                if ($item->getStatusId() == $requiredStatus) {
                    $qty = 0;
                    switch ($requiredStatus) {
                        case Mage_Sales_Model_Order_Item::STATUS_INVOICED:
                            $qty = $item->getQtyInvoiced();
                            break;
                        default:
                            $qty = $item->getQtyOrdered();
                            break;
                    }

                    $isRedeemable = 0;
                    if ($option = $item->getProductOptionByCode('giftcard_is_redeemable')) {
                        $isRedeemable = $option;
                    }

                    $lifetime = 0;
                    if ($option = $item->getProductOptionByCode('giftcard_lifetime')) {
                        $lifetime = $option;
                    }

                    $amount = $item->getBasePrice();
                    $websiteId = Mage::app()->getStore($order->getStoreId())->getWebsiteId();

                    $data = new Varien_Object();
                    $data->setWebsiteId($websiteId)
                        ->setAmount($amount)
                        ->setLifetime($lifetime)
                        ->setIsRedeemable($isRedeemable)
                        ->setOrderItem($item);

                    $options = $item->getProductOptions();
                    if (isset($options['giftcard_created_codes'])) {
                        $qty -= count($options['giftcard_created_codes']);
                    }

                    $codes = (isset($options['giftcard_created_codes']) ? $options['giftcard_created_codes'] : array());
                    $goodCodes = 0;
                    for ($i = 0; $i < $qty; $i++) {
                        try {
                            $code = new Varien_Object();
                            Mage::dispatchEvent('enterprise_giftcardaccount_create', array('request'=>$data, 'code'=>$code));
                            $codes[] = $code->getCode();
                            $goodCodes++;
                        } catch (Mage_Core_Exception $e) {
                            $codes[] = null;
                        }
                    }
                    if ($goodCodes && $item->getProductOptionByCode('giftcard_recipient_email')) {
                        $sender = $item->getProductOptionByCode('giftcard_sender_name');
                        if ($senderEmail = $item->getProductOptionByCode('giftcard_sender_email')) {
                            $sender = "$sender <$senderEmail>";
                        }
                        $codeList = array();
                        $i=0;
                        foreach ($codes as $code) {
                            if ($code !== null) {
                                $i++;
                                $redeemLabel = Mage::helper('enterprise_giftcard')->__('Redeem');
                                $redeemUrl = Mage::getUrl('enterprise_customerbalance/info/', array('giftcard'=>$code));
                                $codeList[] = sprintf('#%d <strong>%s</strong> <a href="%s">%s</a>', $i, $code, $redeemUrl, $redeemLabel);
                            }
                        }
                        $codeList = implode('<br />', $codeList);
                        $templateData = array(
                            'name'                   => htmlspecialchars($item->getProductOptionByCode('giftcard_recipient_name')),
                            'email'                  => htmlspecialchars($item->getProductOptionByCode('giftcard_recipient_email')),
                            'sender_name_with_email' => htmlspecialchars($sender),
                            'gift_message'           => nl2br(htmlspecialchars($item->getProductOptionByCode('giftcard_message'))),
                            'giftcards'              => $codeList,
                        );

                        $email = Mage::getModel('core/email_template')->setDesignConfig(array('store' => $item->getOrder()->getStoreId()));
                        $email->sendTransactional(
//                            Mage::getStoreConfig('giftcard/general/template', $item->getOrder()->getStoreId()),
                            $item->getProductOptionByCode('giftcard_email_template'),
                            Mage::getStoreConfig('giftcard/general/identity', $item->getOrder()->getStoreId()),
                            $item->getProductOptionByCode('giftcard_recipient_email'),
                            $item->getProductOptionByCode('giftcard_recipient_name'),
                            $templateData
                        );

                        if ($email->getSentSuccess()) {
                            $options['email_sent'] = 1;
                        }
                    }
                    $options['giftcard_created_codes'] = $codes;
                    $item->setProductOptions($options);
                    $item->save();
                }
            }
        }


        return $this;
    }
}