<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Core_Model_View_Publisher
{
    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Helper to process css content
     *
     * @var Mage_Core_Helper_Css
     */
    protected $_cssHelper;

    /**
     * @var Mage_Core_Model_View_Service
     */
    protected $_viewService;

    /**
     * @var Mage_Core_Model_View_FileSystem
     */
    protected $_viewFileSystem;

    /**
     * View files publisher model
     *
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Helper_Css $cssHelper
     * @param Mage_Core_Model_View_Service $viewService
     * @param Mage_Core_Model_View_FileSystem $viewFileSystem
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Helper_Css $cssHelper,
        Mage_Core_Model_View_Service $viewService,
        Mage_Core_Model_View_FileSystem $viewFileSystem
    ) {
        $this->_filesystem = $filesystem;
        $this->_cssHelper = $cssHelper;
        $this->_viewService = $viewService;
        $this->_viewFileSystem = $viewFileSystem;
    }

    /**
     * Check, if requested theme file has public access, and move it to public folder, if the file has no public access
     *
     * @param  string $filePath
     * @param  array $params
     * @return string
     * @throws Magento_Exception
     */
    public function getPublishedFilePath($filePath, $params)
    {
        if (!$this->_viewService->isViewFileOperationAllowed()) {
            throw new Magento_Exception('Filesystem operations are not permitted for view files');
        }

        $sourcePath = $this->_viewFileSystem->getViewFile($filePath, $params);

        if (!$this->_filesystem->has($sourcePath)) {
            throw new Magento_Exception("Unable to locate theme file '{$sourcePath}'.");
        }
        if (!$this->_needToProcessFile($sourcePath)) {
            return $sourcePath;
        }

        return $this->_publishFile($filePath, $params, $sourcePath);
    }

    /**
     * Publish file identified by $fileId basing on information about parent file path and name.
     *
     * The method is public only because PHP 5.3 does not permit usage of protected methods inside the closures,
     * even if a closure is created in the same class. The method is not intended to be used by a client of this class.
     * If you ever need to call this method externally, then ensure you have a good reason for it. As such the method
     * would need to be added to the class's interface and proxy.
     *
     * @param string $fileId URL to the file that was extracted from $parentFilePath
     * @param string $parentFilePath path to the file
     * @param string $parentFileName original file name identifier that was requested for processing
     * @param array $params theme/module parameters array
     * @return string
     */
    public function publishRelatedViewFile($fileId, $parentFilePath, $parentFileName, $params)
    {
        $relativeThemeFilePath = $this->_getRelatedViewFile($fileId, $parentFilePath, $parentFileName, $params);

        return $this->getPublishedFilePath($relativeThemeFilePath, $params);
    }

    protected function _publishFile($filePath, $params, $sourcePath)
    {
        $targetPath = $this->_buildPublishedFilePath($filePath, $params, $sourcePath);

        /* Validate whether file needs to be published */
        $isCssFile = $this->_getExtension($filePath) == Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS;
        if ($isCssFile) {
            $cssContent = $this->_getPublicCssContent($sourcePath, $targetPath, $filePath, $params);
        }

        $fileMTime = $this->_filesystem->getMTime($sourcePath);
        if (!$this->_filesystem->has($targetPath) || $fileMTime != $this->_filesystem->getMTime($targetPath)) {
            $publicDir = dirname($targetPath);
            if (!$this->_filesystem->isDirectory($publicDir)) {
                $this->_filesystem->createDirectory($publicDir, 0777);
            }

            if (isset($cssContent)) {
                $this->_filesystem->write($targetPath, $cssContent);
                $this->_filesystem->touch($targetPath, $fileMTime);
            } elseif ($this->_filesystem->isFile($sourcePath)) {
                $this->_filesystem->copy($sourcePath, $targetPath);
                $this->_filesystem->touch($targetPath, $fileMTime);
            } elseif (!$this->_filesystem->isDirectory($targetPath)) {
                $this->_filesystem->createDirectory($targetPath, 0777);
            }
        }

        $this->_viewFileSystem->notifyViewFileLocationChanged($targetPath, $filePath, $params);
        return $targetPath;
    }

    protected function _buildPublishedFilePath($filePath, $params, $sourcePath)
    {
        $allowPublication = (string)Mage::getConfig()->getNode(
            Mage_Core_Model_Design_Package::XML_PATH_ALLOW_DUPLICATION
        );
        $isCssFile = $this->_getExtension($filePath) == Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS;
        if ($allowPublication || $isCssFile) {
            $targetPath = $this->_buildPublicViewRedundantFilename($filePath, $params);
        } else {
            $targetPath = $this->_buildPublicViewSufficientFilename($sourcePath, $params);
        }
        $targetPath = $this->_buildPublicViewFilename($targetPath);

        return $targetPath;
    }

    /**
     * Determine whether a file needs to be published.
     * Js files are never processed. All other files must be processed either if they are not published already,
     * or if they are css-files and we're working in developer mode.
     *
     * @param string $filePath
     * @return bool
     */
    protected function _needToProcessFile($filePath)
    {
        $jsPath = Mage::getBaseDir(Mage_Core_Model_Dir::PUB_LIB) . DS;
        if (strncmp($filePath, $jsPath, strlen($jsPath)) === 0) {
            return false;
        }

        $protectedExtensions = array(
            Mage_Core_Model_Design_Package::CONTENT_TYPE_PHP,
            Mage_Core_Model_Design_Package::CONTENT_TYPE_PHTML,
            Mage_Core_Model_Design_Package::CONTENT_TYPE_XML
        );
        if (in_array($this->_getExtension($filePath), $protectedExtensions)) {
            return false;
        }

        $themePath = $this->_viewService->getPublicDir() . DS;
        if (strncmp($filePath, $themePath, strlen($themePath)) !== 0) {
            return true;
        }

        return ($this->_viewService->getAppMode() == Mage_Core_Model_App_State::MODE_DEVELOPER)
            && $this->_getExtension($filePath) == Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS;
    }

    /**
     * Get file extension by file path
     *
     * @param string $filePath
     * @return string
     */
    protected function _getExtension($filePath)
    {
        $dotPosition = strrpos($filePath, '.');
        return strtolower(substr($filePath, $dotPosition + 1));
    }

    /**
     * Build public filename for a theme file that always includes area/package/theme/locate parameters
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    protected function _buildPublicViewRedundantFilename($file, array $params)
    {
        /** @var $theme Mage_Core_Model_Theme */
        $theme = $params['themeModel'];
        if ($theme->getThemePath()) {
            $designPath = str_replace('/', DS, $theme->getThemePath());
        } elseif ($theme->getId()) {
            $designPath = Mage_Core_Model_Design_Package::PUBLIC_THEME_DIR . $theme->getId();
        } else {
            $designPath = Mage_Core_Model_Design_Package::PUBLIC_VIEW_DIR;
        }

        $publicFile = $params['area'] . DS . $designPath . DS . $params['locale'] .
            ($params['module'] ? DS . $params['module'] : '') . DS . $file;

        return $publicFile;
    }

    /**
     * Build public filename for a view file that sufficiently depends on the passed parameters
     *
     * @param string $filename
     * @param array $params
     * @return string
     */
    protected function _buildPublicViewSufficientFilename($filename, array $params)
    {
        $designDir = Mage::getBaseDir(Mage_Core_Model_Dir::THEMES) . DS;
        if (0 === strpos($filename, $designDir)) {
            // theme file
            $publicFile = substr($filename, strlen($designDir));
        } else {
            // modular file
            $module = $params['module'];
            $moduleDir = Mage::getModuleDir('theme', $module) . DS;
            $publicFile = substr($filename, strlen($moduleDir));
            $publicFile = Mage_Core_Model_Design_Package::PUBLIC_MODULE_DIR . DS . $module . DS . $publicFile;
        }
        return $publicFile;
    }

    /**
     * Retrieve processed CSS file content that contains URLs relative to the specified public directory
     *
     * @param string $sourcePath Absolute path to the current location of CSS file
     * @param string $publicPath Absolute path to location of the CSS file, where it will be published
     * @param string $fileName File name used for reference
     * @param array $params Design parameters
     * @return string
     */
    protected function _getPublicCssContent($sourcePath, $publicPath, $fileName, $params)
    {
        $content = $this->_filesystem->read($sourcePath);

        $publisher = $this;
        $callback = function ($fileId, $originalPath) use ($publisher, $fileName, $params) {
            $relatedFilePathPublic = $publisher->publishRelatedViewFile(
                $fileId, $originalPath, $fileName, $params
            );
            return $relatedFilePathPublic;
        };
        try {
            $content = $this->_cssHelper->replaceCssRelativeUrls($content, $sourcePath, $publicPath, $callback);
        } catch (Magento_Exception $e) {
            Mage::logException($e);
        }
        return $content;
    }

    /**
     * Build path to file located in public folder
     *
     * @param string $file
     * @return string
     */
    protected function _buildPublicViewFilename($file)
    {
        return $this->_viewService->getPublicDir() . DS . $file;
    }

    /**
     * Get relative $fileUrl based on information about parent file path and name.
     *
     * @param string $fileId URL to the file that was extracted from $parentFilePath
     * @param string $parentFilePath path to the file
     * @param string $parentFileName original file name identifier that was requested for processing
     * @param array $params theme/module parameters array
     * @return string
     */
    protected function _getRelatedViewFile($fileId, $parentFilePath, $parentFileName, &$params)
    {
        if (strpos($fileId, Mage_Core_Model_Design_Package::SCOPE_SEPARATOR)) {
            $filePath = $this->_viewService->extractScope($fileId, $params);
        } else {
            /* Check if module file overridden on theme level based on _module property and file path */
            if ($params['module'] && strpos($parentFilePath, Mage::getBaseDir(Mage_Core_Model_Dir::THEMES)) === 0) {
                /* Add module directory to relative URL */
                $filePath = dirname($params['module'] . '/' . $parentFileName)
                    . '/' . $fileId;
                $filePath = $this->_filesystem->normalizePath($filePath, true);
                if (strpos($filePath, $params['module']) === 0) {
                    $filePath = ltrim(str_replace($params['module'], '', $filePath), '/');
                } else {
                    $params['module'] = false;
                }
            } else {
                $filePath = $this->_filesystem->normalizePath(dirname($parentFileName) . '/' . $fileId, true);
            }
        }

        return $filePath;
    }
}
