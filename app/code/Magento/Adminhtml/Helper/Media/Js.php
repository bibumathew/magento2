<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Media library js helper
 *
 * @deprecated since 1.7.0.0
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Helper\Media;

class Js extends \Magento\Core\Helper\Js
{
    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Helper_Context $context
     * @param \Magento\Core\Model\Config\Modules\Reader $configReader
     * @param \Magento\Core\Model\Cache\Type\Config $configCacheType
     * @param Magento_Core_Model_View_Url $viewUrl
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Helper_Context $context,
        Magento_Core_Model_Config_Modules_Reader $configReader,
        Magento_Core_Model_Cache_Type_Config $configCacheType,
        Magento_Core_Model_View_Url $viewUrl
    )
    {
        parent::__construct($coreData, $context, $configReader, $configCacheType, $viewUrl);
        $this->_translateData = array(
            'Complete' => __('Complete'),
            'The file size should be more than 0 bytes.' => __('The file size should be more than 0 bytes.'),
            'Upload Security Error' => __('Upload Security Error'),
            'Upload HTTP Error'     => __('Upload HTTP Error'),
            'Upload I/O Error'     => __('Upload I/O Error'),
            'SSL Error: Invalid or self-signed certificate' => __('SSL Error: Invalid or self-signed certificate'),
            'Tb' => __('Tb'),
            'Gb' => __('Gb'),
            'Mb' => __('Mb'),
            'Kb' => __('Kb'),
            'b' => __('b')
        );
    }

    /**
     * Retrieve JS translator initialization javascript
     *
     * @return string
     */
    public function getTranslatorScript()
    {
        $script = '(function($) {$.mage.translate.add(' . $this->getTranslateJson() . ')})(jQuery);';
        return $this->getScript($script);
    }
}
