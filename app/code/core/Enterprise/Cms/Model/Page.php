<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Cms Page Model extended with Revison functionality
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Model_Page extends Mage_Cms_Model_Page
{
    /**
     * Configuration model
     * @var Enterprise_Cms_Model_Config
     */
    protected $_config;

    /**
     * Flag which deterimnes if native save logic will be run
     * @var bool
     */
    protected $_canRunNativeSave = false;

    /**
     * Flag which determines if native delete logic will be run
     * @var unknown_type
     */
    protected $_canRunNativeDelete = false;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_cms/page');
        $this->_config = Mage::getSingleton('enterprise_cms/config');

        $this->_canRunNativeDelete = $this->_config->isCurrentUserCanDeletePage();

        $this->_canRunNativeSave = $this->_config->isCurrentUserCanCreatePage()
                || $this->_config->isCurrentUserCanPublish();
    }

    /**
     * Filter original cms attributes.
     * Unset data which is under revision control and store it in separate attribute.
     *
     * @return Enterprise_Cms_Model_Page
     */
    protected function _filterData()
    {
        $revisionedData = array();
        $attributes = $this->_config->getPageRevisionControledAttributes();
        foreach ($this->getData() as $key => $value) {
            if (in_array($key, $attributes)) {
                $this->unsData($key);
                $revisionedData[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Processing object after delete data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterDelete()
    {
        if ($this->_canRunNativeDelete) {
            parent::_afterDelete($object);
        }

        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return Enterprise_Cms_Model_Page
     */
    protected function _afterSave()
    {
        if ($this->_canRunNativeSave) {
            parent::_afterSave();
        }

        return $this;
    }

    /**
     * Processing object before delete data
     *
     * @return Enterprise_Cms_Model_Page
     */
    protected function _beforeDelete()
    {
        if ($this->_canRunNativeDelete) {
            parent::_beforeDelete($object);
        }

        return $this;
    }

    /**
     * Processing object before save data
     *
     * @return Enterprise_Cms_Model_Page
     */
    protected function _beforeSave()
    {
        if ($this->_canRunNativeSave) {
            parent::_beforeSave();
        }

        return $this;
    }

    /**
     * Save page's revision data if we have permission for this.
     *
     * @return Enterprise_Cms_Model_Page
     */
    public function save()
    {
        if (!$this->_config->isCurrentUserCanSave()) {
            Mage::throwException(Mage::helper('enterprise_cms')->__('You don\'t have permissions to save revisions.'));
        }
        return parent::save();
    }

    /**
     * Delete page or page's revision if we have permission for this.
     *
     * @return Enterprise_Cms_Model_Page
     */
    public function delete()
    {
        if (!$this->_config->isCurrentUserCanDeletePage()) {
            Mage::throwException(Mage::helper('enterprise_cms')->__('You don\'t have permissions to delete page.'));
        } elseif (!$this->_config->isCurrentUserCanDelete()) {
            Mage::throwException(Mage::helper('enterprise_cms')->__('You don\'t have permissions to delete revision.'));
        }
        return parent::save();
    }
}
