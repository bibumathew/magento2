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
 * Captcha cron actions
 *
 * @category    Magento
 * @package     Magento_Captcha
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model;

class Cron
{
    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;

    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Adminhtml\Data
     */
    protected $_adminHelper;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Captcha\Model\Resource\LogFactory
     */
    protected $_resLogFactory;

    /**
     * @param Resource\LogFactory $resLogFactory
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Captcha\Helper\Adminhtml\Data $adminHelper
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\Captcha\Model\Resource\LogFactory $resLogFactory,
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Captcha\Helper\Adminhtml\Data $adminHelper,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\StoreManager $storeManager
    ) {
        $this->_resLogFactory = $resLogFactory;
        $this->_helper = $helper;
        $this->_adminHelper = $adminHelper;
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
    }

    /**
     * Delete Unnecessary logged attempts
     *
     * @return \Magento\Captcha\Model\Observer
     */
    public function deleteOldAttempts()
    {
        $this->_getResourceModel()->deleteOldAttempts();
        return $this;
    }

    /**
     * Delete Expired Captcha Images
     *
     * @return \Magento\Captcha\Model\Observer
     */
    public function deleteExpiredImages()
    {
        foreach ($this->_storeManager->getWebsites(true) as $website) {
            $helper = $website->getIsDefault() ? $this->_adminHelper : $this->_helper;
            $expire = time() - $helper->getConfig('timeout', $website->getDefaultStore()) * 60;
            $imageDirectory = $helper->getImgDir($website);
            foreach ($this->_filesystem->getNestedKeys($imageDirectory) as $filePath) {
                if ($this->_filesystem->isFile($filePath)
                    && pathinfo($filePath, PATHINFO_EXTENSION) == 'png'
                    && $this->_filesystem->getMTime($filePath) < $expire) {
                    $this->_filesystem->delete($filePath);
                }
            }
        }
        return $this;
    }

    /**
     * Get resource model
     *
     * @return \Magento\Captcha\Model\Resource\Log
     */
    protected function _getResourceModel()
    {
        return $this->_resLogFactory->create();
    }

}

