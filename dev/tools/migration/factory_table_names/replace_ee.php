<?php
/**
 * {license_notice}
 *
 * @category   Magento
 * @package    Mage
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Table names association between Magento1 and Magento2 for Enterprise Edition
 * key => Magento1 table name
 * value => Magento2 table name
 */

return array(
    'array(\'magento_rma/item\', \'datetime\')' => 'magento_rma_item_entity_datetime',
    'array(\'magento_rma/item\', \'decimal\')' => 'magento_rma_item_entity_decimal',
    'array(\'magento_rma/item\', \'int\')' => 'magento_rma_item_entity_int',
    'array(\'magento_rma/item\', \'text\')' => 'magento_rma_item_entity_text',
    'array(\'magento_rma/item\', \'varchar\')' => 'magento_rma_item_entity_varchar',
    'magento_banner/banner' => 'magento_banner',
    'magento_banner/catalogrule' => 'magento_banner_catalogrule',
    'magento_banner/content' => 'magento_banner_content',
    'magento_banner/customersegment' => 'magento_banner_customersegment',
    'magento_banner/salesrule' => 'magento_banner_salesrule',
    'magento_catalogevent/event' => 'magento_catalogevent_event',
    'magento_catalogevent/event_image' => 'magento_catalogevent_event_image',
    'magento_catalogpermissions/permission' => 'magento_catalogpermissions',
    'magento_catalogpermissions/permission_index' => 'magento_catalogpermissions_index',
    'magento_catalogpermissions/permission_index_product' => 'magento_catalogpermissions_index_product',
    'enterprise_cms/hierarchy_lock' => 'enterprise_cms_hierarchy_lock',
    'enterprise_cms/hierarchy_metadata' => 'enterprise_cms_hierarchy_metadata',
    'enterprise_cms/hierarchy_node' => 'enterprise_cms_hierarchy_node',
    'enterprise_cms/increment' => 'enterprise_cms_increment',
    'enterprise_cms/page_revision' => 'enterprise_cms_page_revision',
    'enterprise_cms/page_version' => 'enterprise_cms_page_version',
    'enterprise_customer/sales_order' => 'enterprise_customer_sales_flat_order',
    'enterprise_customer/sales_order_address' => 'enterprise_customer_sales_flat_order_address',
    'enterprise_customer/sales_quote' => 'enterprise_customer_sales_flat_quote',
    'enterprise_customer/sales_quote_address' => 'enterprise_customer_sales_flat_quote_address',
    'magento_customerbalance/balance' => 'magento_customerbalance',
    'magento_customerbalance/balance_history' => 'magento_customerbalance_history',
    'magento_customersegment/customer' => 'magento_customersegment_customer',
    'magento_customersegment/event' => 'magento_customersegment_event',
    'magento_customersegment/segment' => 'magento_customersegment_segment',
    'magento_customersegment/website' => 'magento_customersegment_website',
    'magento_giftcard/amount' => 'magento_giftcard_amount',
    'magento_giftcardaccount/giftcardaccount' => 'magento_giftcardaccount',
    'magento_giftcardaccount/history' => 'magento_giftcardaccount_history',
    'magento_giftcardaccount/pool' => 'magento_giftcardaccount_pool',
    'magento_giftregistry/data' => 'magento_giftregistry_data',
    'magento_giftregistry/entity' => 'magento_giftregistry_entity',
    'magento_giftregistry/info' => 'magento_giftregistry_type_info',
    'magento_giftregistry/item' => 'magento_giftregistry_item',
    'magento_giftregistry/item_option' => 'magento_giftregistry_item_option',
    'magento_giftregistry/label' => 'magento_giftregistry_label',
    'magento_giftregistry/person' => 'magento_giftregistry_person',
    'magento_giftregistry/type' => 'magento_giftregistry_type',
    'magento_giftwrapping/attribute' => 'magento_giftwrapping_store_attributes',
    'magento_giftwrapping/website' => 'magento_giftwrapping_website',
    'magento_giftwrapping/wrapping' => 'magento_giftwrapping',
    'enterprise_importexport/scheduled_operation' => 'enterprise_scheduled_operations',
    'magento_invitation/invitation' => 'magento_invitation',
    'magento_invitation/invitation_history' => 'magento_invitation_status_history',
    'magento_invitation/invitation_track' => 'magento_invitation_track',
    'magento_logging/event' => 'magento_logging_event',
    'magento_logging/event_changes' => 'magento_logging_event_changes',
    'magento_pci/admin_passwords' => 'enterprise_admin_passwords',
    'magento_reminder/coupon' => 'magento_reminder_rule_coupon',
    'magento_reminder/log' => 'magento_reminder_rule_log',
    'magento_reminder/rule' => 'magento_reminder_rule',
    'magento_reminder/template' => 'magento_reminder_template',
    'magento_reminder/website' => 'magento_reminder_rule_website',
    'magento_reward/reward' => 'magento_reward',
    'magento_reward/reward_history' => 'magento_reward_history',
    'magento_reward/reward_rate' => 'magento_reward_rate',
    'magento_reward/reward_salesrule' => 'magento_reward_salesrule',
    'magento_rma/item_eav_attribute' => 'magento_rma_item_eav_attribute',
    'magento_rma/item_eav_attribute_website' => 'magento_rma_item_eav_attribute_website',
    'magento_rma/item_entity' => 'magento_rma_item_entity',
    'magento_rma/item_form_attribute' => 'magento_rma_item_form_attribute',
    'magento_rma/rma' => 'magento_rma',
    'magento_rma/rma_grid' => 'magento_rma_grid',
    'magento_rma/rma_shipping_label' => 'magento_rma_shipping_label',
    'magento_rma/rma_status_history' => 'magento_rma_status_history',
    'enterprise_salesarchive/creditmemo_grid' => 'enterprise_sales_creditmemo_grid_archive',
    'enterprise_salesarchive/invoice_grid' => 'enterprise_sales_invoice_grid_archive',
    'enterprise_salesarchive/order_grid' => 'enterprise_sales_order_grid_archive',
    'enterprise_salesarchive/shipment_grid' => 'enterprise_sales_shipment_grid_archive',
    'enterprise_search/recommendations' => 'catalogsearch_recommendations',
    'enterprise_targetrule/customersegment' => 'enterprise_targetrule_customersegment',
    'enterprise_targetrule/index' => 'enterprise_targetrule_index',
    'enterprise_targetrule/index_crosssell' => 'enterprise_targetrule_index_crosssell',
    'enterprise_targetrule/index_related' => 'enterprise_targetrule_index_related',
    'enterprise_targetrule/index_upsell' => 'enterprise_targetrule_index_upsell',
    'enterprise_targetrule/product' => 'enterprise_targetrule_product',
    'enterprise_targetrule/rule' => 'enterprise_targetrule',
);
