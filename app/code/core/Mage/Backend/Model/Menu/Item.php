<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Menu item. Should be used to create nested menu structures with Mage_Backend_Model_Menu
 */
class Mage_Backend_Model_Menu_Item
{
    /**
     * Menu item id
     *
     * @var string
     */
    protected $_id;

    /**
     * Menu item title
     *
     * @var string
     */
    protected $_title;

    /**
     * Module of menu item
     *
     * @var Mage_Core_Helper_Abstract
     */
    protected $_moduleHelper;

    /**
     * Menu item sort index in list
     *
     * @var string
     */
    protected $_sortIndex = null;

    /**
     * Menu item action
     *
     * @var string
     */
    protected $_action = null;

    /**
     * Parent menu item id
     *
     * @var string
     */
    protected $_parentId = null;

    /**
     * Acl resource of menu item
     *
     * @var string
     */
    protected $_resource;

    /**
     * Item tooltip text
     *
     * @var string
     */
    protected $_tooltip;

    /**
     * Path from root element in tree
     *
     * @var string
     */
    protected $_path = '';

    /**
     * Acl
     *
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_acl;

    /**
     * Module that item is dependent on
     *
     * @var string|null
     */
    protected $_dependsOnModule;

    /**
     * Global config option that item is dependent on
     *
     * @var string|null
     */
    protected $_dependsOnConfig;

    /**
     * Submenu item list
     *
     * @var Mage_Backend_Model_Menu
     */
    protected $_submenu;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_objectFactory;

    /**
     * @var Mage_Backend_Model_Url
     */
    protected $_urlModel;

    /**
     * @var Mage_Backend_Model_Menu_Item_Validator
     */
    protected $_validator;

    /**
     * @param array $data
     * @throws InvalidArgumentException
     * @throws BadMethodCallException
     */
    public function __construct(array $data = array())
    {
        if (!isset($data['validator'])
            || !$data['validator'] instanceof Mage_Backend_Model_Menu_Item_Validator) {
            throw new InvalidArgumentException('Wrong validator object provided');
        }

        $this->_validator = $data['validator'];
        $this->_validator->validate($data);

        $this->_acl = $data['acl'];
        $this->_objectFactory = $data['objectFactory'];
        $this->_urlModel = $data['urlModel'];

        $this->_id = $data['id'];
        $this->_title = $data['title'];
        $this->_moduleHelper = $data['module'];
        $this->_action = isset($data['action']) ? $data['action'] : null;
        $this->_resource = isset($data['resource']) ? $data['resource'] : null;
        $this->_tooltip = isset($data['toolTip']) ? $data['toolTip'] : '';
    }

    /**
     * Retrieve item id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Check whether item has subnodes
     *
     * @return bool
     */
    public function hasChildren()
    {
        return !is_null($this->_submenu) && (bool) $this->_submenu->count();
    }

    /**
     * Retrieve submenu
     *
     * @return Mage_Backend_Model_Menu
     */
    public function getChildren()
    {
        if (!$this->_submenu) {
            $this->_submenu = $this->_objectFactory->getModelInstance(
                'Mage_Backend_Model_Menu',
                array('path' => $this->getFullPath())
            );
        }
        return $this->_submenu;
    }

    /**
     * Retrieve full path from root element
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->_path . $this->_id;
    }

    /**
     * Retrieve menu item url
     *
     * @return string
     */
    public function getUrl()
    {
        if ((bool) $this->_action) {
            return $this->_urlModel->getUrl((string)$this->_action, array('_cache_secret_key' => true));
        }
        return '#';
    }

    /**
     * Retrieve menu item action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Set Item action
     *
     * @param string $action
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setAction($action)
    {
        $this->_validator->validateParam('action', $action);
        $this->_action = $action;
        return $this;
    }

    /**
     * Chechk whether item has javascript callback on click
     *
     * @return bool
     */
    public function hasClickCallback()
    {
        return $this->getUrl() == '#';
    }

    /**
     * Retrieve item click callback
     *
     * @return bool
     */
    public function getClickCallback()
    {
        if ($this->getUrl() == '#') {
            return 'return false;';
        }
        return '';
    }

    /**
     * Retrieve tooltip text title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set Item title
     *
     * @param string $title
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setTitle($title)
    {
        $this->_validator->validateParam('title', $title);
        $this->_title = $title;
        return $this;
    }

    /**
     * Check whether item has tooltip text
     *
     * @return bool
     */
    public function hasTooltip()
    {
        return (bool) $this->_tooltip;
    }

    /**
     * Retrieve item tooltip text
     *
     * @return string
     */
    public function getTooltip()
    {
        return $this->_tooltip;
    }

    /**
     * Set Item tooltip
     *
     * @param string $tooltip
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setTooltip($tooltip)
    {
        $this->_validator->validateParam('tooltip', $tooltip);
        $this->_tooltip = $tooltip;
        return $this;
    }

    /**
     * Retrieve module helper object linked to item.
     * Should be used to translate item labels
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getModuleHelper()
    {
        return $this->_moduleHelper;
    }

    /**
     * Set Item module
     *
     * @param Mage_Core_Helper_Abstract $helper
     * @return Mage_Backend_Model_Menu_Item
     * @throws InvalidArgumentException
     */
    public function setModuleHelper(Mage_Core_Helper_Abstract $helper)
    {
        $this->_validator->validateParam('module', $helper);
        $this->_moduleHelper = $helper;
        return $this;
    }

    /**
     * Check whether item is disabled. Disabled items are not shown to user
     *
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->_moduleHelper->isModuleOutputEnabled();
    }

    /**
     * Check whether item is allowed to the user
     *
     * @return bool
     */
    public function isAllowed()
    {
        try {
            $aclResource = 'admin/' . ($this->_resource ? (string)$this->_resource : $this->getFullPath());
            return $this->_acl->isAllowed($aclResource);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Set path in structure
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
        if ($this->_submenu) {
            $this->_submenu->setPath($this->getFullPath());
        }
    }
}
