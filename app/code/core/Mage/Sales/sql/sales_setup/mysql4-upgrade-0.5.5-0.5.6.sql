drop table if exists `sales_order_status`;

CREATE TABLE `sales_order_status` (
  `order_status_id` int(5) unsigned NOT NULL auto_increment,
  `frontend_label` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`order_status_id`),
  UNIQUE KEY `frontend_label` (`frontend_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sales_order_status` (`order_status_id`, `frontend_label`) VALUES (1, 'Pending');
INSERT INTO `sales_order_status` (`order_status_id`, `frontend_label`) VALUES (2, 'Processing');
INSERT INTO `sales_order_status` (`order_status_id`, `frontend_label`) VALUES (3, 'Complete');
INSERT INTO `sales_order_status` (`order_status_id`, `frontend_label`) VALUES (4, 'Cancelled');


alter table `sales_quote_entity_datetime` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_quote_entity_datetime` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_quote_entity_decimal` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_quote_entity_decimal` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_quote_entity_int` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_quote_entity_int` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_quote_entity_varchar` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_quote_entity_varchar` add index `value_by_entity_type` (`entity_type_id`, `value`);

alter table `sales_order_entity_datetime` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_order_entity_datetime` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_order_entity_decimal` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_order_entity_decimal` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_order_entity_int` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_order_entity_int` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_order_entity_varchar` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_order_entity_varchar` add index `value_by_entity_type` (`entity_type_id`, `value`);

alter table `sales_invoice_entity_datetime` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_invoice_entity_datetime` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_invoice_entity_decimal` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_invoice_entity_decimal` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_invoice_entity_int` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_invoice_entity_int` add index `value_by_entity_type` (`entity_type_id`, `value`);
alter table `sales_invoice_entity_varchar` add index `value_by_attribute` (`attribute_id`, `value`);
alter table `sales_invoice_entity_varchar` add index `value_by_entity_type` (`entity_type_id`, `value`);

