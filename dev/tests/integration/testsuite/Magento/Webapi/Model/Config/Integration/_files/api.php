<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
return array(
    'TestIntegration1' => array(
        'resources' => array(
            'Magento_Customer::manage',
            'Magento_Customer::online',
            'Magento_Sales::capture',
            'Magento_SalesRule::quote'
        )
    ),
    'TestIntegration2' => array(
        'resources' => array(
            'Magento_Catalog::product_read',
            'Magento_SalesRule::config_promo'
        )
    ),
    'TestIntegration3' => array(
        'resources' => array(
            'Magento_Catalog::product_read',
            'Magento_Sales::create',
            'Magento_SalesRule::quote'
        )
    ),
);
