<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Captcha
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Captcha block
 *
 * @category   Core
 * @package    Magento_Captcha
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Block\Captcha;

class DefaultCaptcha extends \Magento\Core\Block\Template
{
    protected $_template = 'default.phtml';

    /**
     * @var string
     */
    protected $_captcha;

    /**
     * Captcha data
     *
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captchaData = null;

    /**
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $captchaData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_captchaData = $captchaData;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Returns template path
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->getIsAjax() ? '' : $this->_template;
    }

    /**
     * Returns URL to controller action which returns new captcha image
     *
     * @return string
     */
    public function getRefreshUrl()
    {
        $urlPath = 'captcha/refresh';
        $params = array('_secure' => \Mage::app()->getStore()->isCurrentlySecure());

        if (\Mage::app()->getStore()->isAdmin()) {
            $urlPath = 'adminhtml/refresh/refresh';
            $params = array_merge($params, array('_nosecret' => true));
        }

        return \Mage::app()->getStore()->getUrl($urlPath, $params);
    }

    /**
     * Renders captcha HTML (if required)
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getCaptchaModel()->isRequired()) {
            $this->getCaptchaModel()->generate();
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Returns captcha model
     *
     * @return Magento_Captcha_Model_Abstract
     */
    public function getCaptchaModel()
    {
        return $this->_captchaData->getCaptcha($this->getFormId());
    }
}
