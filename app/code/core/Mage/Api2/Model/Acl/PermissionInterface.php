<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * API2 ACL resource permission interface
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
interface Mage_Api2_Model_Acl_PermissionInterface
{
    /**
     * Get ACL resources permissions
     *
     * Get permissions list with set permissions
     *
     * @return array
     */
    public function getResourcesPermissions();

    /**
     * Set filter value
     *
     * @param mixed $filterValue
     * @return Mage_Api2_Model_Acl_PermissionInterface
     */
    public function setFilterValue($filterValue);
}