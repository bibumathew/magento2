<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_AdminGws
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Websites fieldset for admin roles edit tab
 */
namespace Magento\AdminGws\Block\Adminhtml\Permissions\Tab\Rolesedit;

class Gws extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\AdminGws\Model\Role
     */
    protected $_adminGwsRole;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\AdminGws\Model\Role $adminGwsRole
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\AdminGws\Model\Role $adminGwsRole,
        \Magento\Core\Model\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_adminGwsRole = $adminGwsRole;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Check whether role assumes all websites permissions
     *
     * @return bool
     */
    public function getGwsIsAll()
    {
        if (!$this->canAssignGwsAll()) {
            return false;
        }

        if (!$this->_coreRegistry->registry('current_role')->getId()) {
            return true;
        }

        return $this->_coreRegistry->registry('current_role')->getGwsIsAll();
    }

    /**
     * Get the role object
     *
     * @return \Magento\User\Model\Role
     */
    public function getRole()
    {
        return $this->_coreRegistry->registry('current_role');
    }

    /**
     * Check an ability to create 'no website restriction' roles
     *
     * @return bool
     */
    public function canAssignGwsAll()
    {
        return $this->_adminGwsRole->getIsAll();
    }

    /**
     * Gather disallowed store group ids and return them as Json
     *
     * @return string
     */
    public function getDisallowedStoreGroupsJson()
    {
        $result = array();
        foreach ($this->_storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $groupId = $group->getId();
                if (!$this->_adminGwsRole->hasStoreGroupAccess($groupId)) {
                    $result[$groupId] = $groupId;
                }
            }
        }
        return $this->_jsonEncoder->encode($result);
    }

    /**
     * Get websites
     *
     * @return \Magento\Core\Model\Website[]
     */
    public function getWebsites()
    {
        return $this->_storeManager->getWebsites();
    }
}
