<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Enterprise_TargetRule
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

if (!isset($type)) {
    $type = 'related';
}
$applyTo = $type == 'related' ? '1' : '2';

$rule = new Enterprise_TargetRule_Model_Rule;
$data = array(
    'name' => $type,
    'is_active' => '1',
    'apply_to' => $applyTo,
    'use_customer_segment' => '0',
    'customer_segment_ids' => array('0' => ''),
);
$rule->loadPost($data);
$rule->save();
