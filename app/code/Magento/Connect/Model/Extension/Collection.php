<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Extension packages files collection
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Model\Extension;

class Collection extends \Magento\Data\Collection\Filesystem
{
    /**
     * Files and folders regexsp
     *
     * @var string
     */
    protected $_allowedDirsMask     = '/^[a-z0-9\.\-]+$/i';
    protected $_allowedFilesMask    = '/^[a-z0-9\.\-\_]+\.(xml|ser)$/i';
    protected $_disallowedFilesMask = '/^package\.xml$/i';

    /**
     * Base dir where packages are located
     *
     * @var string
     */
    protected $_baseDir = '';

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\App\Dir $dirs
     */
    public function __construct(\Magento\Core\Model\EntityFactory $entityFactory, \Magento\App\Dir $dirs)
    {
        parent::__construct($entityFactory);
        $this->_baseDir = $dirs->getDir('var') . DS . 'connect';
        $io = new \Magento\Io\File();
        $io->setAllowCreateFolders(true)->createDestinationDir($this->_baseDir);
        $this->addTargetDir($this->_baseDir);
    }

    /**
     * Row generator
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $row = parent::_generateRow($filename);
        $row['package'] = preg_replace('/\.(xml|ser)$/', '', str_replace($this->_baseDir . DS, '', $filename));
        $row['filename_id'] = $row['package'];
        $folder = explode(DS, $row['package']);
        array_pop($folder);
        $row['folder'] = DS;
        if (!empty($folder)) {
            $row['folder'] = implode(DS, $folder) . DS;
        }
        return $row;
    }

    /**
     * Get all folders as options array
     *
     * @return array
     */
    public function collectFolders()
    {
        $collectFiles = $this->_collectFiles;
        $collectDirs = $this->_collectDirs;
        $this->setCollectFiles(false)->setCollectDirs(true);

        $this->_collectRecursive($this->_baseDir);
        $result = array(DS => DS);
        foreach ($this->_collectedDirs as $dir) {
            $dir = str_replace($this->_baseDir . DS, '', $dir) . DS;
            $result[$dir] = $dir;
        }

        $this->setCollectFiles($collectFiles)->setCollectDirs($collectDirs);
        return $result;
    }

}
