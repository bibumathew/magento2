<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Filesystem\Directory;

use Magento\Filesystem\FilesystemException;

class Read implements ReadInterface
{
    /**
     * Directory path
     *
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * File factory
     *
     * @var \Magento\Filesystem\File\ReadFactory
     */
    protected $fileFactory;

    /**
     * Filesystem driver
     *
     * @var \Magento\Filesystem\DriverInterface
     */
    protected $driver;

    /**
     * @var \Magento\Filesystem\WrapperFactory
     */
    protected $wrapperFactory;

    /**
     * List wrappers that can have file path
     *
     * @var array
     */
    protected $fileWrappers = array(
        \Magento\Filesystem::WRAPPER_STREAM_FILE,
        \Magento\Filesystem::WRAPPER_STREAM_GLOB
    );

    /**
     * Constructor. Set properties.
     *
     * @param array $config
     * @param \Magento\Filesystem\File\ReadFactory $fileFactory
     * @param \Magento\Filesystem\DriverInterface $driver
     */
    public function __construct
    (
        array $config,
        \Magento\Filesystem\File\ReadFactory $fileFactory,
        \Magento\Filesystem\DriverInterface $driver
    ) {
        $this->setProperties($config);
        $this->fileFactory = $fileFactory;
        $this->driver = $driver;
    }

    /**
     * Set properties from config
     *
     * @param array $config
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function setProperties(array $config)
    {
        if (!empty($config['path'])) {
            $this->path = rtrim($this->fixSeparator($config['path']), '/') . '/';
        }
        if (!empty($config['protocol'])) {
            $this->scheme = $config['protocol'];
        }
    }

    /**
     * Retrieves absolute path
     * E.g.: /var/www/application/file.txt
     *
     * @param string $path
     * @param string $schema
     * @return string
     */
    public function getAbsolutePath($path = null, $schema = null)
    {
        return $this->getScheme($schema) . $this->path . ltrim($this->fixSeparator($path), '/');
    }

    /**
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     */
    protected function getScheme($scheme = null)
    {
        $scheme = $scheme ?: $this->scheme;
        return $scheme ? $scheme . '://' : '';
    }

    /**
     * Retrieves relative path
     *
     * @param string $path
     * @return string
     */
    public function getRelativePath($path = null)
    {
        $path = $this->fixSeparator($path);
        if ((strpos($path, $this->path) === 0) || ($this->path == $path . '/')) {
            $result = substr($path, strlen($this->path));
        } else {
            $result = $path;
        }
        return $result;
    }
    /**
     * Validate of path existence
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function assertExist($path)
    {
        $absolutePath = $this->getAbsolutePath($path);
        if ($this->driver->isExists($absolutePath) === false) {
            throw new FilesystemException(sprintf('The path "%s" doesn\'t exist', $absolutePath));
        }
        return true;
    }

    /**
     * Retrieve list of all entities in given path
     *
     * @param string|null $path
     * @return array
     */
    public function read($path = null)
    {
        $this->assertExist($path);

        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($this->getAbsolutePath($path), $flags);
        $result = array();
        /** @var \FilesystemIterator $file */
        foreach ($iterator as $file) {
            $result[] = $this->getRelativePath($file->getPathname());
        }
        return $result;
    }

    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string $path [optional]
     * @return array
     */
    public function search($pattern, $path = null)
    {
        clearstatcache();
        if ($path) {
            $absolutePath = $this->getAbsolutePath($this->getRelativePath($path));
        } else {
            $absolutePath = $this->path;
        }

        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolutePath, $flags), \RecursiveIteratorIterator::CHILD_FIRST
            ),
            $pattern
        );
        $result = array();
        /** @var \FilesystemIterator $file */
        foreach ($iterator as $file) {
            $result[] = $this->getRelativePath($file->getPathname());
        }
        return $result;
    }

    /**
     * Check a file or directory exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function isExist($path = null)
    {
        return $this->driver->isExists($this->getAbsolutePath($path));
    }

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function stat($path)
    {
        $this->assertExist($path);
        return $this->driver->stat($this->getAbsolutePath($path));
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function isReadable($path)
    {
        return $this->driver->isReadable($this->getAbsolutePath($path));
    }

    /**
     * Open file in read mode
     *
     * @param string $path
     * @param string|null $protocol
     *
     * @return \Magento\Filesystem\File\ReadInterface
     */
    public function openFile($path, $protocol = null)
    {
        return $this->fileFactory->create($this->getAbsolutePath($path), $this->driver, $protocol);
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $protocol
     * @return string
     * @throws FilesystemException
     */
    public function readFile($path, $protocol = null)
    {
        $absolutePath = $this->getAbsolutePath($path, $protocol);

        /** @var \Magento\Filesystem\File\Read $fileReader */
        $fileReader = $this->fileFactory->create($absolutePath, $this->driver, $protocol);
        return $fileReader->readAll();

        if (!$this->driver->isFile($absolutePath)) {
            throw new FilesystemException(
                sprintf('The file "%s" either doesn\'t exist or not a file', $absolutePath)
            );
        }
        return $this->driver->fileGetContents($absolutePath);
    }

    /**
     * Check whether given path is file
     *
     * @param string $path
     * @return bool
     */
    public function isFile($path)
    {
        return $this->driver->isFile($this->getAbsolutePath($path));
    }

    /**
     * Check whether given path is directory
     *
     * @param string $path
     * @return bool
     */
    public function isDirectory($path)
    {
        return $this->driver->isDirectory($this->getAbsolutePath($path));
    }

    /**
     * Checks is directory contains path
     * Utility method.
     *
     * @param string $path
     * @param string $directory
     * @return bool
     */
    public function isPathInDirectory($path, $directory)
    {
        return 0 === strpos($this->fixSeparator($path), $this->fixSeparator($directory));
    }

    /**
     * Fixes path separator
     * Utility method.
     *
     * @param string $path
     * @return string
     */
    protected function fixSeparator($path)
    {
        return str_replace('\\', '/', $path);
    }
}
