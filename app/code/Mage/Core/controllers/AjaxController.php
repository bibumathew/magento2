<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 */
class Mage_Core_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Ajax action for inline translation
     */
    public function translateAction()
    {
        $translationParams = (array)$this->getRequest()->getPost('translate');
        $area = $this->getRequest()->getPost('area');
        /** @var Mage_Core_Helper_Translate $translationHelper */
        $translationHelper = $this->_objectManager->get('Mage_Core_Helper_Translate');
        $response = $translationHelper->apply($translationParams, $area);
        $this->getResponse()->setBody($response);
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
    }
}
