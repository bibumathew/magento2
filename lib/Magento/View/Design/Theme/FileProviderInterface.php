<?php
/**
 * {license_notice}
 *   
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\View\Design\Theme;

interface FileProviderInterface
{
    /**
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param array $filters
     * @return \Magento\View\Design\Theme\FileInterface[]
     */
    public function getItems(\Magento\View\Design\ThemeInterface $theme, array $filters  = array());
}