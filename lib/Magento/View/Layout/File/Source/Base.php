<?php
/**
 * Source of base layout files introduced by modules
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Layout\File\Source;

use Magento\View\Layout\File\SourceInterface;
use Magento\View\Design\ThemeInterface;
use Magento\Core\Model\Dir;
use Magento\Filesystem;
use Magento\View\Layout\File\Factory;

class Base implements SourceInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Dir
     */
    private $dirs;

    /**
     * @var Factory
     */
    private $fileFactory;

    /**
     * @param Filesystem $filesystem
     * @param Dir $dirs
     * @param Factory $fileFactory
     */
    public function __construct(
        Filesystem $filesystem,
        Dir $dirs,
        Factory $fileFactory
    ) {
        $this->filesystem = $filesystem;
        $this->dirs = $dirs;
        $this->fileFactory = $fileFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFiles(ThemeInterface $theme, $filePath = '*')
    {
        $namespace = $module = '*';
        $area = $theme->getArea();
        $files = $this->filesystem->searchKeys(
            $this->dirs->getDir(Dir::MODULES),
            "{$namespace}/{$module}/view/{$area}/layout/{$filePath}.xml"
        );
        $result = array();
        foreach ($files as $filename) {
            $moduleDir = dirname(dirname(dirname(dirname($filename))));
            $module = basename($moduleDir);
            $namespace = basename(dirname($moduleDir));
            $moduleFull = "{$namespace}_{$module}";
            $result[] = $this->fileFactory->create($filename, $moduleFull);
        }
        return $result;
    }
}
