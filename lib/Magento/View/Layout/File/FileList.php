<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Unordered list of layout file instances with awareness of layout file identity
 */
namespace Magento\View\Layout\File;

class FileList
{
    /**
     * @var \Magento\View\Layout\File[]
     */
    private $_files = array();

    /**
     * Retrieve all layout file instances
     *
     * @return \Magento\View\Layout\File[]
     */
    public function getAll()
    {
        return array_values($this->_files);
    }

    /**
     * Add layout file instances to the list, preventing identity coincidence
     *
     * @param \Magento\View\Layout\File[] $files
     * @throws \LogicException
     */
    public function add(array $files)
    {
        foreach ($files as $file) {
            $identifier = $this->_getFileIdentifier($file);
            if (array_key_exists($identifier, $this->_files)) {
                $filename = $this->_files[$identifier]->getFilename();
                throw new \LogicException(
                    "Layout file '{$file->getFilename()}' is indistinguishable from the file '{$filename}'."
                );
            }
            $this->_files[$identifier] = $file;
        }
    }

    /**
     * Replace already added layout files with specified ones, checking for identity match
     *
     * @param \Magento\View\Layout\File[] $files
     * @throws \LogicException
     */
    public function replace(array $files)
    {
        foreach ($files as $file) {
            $identifier = $this->_getFileIdentifier($file);
            if (!array_key_exists($identifier, $this->_files)) {
                throw new \LogicException(
                    "Overriding layout file '{$file->getFilename()}' does not match to any of the files."
                );
            }
            $this->_files[$identifier] = $file;
        }
    }

    /**
     * Calculate unique identifier for a layout file
     *
     * @param \Magento\View\Layout\File $file
     * @return string
     */
    protected function _getFileIdentifier(\Magento\View\Layout\File $file)
    {
        $theme = ($file->getTheme() ? 'theme:' . $file->getTheme()->getFullPath() : 'base');
        return $theme . '|module:' . $file->getModule() . '|file:' . $file->getName();
    }
}
