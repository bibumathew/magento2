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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 Global ACL role resources permissions model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl_Global_Rule_ResourcePermission
    implements Mage_Api2_Model_Acl_Global_AclPermissionInterface
{
    /**
     * Resources permissions
     *
     * @var array
     */
    protected $_resourcesPermissions;

    /**
     * Filter item value
     *
     * @var int
     */
    protected $_roleId;

    /**
     * Get resources permissions for selected role
     *
     * @return array
     */
    public function getResourcesPermissions()
    {
        if (null === $this->_resourcesPermissions) {
            $rulesPairs = array();

            if ($this->_roleId) {
                /** @var $rules Mage_Api2_Model_Resource_Acl_Global_Rule_Collection */
                $rules = Mage::getResourceModel('api2/acl_global_rule_collection');
                $rules->addFilterByRoleId($this->_roleId);

                /** @var $rule Mage_Api2_Model_Acl_Global_Rule */
                foreach ($rules as $rule) {
                    $resourceId = $rule->getResourceId();
                    $rulesPairs[$resourceId]['privileges'][$rule->getPrivilege()] =
                            Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_ALLOW;
                }
            } else {
                //make resource "all" as default for new item
                $rulesPairs = array(
                    Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL =>
                    Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_ALLOW);
            }

            //set permissions to resources
            /** @var $config Mage_Api2_Model_Config */
            $config = Mage::getModel('api2/config');
            $resources = $config->getResources();
            /** @var $privilegeSource Mage_Api2_Model_Acl_Global_Rule_Privilege */
            $privilegeSource = Mage::getModel('api2/acl_global_rule_privilege');
            $privileges = array_keys($privilegeSource->toArray());

            /** @var $node Varien_Simplexml_Element */
            foreach ($resources as $node) {
                $resourceId = (string) $node->type;
                $allowedPrivileges = (array) $node->privileges;
                foreach ($privileges as $privilege) {
                    if (empty($allowedPrivileges[$privilege])
                        && isset($rulesPairs[$resourceId]['privileges'][$privilege])
                    ) {
                        unset($rulesPairs[$resourceId]['privileges'][$privilege]);
                    } elseif (!empty($allowedPrivileges[$privilege])
                        && !isset($rulesPairs[$resourceId]['privileges'][$privilege])
                    ) {
                        $rulesPairs[$resourceId]['privileges'][$privilege] =
                            Mage_Api2_Model_Acl_Global_Rule_Permission::TYPE_DENY;
                    }
                }
            }
            $this->_resourcesPermissions = $rulesPairs;
        }
        return $this->_resourcesPermissions;
    }

    /**
     * Set filter value
     *
     * Set role ID
     *
     * @param Mage_Api2_Model_Acl_Global_Role $role
     */
    public function setFilterValue($role)
    {
        if ($role && $role->getId()) {
            $this->_roleId = $role->getId();
        }
    }
}
