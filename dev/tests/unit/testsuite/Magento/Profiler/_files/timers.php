<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
?>
<?php return array(
    'some_root_timer' => array(
        'start'         => false,
        'sum'           => 0.08,
        'count'         => 2,
        'realmem'       => 50000000,
        'emalloc'       => 51000000,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
    'some_root_timer->some_nested_timer' => array(
        'start'         => false,
        'sum'           => 0.08,
        'count'         => 3,
        'realmem'       => 40000000,
        'emalloc'       => 42000000,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
    'some_root_timer->some_nested_timer->some_deeply_nested_timer' => array(
        'start'         => false,
        'sum'           => 0.03,
        'count'         => 3,
        'realmem'       => 10000000,
        'emalloc'       => 13000000,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
    'one_more_root_timer' => array(
        'start'         => false,
        'sum'           => 0.01,
        'count'         => 1,
        'realmem'       => 12345678,
        'emalloc'       => 23456789,
        'realmem_start' => 0,
        'emalloc_start' => 0,
    ),
);
