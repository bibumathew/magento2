<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Centinel
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Authentication start/redirect form
 */
class Mage_Centinel_Block_Authentication_Start extends Mage_Core_Block_Template
{
    /**
     * Prepare form parameters and render
     *
     * @return string
     */
    protected function _toHtml()
    {
        $validator = Mage::registry('current_centinel_validator');
        if ($validator && $validator->shouldAuthenticate()) {
            $this->addData($validator->getAuthenticateStartData());
            return parent::_toHtml();
        }
        return '';
    }
}
