<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Fieldset renderer for PayPal solutions group
 */
class Magento_Paypal_Block_Adminhtml_System_Config_Fieldset_Group
    extends Magento_Backend_Block_System_Config_Form_Fieldset
{
    /**
     * @var Magento_Backend_Model_Auth_Session
     */
    protected $_backendAuthSession;

    /**
     * @param Magento_Backend_Block_Context $context
     * @param Magento_Backend_Model_Auth_Session $backendAuthSession
     * @param array $data
     */
    public function __construct(
        Magento_Backend_Block_Context $context,
        Magento_Backend_Model_Auth_Session $backendAuthSession,
        array $data = array()
    ) {
        $this->_backendAuthSession = $backendAuthSession;
        parent::__construct($context, $data);
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param Magento_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $groupConfig = $element->getGroup();

        if (empty($groupConfig['help_url']) || !$element->getComment()) {
            return parent::_getHeaderCommentHtml($element);
        }

        $html = '<div class="comment">' . $element->getComment()
            . ' <a target="_blank" href="' . $groupConfig['help_url'] . '">'
            . __('Help') . '</a></div>';

        return $html;
    }

    /**
     * Return collapse state
     *
     * @param Magento_Data_Form_Element_Abstract $element
     * @return bool
     */
    protected function _isCollapseState($element)
    {
        $extra = $this->_backendAuthSession->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        if ($element->getExpanded() !== null) {
            return true;
        }

        return false;
    }
}
