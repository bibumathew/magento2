<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog product media config
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magento_Catalog_Model_Product_Media_Config implements Magento_Media_Model_Image_Config_Interface
{
    /**
     * Dir
     *
     * @var Magento_Core_Model_Dir
     */
    protected $_dir;

    /**
     * Store manager
     *
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Core_Model_Dir $dir
     */
    public function __construct(
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Core_Model_Dir $dir
    ) {
        $this->_storeManager = $storeManager;
        $this->_dir = $dir;
    }

    /**
     * Filesystem directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return 'catalog' . DIRECTORY_SEPARATOR . 'product';
    }

    /**
     * Web-based directory path of product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaUrlAddition()
    {
        return 'catalog/product';
    }

    /**
     * Filesystem directory path of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaPathAddition()
    {
        return 'tmp' . DIRECTORY_SEPARATOR . $this->getBaseMediaPathAddition();
    }

    /**
     * Web-based directory path of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseTmpMediaUrlAddition()
    {
        return 'tmp/' . $this->getBaseMediaUrlAddition();
    }

    public function getBaseMediaPath()
    {
        return $this->_dir->getDir(Magento_Core_Model_Dir::MEDIA) . DIRECTORY_SEPARATOR
            . 'catalog' . DIRECTORY_SEPARATOR . 'product';
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(Magento_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
    }

    public function getBaseTmpMediaPath()
    {
        return $this->_dir->getDir(Magento_Core_Model_Dir::MEDIA) . DIRECTORY_SEPARATOR
            . $this->getBaseTmpMediaPathAddition();
    }

    public function getBaseTmpMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(Magento_Core_Model_Store::URL_TYPE_MEDIA) . $this->getBaseTmpMediaUrlAddition();
    }

    public function getMediaUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            return $this->getBaseMediaUrl() . $file;
        }

        return $this->getBaseMediaUrl() . '/' . $file;
    }

    public function getMediaPath($file)
    {
        $file = $this->_prepareFileForPath($file);

        if(substr($file, 0, 1) == DIRECTORY_SEPARATOR) {
            return $this->getBaseMediaPath() . DIRECTORY_SEPARATOR . substr($file, 1);
        }

        return $this->getBaseMediaPath() . DIRECTORY_SEPARATOR . $file;
    }

    public function getTmpMediaUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpMediaUrl() . '/' . $file;
    }

    /**
     * Part of URL of temporary product images
     * relatively to media folder
     *
     * @return string
     */
    public function getTmpMediaShortUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpMediaUrlAddition() . '/' . $file;
    }

    /**
     * Part of URL of product images relatively to media folder
     *
     * @return string
     */
    public function getMediaShortUrl($file)
    {
        $file = $this->_prepareFileForUrl($file);

        if(substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseMediaUrlAddition() . '/' . $file;
    }

    public function getTmpMediaPath($file)
    {
        $file = $this->_prepareFileForPath($file);

        if(substr($file, 0, 1) == DIRECTORY_SEPARATOR) {
            return $this->getBaseTmpMediaPath() . DIRECTORY_SEPARATOR . substr($file, 1);
        }

        return $this->getBaseTmpMediaPath() . DIRECTORY_SEPARATOR . $file;
    }

    protected function _prepareFileForUrl($file)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', $file);
    }

    protected function _prepareFileForPath($file)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $file);
    }
}
