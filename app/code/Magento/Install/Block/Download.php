<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Install
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Download Magento core modules and updates choice (online, offline)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Block;

class Download extends \Magento\Install\Block\AbstractBlock
{
    /**
     * @var string
     */
    protected $_template = 'download.phtml';

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_coreConfig;

    /**
     * Constructor
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\Config $coreConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\Config $coreConfig,
        array $data = array()
    ) {
        parent::__construct(
            $coreData,
            $context,
            $data
        );
        $this->_coreConfig = $coreConfig;
    }

    /**
     * Retrieve locale data post url
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('*/*/downloadPost');
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return \Mage::getModel('Magento\Install\Model\Wizard')
            ->getStepByName('download')
            ->getNextUrl();
    }

    /**
     * @return bool
     */
    public function hasLocalCopy()
    {
        $dir = $this->_coreConfig->getModuleDir('etc', 'Magento_Adminhtml');
        if ($dir && $this->_filesystem->isDirectory($dir)) {
            return true;
        }
        return false;
    }
}
