<?php

class Mage_Sales_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        return array(
            'quote'=>array(
                'table'=>'sales/quote',
                'increment_model'=>'eav/entity_increment_alphanum',
                'increment_per_store'=>true,
                'attributes' => array(
                    'entity_id' => array('type'=>'static', 'backend'=>'sales_entity/quote_attribute_backend_parent'),
                    'customer_id' => array('type'=>'int', 'visible'=>false),
                    'remote_ip' => array('visible'=>false),
                    'checkout_method' => array(),
                    'password_hash' => array(),
                    'quote_status_id' => array('label'=>'Quote Status', 'type'=>'int', 'source'=>'sales_entity/quote_attribute_source_status'),
                    'billing_address_id' => array('type'=>'int', 'visible'=>false),
                    'converted_at' => array('type'=>'datetime', 'visible'=>false),
                    'coupon_code' => array('label'=>'Coupon'),
                    'giftcert_code' => array('label'=>'Gift certificate'),
                    'custbalance_amount' => array('type'=>'decimal'),
                    'base_currency_code' => array('label'=>'Base currency'),
                    'store_currency_code' => array('label'=>'Store currency'),
                    'quote_currency_code' => array('label'=>'Quote currency'),
                    'store_to_base_rate' => array('type'=>'decimal', 'label'=>'Store to Base rate'),
                    'store_to_quote_rate' => array('type'=>'decimal', 'label'=>'Store to Quote rate'),
                    'grand_total' => array('type'=>'decimal'),
                    'orig_order_id' => array('label'=>'Original order ID'),
                    'applied_rule_ids' => array('type'=>'text', 'visible'=>false),
                    'is_virtual' => array('type'=>'int', 'visible'=>false),
                    'is_multi_shipping' => array('type'=>'int', 'visible'=>false),
                    'is_multi_payment' => array('type'=>'int', 'visible'=>false),
                ),
            ), 
            'quote_address' => array(
                'table'=>'sales/quote',
                'backend_prefix'=>'sales_entity/quote_address_attribute_backend',
                'frontend_prefix'=>'sales_entity/quote_address_attribute_frontend',
                'attributes' => array(
                    'entity_id' => array('type'=>'static', 'backend'=>'sales_entity/quote_address_attribute_backend_parent'),
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/quote_attribute_backend_child'),
                    'address_type' => array(),
                    'customer_id' => array('type'=>'int'),
                    'customer_address_id' => array('type'=>'int'),
                    'email' => array(),
                    'firstname' => array(),
                    'lastname' => array(),
                    'company' => array(),
                    'street' => array(),
                    'city' => array(),
                    'region' => array(),
                    'region_id' => array('type'=>'int'),
                    'postcode' => array(),
                    'country_id' => array('type'=>'int'),
                    'telephone' => array(),
                    'fax' => array(),
                    'same_as_billing' => array('type'=>'int'),
                    'weight' => array('type'=>'decimal'),
                    'shipping_method' => array(),
                    'shipping_description' => array('type'=>'text'),
                    'subtotal' => array('type'=>'decimal', 'backend'=>'_subtotal', 'frontend'=>'_subtotal'),
                    'tax_amount' => array('type'=>'decimal', 'backend'=>'_tax', 'frontend'=>'_tax'),
                    'shipping_amount' => array('type'=>'decimal', 'backend'=>'_shipping', 'frontend'=>'_shipping'),
                    'discount_amount' => array('type'=>'decimal', 'backend'=>'_discount', 'frontend'=>'_discount'),
                    #'giftcert_amount' => array('type'=>'decimal', 'backend'=>'giftcert/entity_quote_address_attribute_backend_giftcert', 'frontend'=>'giftcert/entity_quote_address_attribute_frontend_giftcert'),
                    'custbalance_amount' => array('type'=>'decimal', 'backend'=>'_custbalance', 'frontend'=>'_custbalance'),
                    'grand_total' => array('type'=>'decimal', 'frontend'=>'_grand'),
                    'customer_notes' => array('type'=>'text'),
                ),
            ),             
            'quote_address_rate' => array(
                'table'=>'sales/quote_temp',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/quote_address_attribute_backend_child'),
                    'code' => array(),
                    'carrier' => array(),
                    'carrier_title' => array(),
                    'method' => array(),
                    'method_description' => array('type'=>'text'),
                    'price' => array('type'=>'decimal'),
                    'error_message' => array('type'=>'text'),
                ),
            ),
            'quote_address_item' => array(
                'table'=>'sales/quote_temp',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/quote_address_attribute_backend_child'),
                    'quote_item_id' => array('type'=>'int'),
                    'qty' => array('type'=>'decimal'),
                    'discount_percent' => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'tax_percent' => array('type'=>'decimal'),
                    'tax_amount' => array('type'=>'decimal'),
                    'row_total' => array('type'=>'decimal'),
                    'row_weight' => array('type'=>'decimal'),
                ),
            ),
            'quote_item' => array(
                'table'=>'sales/quote',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/quote_attribute_backend_child'),
                    'product_id' => array('type'=>'int'),
                    'sku' => array(),
                    'image' => array(),
                    'name' => array(),
                    'description' => array('type'=>'text'),
                    'weight' => array('type'=>'decimal'),
                    'qty' => array('type'=>'decimal'),
                    'price' => array('type'=>'decimal'),
                    'discount_percent' => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'tax_percent' => array('type'=>'decimal'),
                    'tax_amount' => array('type'=>'decimal'),
                    'row_total' => array('type'=>'decimal'),
                    'row_weight' => array('type'=>'decimal'),
                ),
            ),
            'quote_payment' => array(
                'table'=>'sales/quote',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/quote_attribute_backend_child'),
                    'customer_payment_id' => array('type'=>'int'),
                    'method' => array(),
                    'po_number' => array(),
                    'cc_type' => array(),
                    'cc_number_enc' => array(),
                    'cc_last4' => array(),
                    'cc_owner' => array(),
                    'cc_exp_month' => array('type'=>'int'),
                    'cc_exp_year' => array('type'=>'int'),
                ),
            ),
            
            'order' => array(
                'table'=>'sales/order',
                'increment_model'=>'eav/entity_increment_numeric',
                'increment_per_store'=>true,
                'attributes' => array(
                    'entity_id' => array('type'=>'static', 'backend'=>'sales_entity/order_attribute_backend_parent'),
                    'customer_id' => array('type'=>'int'),
                    'remote_ip' => array(),
                    'order_status_id' => array('type'=>'int'),
                    'quote_id' => array('type'=>'int'),
                    'quote_address_id' => array('type'=>'int'),
                    'billing_address_id' => array('type'=>'int'),
                    'shipping_address_id' => array('type'=>'int'),
                    'coupon_code' => array(),
                    'giftcert_code' => array(),
                    'base_currency_code' => array(),
                    'store_currency_code' => array(),
                    'order_currency_code' => array(),
                    'store_to_base_rate' => array('type'=>'decimal'),
                    'store_to_order_rate' => array('type'=>'decimal'),
                    'is_virtual' => array('type'=>'int'),
                    'is_multi_payment' => array('type'=>'int'),
                    'weight' => array('type'=>'decimal'),
                    'shipping_method' => array(),
                    'shipping_description' => array(),
                    'subtotal' => array('type'=>'decimal'),
                    'tax_amount' => array('type'=>'decimal'),
                    'shipping_amount' => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'giftcert_amount' => array('type'=>'decimal'),
                    'custbalance_amount' => array('type'=>'decimal'),
                    'grand_total' => array('type'=>'decimal'),
                    'total_paid' => array('type'=>'decimal'),
                    'total_due' => array('type'=>'decimal'),
                    'customer_notes' => array('type'=>'text'),
        
                ),
            ),
            'order_address' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/order_attribute_backend_child'),
                    'quote_address_id' => array('type'=>'int'),
                    'address_type' => array(),
                    'customer_id' => array('type'=>'int'),
                    'customer_address_id' => array('type'=>'int'),
                    'email' => array(),
                    'firstname' => array(),
                    'lastname' => array(),
                    'company' => array(),
                    'street' => array(),
                    'city' => array(),
                    'region' => array(),
                    'region_id' => array('type'=>'int'),
                    'postcode' => array(),
                    'country_id' => array('type'=>'int'),
                    'telephone' => array(),
                    'fax' => array(),
        
                ),
            ),
            'order_item' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/order_attribute_backend_child'),
                    'quote_item_id' => array('type'=>'int'),
                    'product_id' => array('type'=>'int'),
                    'sku' => array(),
                    'image' => array(),
                    'name' => array(),
                    'description' => array('type'=>'text'),
                    'qty_ordered' => array('type'=>'decimal'),
                    'qty_backordered' => array('type'=>'decimal'),
                    'qty_canceled' => array('type'=>'decimal'),
                    'qty_shipped' => array('type'=>'decimal'),
                    'qty_returned' => array('type'=>'decimal'),
                    'price' => array('type'=>'decimal'),
                    'cost' => array('type'=>'decimal'),
                    'discount_percent' => array('type'=>'decimal'),
                    'discount_amount' => array('type'=>'decimal'),
                    'tax_percent' => array('type'=>'decimal'),
                    'tax_amount' => array('type'=>'decimal'),
                    'row_total' => array('type'=>'decimal'),
                    'row_weight' => array('type'=>'decimal'),
                ),
            ),    
            'order_payment' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/order_attribute_backend_child'),
                    'quote_payment_id' => array('type'=>'int'),
                    'customer_payment_id' => array('type'=>'int'),
                    'amount' => array('type'=>'decimal'),
                    'method' => array(),
                    'po_number' => array(),
                    'cc_type' => array(),
                    'cc_number_enc' => array(),
                    'cc_last4' => array(),
                    'cc_owner' => array(),
                    'cc_exp_month' => array('type'=>'int'),
                    'cc_exp_year' => array('type'=>'int'),
                    'cc_trans_id' => array(),
                    'cc_approval' => array(),
                    'cc_avs_status' => array(),
                    'cc_cid_status' => array(),
                    'cc_debug_request' => array(),
                    'cc_debug_response' => array(),
                    
                    'anet_trans_method' => array(),
                    'echeck_routing_number' => array(),
                    'echeck_bank_name' => array(),
                    'echeck_account_type' => array(),
                    'echeck_account_name' => array(),
                    'echeck_type' => array(),
                ),
            ),

            'order_status_history' => array(
                'table'=>'sales/order',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/order_attribute_backend_child'),
                    'order_status_id' => array('type'=>'int'),
                    'comments' => array('type'=>'text'),
                    'is_customer_notified' => array('type'=>'int'),
                ),
            ),
            
            'invoice' => array(
                'table'=>'sales/invoice',
                'increment_model'=>'eav/entity_increment_numeric',
                'increment_per_store'=>true,
                'attributes' => array(
                    'entity_id' => array('type'=>'static', 'backend'=>'sales_entity/invoice_attribute_backend_parent'),
                    'customer_id' => array('type'=>'int'),
                    'order_id' => array('type'=>'int'),
                    'invoice_status_id' => array('type'=>'int'),
                    'billing_address_id' => array('type'=>'int'),
                    'shipping_address_id' => array('type'=>'int'),
                    'base_currency_code' => array(),
                    'store_currency_code' => array(),
                    'order_currency_code' => array(),
                    'store_to_base_rate' => array('type'=>'decimal'),
                    'store_to_order_rate' => array('type'=>'decimal'),
                    'is_virtual' => array('type'=>'int'),
                    'subtotal' => array('type'=>'decimal'),
                    'tax_amount' => array('type'=>'decimal'),
                    'shipping_amount' => array('type'=>'decimal'),
                    'grand_total' => array('type'=>'decimal'),
                    'total_paid' => array('type'=>'decimal'),
                    'total_due' => array('type'=>'decimal'),
                ),
            ),
            'invoice_address' => array(
                'table'=>'sales/invoice',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/invoice_attribute_backend_child'),
                    'order_address_id' => array('type'=>'int'),
                    'address_type' => array(),
                    'customer_id' => array('type'=>'int'),
                    'customer_address_id' => array('type'=>'int'),
                    'email' => array(),
                    'firstname' => array(),
                    'lastname' => array(),
                    'company' => array(),
                    'street' => array(),
                    'city' => array(),
                    'region' => array(),
                    'region_id' => array('type'=>'int'),
                    'postcode' => array(),
                    'country_id' => array('type'=>'int'),
                    'telephone' => array(),
                    'fax' => array(),
                ),
            ),
            'invoice_item' => array(
                'table'=>'sales/invoice',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/invoice_attribute_backend_child'),
                    'order_item_id' => array('type'=>'int'),
                    'product_id' => array('type'=>'int'),
                    'product_name' => array(),
                    'description' => array('type'=>'text'),
                    'sku' => array(),
                    'qty' => array('type'=>'decimal'),
                    'price' => array('type'=>'decimal'),
                    'cost' => array('type'=>'decimal'),
                    'row_total' => array('type'=>'decimal'),
                    'shipment_id' => array('type'=>'int'),
                ),
            ),
            'invoice_payment' => array(
                'table'=>'sales/invoice',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/invoice_attribute_backend_child'),
                    'order_payment_id' => array('type'=>'int'),
                    'amount' => array('type'=>'decimal'),
                    'method' => array(),
                    'cc_trans_id' => array(),
                    'cc_approval' => array(),
                    'cc_debug_request' => array(),
                    'cc_debug_response' => array(),
                ),
            ),
            'invoice_shipment' => array(
                'table'=>'sales/invoice',
                'attributes' => array(
                    'parent_id' => array('type'=>'static', 'backend'=>'sales_entity/invoice_attribute_backend_child'),
                    'shipping_method' => array(),
                    'tracking_id' => array(),
                    'shipment_status_id' => array('type'=>'int'),
                ),
            ),
        );
    }
}