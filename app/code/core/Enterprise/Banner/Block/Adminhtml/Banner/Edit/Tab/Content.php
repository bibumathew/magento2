<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Banner
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Banner content per store view edit page
 *
 * @category   Enterprise
 * @package    Enterprise_Banner
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Banner_Block_Adminhtml_Banner_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Banner helper
     *
     * @var Enterprise_Banner_Helper_Data
     */
    protected $_helper;

    /**
     * WYSIWYG config object
     *
     * @var Mage_Cms_Model_Wysiwyg_Config
     */
    protected $_wysiwygConfigModel;


    /**
     * WYSIWYG config data
     *
     * @var Varien_Object
     */
    protected $_wysiwygConfig;

    /**
     * Event manager
     *
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * Registry model
     *
     * @var Mage_Core_Model_Registry
     */
    protected $_registryManager;

    /**
     * Application model
     *
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Cms_Model_Wysiwyg_Config $wysiwygConfig
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_Core_Model_App $app
     * @param Enterprise_Banner_Helper_Data $bannerHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Cms_Model_Wysiwyg_Config $wysiwygConfig,
        Mage_Core_Model_Registry $registry,
        Mage_Core_Model_App $app,
        Enterprise_Banner_Helper_Data $bannerHelper,
        array $data = array()
    ) {
        $this->_helper = $bannerHelper;
        $this->_wysiwygConfigModel = $wysiwygConfig;
        $this->_eventManager = $eventManager;
        $this->_registryManager = $registry;
        $this->_app = $app;

        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $data
        );
    }


    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->getHelper()->__('Content');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare Banners Content Tab form, define Editor settings
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('banner_content_');

        $model = $this->_registryManager->registry('current_banner');

        $this->_eventManager->dispatch(
            'adminhtml_banner_edit_tab_content_before_prepare_form',
            array('model' => $model, 'form' => $form)
        );

        $fieldsetHtmlClass = 'fieldset-wide';
        $fieldset = $this->_createDefaultContentFieldset($form, $fieldsetHtmlClass);

        if ($this->_app->isSingleStoreMode() == false) {
            $this->_createDefaultContentForStoresField($fieldset, $form, $model);
        }

        $this->_createStoreDefaultContentField($fieldset, $model, $form);

        if ($this->_app->isSingleStoreMode() == false) {
            $this->_createStoresContentFieldset($form, $fieldsetHtmlClass, $model);

        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Create default content fieldset
     *
     * @param Varien_Data_Form $form
     * @param string $fieldsetHtmlClass
     * @return Varien_Data_Form_Element_Fieldset
     */
    protected function _createDefaultContentFieldset($form, $fieldsetHtmlClass)
    {
        $fieldset = $form->addFieldset('default_fieldset', array(
            'legend' => $this->getHelper()->__('Default Content'),
            'class' => $fieldsetHtmlClass,
        ));
        return $fieldset;
    }

    /**
     * Get Wysiwyg Config
     *
     * @return Varien_Object
     */
    protected function _getWysiwygConfig()
    {
        if (is_null($this->_wysiwygConfig)) {
            $this->_wysiwygConfig = $this->_wysiwygConfigModel->getConfig(
                array(
                    'tab_id' => $this->getTabId(),
                    'skip_widgets' => array('Enterprise_Banner_Block_Widget_Banner'),
                )
            );
        }
        return $this->_wysiwygConfig;
    }

    /**
     * Create Store default content field
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param Enterprise_Banner_Model_Banner $model
     * @param Varien_Data_Form $form
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function _createStoreDefaultContentField($fieldset, $model, $form)
    {
        $storeContents = $this->_registryManager->registry('current_banner')->getStoreContents();
        $isDisabled = (bool)$model->getIsReadonly() || ($model->getCanSaveAllStoreViewsContent() === false)
            || (isset($storeContents[0]) ? false : (!$model->getId() ? false : true));
        $isVisible = (bool)$model->getIsReadonly() || ($model->getCanSaveAllStoreViewsContent() === false);
        $afterHtml = '<script type="text/javascript">'
            . ($isVisible ? '$(\'buttons' . $form->getHtmlIdPrefix() . 'store_default_content\').hide(); ' : '')
            . (isset($storeContents[0]) ? '' : (!$model->getId() ? '' : '$(\'store_default_content\').hide();'))
            . '</script>';
        return $fieldset->addField('store_default_content', 'editor', array(
            'name' => 'store_contents[0]',
            'value' => (isset($storeContents[0]) ? $storeContents[0] : ''),
            'disabled' => $isDisabled,
            'config' => $this->_getWysiwygConfig(),
            'wysiwyg' => false,
            'container_id' => 'store_default_content',
            'after_element_html' =>  $afterHtml,
        ));
    }

    /**
     * Create default content for stores field
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param Varien_Data_Form $form
     * @param Enterprise_Banner_Model_Banner $model
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function _createDefaultContentForStoresField($fieldset, $form, $model)
    {
        $storeContents = $this->_registryManager->registry('current_banner')->getStoreContents();
        $onclickScript = "$('store_default_content').toggle(); \n $('"
            . $form->getHtmlIdPrefix() . "store_default_content').disabled = !$('"
            . $form->getHtmlIdPrefix() . "store_default_content').disabled;";

        $afterHtml = '<label for="' . $form->getHtmlIdPrefix() . 'store_0_content_use">'
            . $this->getHelper()->__('No Default Content') . '</label>';

        $isDisabled = (bool)$model->getIsReadonly() || ($model->getCanSaveAllStoreViewsContent() === false);

        return $fieldset->addField('store_0_content_use', 'checkbox', array(
            'name' => 'store_contents_not_use[0]',
            'required' => false,
            'label' => $this->getHelper()->__('Banner Default Content for All Store Views'),
            'onclick' => $onclickScript,
            'checked' => isset($storeContents[0]) ? false : (!$model->getId() ? false : true),
            'after_element_html' => $afterHtml,
            'value' => 0,
            'fieldset_html_class' => 'store',
            'disabled' => $isDisabled
        ));
    }

    /**
     * Create fieldset that provides ability to change content per store view
     *
     * @param Varien_Data_Form $form
     * @param string $fieldsetHtmlClass
     * @param Enterprise_Banner_Model_Banner $model
     * @return Varien_Data_Form_Element_Fieldset
     */
    protected function _createStoresContentFieldset($form, $fieldsetHtmlClass, $model)
    {
        $storeContents = $this->_registryManager->registry('current_banner')->getStoreContents();
        $fieldset = $form->addFieldset('scopes_fieldset', array(
            'legend' => $this->getHelper()->__('Store View Specific Content'),
            'class' => $fieldsetHtmlClass,
            'table_class' => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('Mage_Backend_Block_Store_Switcher_Form_Renderer_Fieldset');
        $fieldset->setRenderer($renderer);
        $this->_getWysiwygConfig()->setUseContainer(true);
        foreach ($this->_app->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label", 'note', array(
                'label' => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label", 'note', array(
                    'label' => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $storeContent = isset($storeContents[$store->getId()]) ? $storeContents[$store->getId()] : '';
                    $contentFieldId = 's_' . $store->getId() . '_content';
                    $wysiwygConfig = clone $this->_getWysiwygConfig();
                    $fieldset->addField('store_' . $store->getId() . '_content_use', 'checkbox', array(
                        'name' => 'store_contents_not_use[' . $store->getId() . ']',
                        'required' => false,
                        'label' => $store->getName(),
                        'onclick' => "$('{$contentFieldId}').toggle(); $('" . $form->getHtmlIdPrefix()
                            . $contentFieldId . "').disabled = !$('" . $form->getHtmlIdPrefix() . $contentFieldId
                            . "').disabled;",
                        'checked' => $storeContent ? false : true,
                        'after_element_html' => '<label for="' . $form->getHtmlIdPrefix()
                            . 'store_' . $store->getId() . '_content_use">'
                            . $this->getHelper()->__('Use Default') . '</label>',
                        'value' => $store->getId(),
                        'fieldset_html_class' => 'store',
                        'disabled' => (bool)$model->getIsReadonly()
                    ));

                    $fieldset->addField($contentFieldId, 'editor', array(
                        'name' => 'store_contents[' . $store->getId() . ']',
                        'required' => false,
                        'disabled' => (bool)$model->getIsReadonly() || ($storeContent ? false : true),
                        'value' => $storeContent,
                        'container_id' => $contentFieldId,
                        'config' => $wysiwygConfig,
                        'wysiwyg' => false,
                        'after_element_html' =>
                            '<script type="text/javascript">' . ((bool)$model->getIsReadonly() ? '$(\'buttons'
                                . $form->getHtmlIdPrefix() . $contentFieldId . '\').hide(); ' : '')
                                . ($storeContent ? '' : '$(\'' . $contentFieldId . '\').hide();')
                                . '</script>',
                    ));
                }
            }
        }
        return $fieldset;
    }

    /**
     * Get helper
     *
     * @return Enterprise_Banner_Helper_Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }
}