<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * OAuth authorization base abstract block with auth buttons
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Oauth_Block_Authorize_ButtonBaseAbstract extends Mage_Oauth_Block_Authorize_Abstract
{
    /**
     * Get confirm url path
     *
     * @return string
     */
    abstract public function getConfirmUrlPath();

    /**
     * Get reject url path
     *
     * @return string
     */
    abstract public function getRejectUrlPath();

    /**
     * Retrieve reject authorization url
     *
     * @return string
     */
    public function getConfirmUrl()
    {
        return $this->getUrl($this->getConfirmUrlPath() . ($this->getIsSimple() ? 'Simple' : ''));
    }

    /**
     * Retrieve reject authorization url
     *
     * @return string
     */
    public function getRejectUrl()
    {
        return $this->getUrl($this->getRejectUrlPath() . ($this->getIsSimple() ? 'Simple' : ''));
    }
}