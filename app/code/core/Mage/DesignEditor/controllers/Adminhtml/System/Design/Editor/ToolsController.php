<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Backend controller for the design editor
 */
class Mage_DesignEditor_Adminhtml_System_Design_Editor_ToolsController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  Upload custom CSS action
     */
    public function uploadAction()
    {
        $themeCss = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Css');
        /** @var $serviceModel Mage_Theme_Model_Uploader_Service */
        $serviceModel = $this->_objectManager->get('Mage_Theme_Model_Uploader_Service');
        try {
            $theme = $this->_getEditableTheme();
            $cssFileContent = $serviceModel->uploadCssFile(
                Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Custom::FILE_ELEMENT_NAME
            )->getFileContent();
            $themeCss->setDataForSave($cssFileContent);
            $themeCss->saveData($theme);
            $response = array('error' => false, 'content' => $cssFileContent);
            $this->_session->addSuccess($this->__('Success: Theme custom css was saved.'));
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot upload css file');
            $this->_session->addError($errorMessage);
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->loadLayout();
        $response['message_html'] = $this->getLayout()->getMessagesBlock()->toHtml();
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Save custom css file
     */
    public function saveCssContentAction()
    {
        $customCssContent = (string)$this->getRequest()->getParam('custom_css_content', '');
        try {
            $theme = $this->_getEditableTheme();
            /** @var $themeCss Mage_Core_Model_Theme_Customization_Files_Css */
            $themeCss = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Css');
            $themeCss->setDataForSave(
                array(Mage_Core_Model_Theme_Customization_Files_Css::CUSTOM_CSS => $customCssContent)
            );
            $theme->setCustomization($themeCss)->save();
            $response = array('error' => false);
            $this->_session->addSuccess($this->__('Theme custom css was saved.'));
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot save custom css');
            $this->_session->addError($errorMessage);
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->loadLayout();
        $response['message_html'] = $this->getLayout()->getMessagesBlock()->toHtml();
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Ajax list of existing javascript files
     */
    public function jsListAction()
    {
        try {
            $theme = $this->_getEditableTheme();
            $this->loadLayout();

            /** @var $filesJs Mage_Core_Model_Theme_Customization_Files_Js */
            $filesJs = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Js');
            $customJsFiles = $theme->setCustomization($filesJs)
                ->getCustomizationData(Mage_Core_Model_Theme_Customization_Files_Js::TYPE);

            $jsItemsBlock = $this->getLayout()->getBlock('design_editor_tools_code_js_items');
            $jsItemsBlock->setJsFiles($customJsFiles);

            $result = array('error' => false, 'content' => $jsItemsBlock->toHtml());
            $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
        } catch (Exception $e) {
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
    }

    /**
     * Upload js file
     */
    public function uploadJsAction()
    {
        /** @var $serviceModel Mage_Theme_Model_Uploader_Service */
        $serviceModel = $this->_objectManager->get('Mage_Theme_Model_Uploader_Service');
        try {
            $theme = $this->_getEditableTheme();
            $serviceModel->uploadJsFile('js_files_uploader', $theme, false);
            $theme->setCustomization($serviceModel->getJsFiles())->save();
            $this->_forward('jsList');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot upload js file');
            $this->_getSession()->addError($errorMessage);
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->loadLayout();
        $response['message_html'] = $this->getLayout()->getMessagesBlock()->toHtml();
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Delete custom file action
     */
    public function deleteCustomFilesAction()
    {
        $removeJsFiles = (array)$this->getRequest()->getParam('js_removed_files');
        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
        try {
            $theme = $this->_getEditableTheme();

            /** @var $themeJs Mage_Core_Model_Theme_Customization_Files_Js */
            $themeJs = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Js');
            $theme->setCustomization($themeJs);

            $themeJs->setDataForDelete($removeJsFiles);
            $theme->save();

            $this->_forward('jsList');
        } catch (Exception $e) {
            $this->_redirectUrl($this->_getRefererUrl());
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
    }

    /**
     * Reorder js file
     */
    public function reorderJsAction()
    {
        $reorderJsFiles = (array)$this->getRequest()->getParam('js_order', array());
        /** @var $themeJs Mage_Core_Model_Theme_Customization_Files_Js */
        $themeJs = $this->_objectManager->create('Mage_Core_Model_Theme_Customization_Files_Js');
        try {
            $theme = $this->_getEditableTheme();
            $themeJs->setJsOrderData($reorderJsFiles);
            $theme->setCustomization($themeJs);
            $theme->save();

            $result = array('success' => true);
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot upload css file');
            $this->_session->addError($errorMessage);
            $result = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->loadLayout();
        $result['message_html'] = $this->getLayout()->getMessagesBlock()->toHtml();
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    /**
     * Save image sizes
     */
    public function saveImageSizingAction()
    {
        $imageSizing = $this->getRequest()->getParam('imagesizing');
        /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
        $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');
        /** @var $imageSizingValidator Mage_DesignEditor_Model_Editor_Tools_ImageSizing_Validator */
        $imageSizingValidator = $this->_objectManager->get(
            'Mage_DesignEditor_Model_Editor_Tools_ImageSizing_Validator'
        );
        try {
            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_IMAGE_SIZING, $this->_getEditableTheme()
            );
            $imageSizing = $imageSizingValidator->validate($configuration->getAllControlsData(), $imageSizing);
            $configuration->saveData($imageSizing);
            $this->_session->addSuccess('Image sizes are saved.');
            $result = array('success' => true);
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
            $result = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot save image sizes.');
            $this->_session->addError($errorMessage);
            $result = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->loadLayout();
        $result['message_html'] = $this->getLayout()->getMessagesBlock()->toHtml();
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($result));

    }

    /**
     * Upload quick style image
     */
    public function uploadQuickStyleImageAction()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');

        /** @var $uploaderModel Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader */
        $uploaderModel = $this->_objectManager->get('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader');
        /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
        $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');
        try {
            $theme = $this->_getEditableTheme();
            $keys = array_keys($this->getRequest()->getFiles());
            $result = $uploaderModel->setTheme($theme)->uploadFile($keys[0]);

            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_QUICK_STYLES, $theme
            );
            $configuration->saveData(array($keys[0] => $result['css_path']));

            $response = array('error' => false, 'content' => $result);
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot upload image file');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Remove quick style image
     */
    public function removeQuickStyleImageAction()
    {
        $fileName = $this->getRequest()->getParam('file_name', false);
        $elementName = $this->getRequest()->getParam('element', false);

        /** @var $uploaderModel Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader */
        $uploaderModel = $this->_objectManager->get('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_ImageUploader');
        try {
            $theme = $this->_getEditableTheme();
            $result = $uploaderModel->setTheme($theme)->removeFile($fileName);

            /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
            $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');

            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_QUICK_STYLES, $theme
            );
            $configuration->saveData(array($elementName => ''));

            $response = array('error' => false, 'content' => $result);
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot upload image file');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Upload store logo
     *
     * @throws Mage_Core_Exception
     */
    public function uploadStoreLogoAction()
    {
        try {
            $theme = $this->_getEditableTheme();

            /** @var $themeService Mage_Core_Model_Theme_Service */
            $themeService = $this->_objectManager->get('Mage_Core_Model_Theme_Service');
            $stores = $themeService->getStoresByThemes();

            if (!isset($stores[$theme->getId()])) {
                throw new Mage_Core_Exception($this->__('Theme is not assigned to any store.', $theme->getId()));
            }

            /** @var $storeLogo Mage_DesignEditor_Model_Editor_Tools_QuickStyles_LogoUploader */
            $storeLogo = $this->_objectManager->get('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_LogoUploader');

            foreach ($stores[$theme->getId()] as $store) {
                $storeLogo->setScope('stores')->setScopeId($store->getId())->setPath('design/header/logo_src')->save();
            }
            $response = array('error' => false, 'content' => array('name' => $storeLogo->getValue()));
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot upload image file');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Remove store logo
     *
     * @throws Mage_Core_Exception
     */
    public function removeStoreLogoAction()
    {
        try {
            $theme = $this->_getEditableTheme();

            /** @var $themeService Mage_Core_Model_Theme_Service */
            $themeService = $this->_objectManager->get('Mage_Core_Model_Theme_Service');
            $stores = $themeService->getStoresByThemes();

            if (!isset($stores[$theme->getId()])) {
                throw new Mage_Core_Exception($this->__('Theme is not assigned to any store.', $theme->getId()));
            }

            foreach ($stores[$theme->getId()] as $store) {
                $this->_objectManager->get('Mage_Backend_Model_Config_Backend_Store')
                    ->setScope('stores')->setScopeId($store->getId())->setPath('design/header/logo_src')
                    ->setValue('')->save();
            }

            $response = array('error' => false, 'content' => array());
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Cannot upload image file');
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Save quick styles data
     */
    public function saveQuickStylesAction()
    {
        $controlId = $this->getRequest()->getParam('id');
        $controlValue = $this->getRequest()->getParam('value');
        try {
            /** @var $configFactory Mage_DesignEditor_Model_Editor_Tools_Controls_Factory */
            $configFactory = $this->_objectManager->create('Mage_DesignEditor_Model_Editor_Tools_Controls_Factory');
            $configuration = $configFactory->create(
                Mage_DesignEditor_Model_Editor_Tools_Controls_Factory::TYPE_QUICK_STYLES,
                $this->_getEditableTheme()
            );
            $configuration->saveData(array($controlId => $controlValue));
            $response = array('success' => true);
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $errorMessage = $this->__('Error while saving quick style "%s"', 'some_style_id');
            $this->_session->addError($errorMessage);
            $response = array('error' => true, 'message' => $errorMessage);
            $this->_objectManager->get('Mage_Core_Model_Logger')->logException($e);
        }

        $this->loadLayout();
        $response['message_html'] = $this->getLayout()->getMessagesBlock()->toHtml();
        $this->getResponse()->setBody($this->_objectManager->get('Mage_Core_Helper_Data')->jsonEncode($response));
    }

    /**
     * Get theme launched in editor
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _getEditableTheme()
    {
        /** @var $dataHelper Mage_DesignEditor_Helper_Data */
        $dataHelper = $this->_objectManager->get('Mage_DesignEditor_Helper_Data');
        /** @var $helper Mage_Core_Helper_Theme */
        $helper = $this->_objectManager->get('Mage_Core_Helper_Theme');
        return $helper->loadTheme($dataHelper->getEditableThemeId());
    }
}
