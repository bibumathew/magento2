<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Users
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * User statuses option array
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_UrlRewriteOptions implements Mage_Core_Model_Option_ArrayInterface
{
    /**
     * Return statuses array
     * @return array
     */
    public function toOptionArray()
    {
        return array('1' => __('System'),
                     '0' => __('Custom'));
    }
}