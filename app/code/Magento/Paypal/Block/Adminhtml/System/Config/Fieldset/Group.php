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
namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

class Group
    extends \Magento\Backend\Block\System\Config\Form\Fieldset
{
    /**
     * Return header comment part of html for fieldset
     *
     * @param \Magento\Data\Form\Element\AbstractElement $element
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
     * @param \Magento\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isCollapseState($element)
    {
        $extra = \Mage::getSingleton('Magento\Backend\Model\Auth\Session')->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        if ($element->getExpanded() !== null) {
            return true;
        }

        return false;
    }
}
