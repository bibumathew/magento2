<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Theme factory
 */
namespace Magento\View\Design\Theme;

class FlyweightFactory
{
    /**
     * @var ThemeProviderInterface
     */
    protected $dataProvider;

    /**
     * @var \Magento\View\Design\ThemeInterface[]
     */
    protected $themes = array();

    /**
     * @var \Magento\View\Design\ThemeInterface[]
     */
    protected $themesByPath = array();

    /**
     * @param ThemeProviderInterface $dataProvider
     */
    public function __construct(
        ThemeProviderInterface $dataProvider
    ) {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Creates or returns a shared model of theme
     *
     * @param string|int $themeKey
     * @param string $area
     * @return \Magento\View\Design\ThemeInterface|null
     * @throws \InvalidArgumentException
     */
    public function create($themeKey, $area = \Magento\View\DesignInterface::DEFAULT_AREA)
    {
        if (is_numeric($themeKey)) {
            $themeModel = $this->_loadById($themeKey);
        } elseif (is_string($themeKey)) {
            $themeModel = $this->_loadByPath($themeKey, $area);
        } else {
            throw new \InvalidArgumentException('Incorrect theme identification key');
        }
        if (!$themeModel->getId()) {
            return null;
        }
        $this->_addTheme($themeModel);
        return $themeModel;
    }

    /**
     * Load theme by id
     *
     * @param int $themeId
     * @return \Magento\View\Design\ThemeInterface
     */
    protected function _loadById($themeId)
    {
        if (isset($this->themes[$themeId])) {
            return $this->themes[$themeId];
        }

        return $this->dataProvider->getById($themeId);
    }

    /**
     * Load theme by theme path
     *
     * @param string $themePath
     * @param string $area
     * @return \Magento\View\Design\ThemeInterface
     */
    protected function _loadByPath($themePath, $area)
    {
        $fullPath = $area . \Magento\View\Design\ThemeInterface::PATH_SEPARATOR . $themePath;
        if (isset($this->themesByPath[$fullPath])) {
            return $this->themesByPath[$fullPath];
        }

        return $this->dataProvider->getByFullPath($fullPath);
    }

    /**
     * Add theme to shared collection
     *
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @return FlyweightFactory
     */
    protected function _addTheme(\Magento\View\Design\ThemeInterface $themeModel)
    {
        if ($themeModel->getId()) {
            $this->themes[$themeModel->getId()] = $themeModel;
            $themePath = $themeModel->getFullPath();
            if ($themePath) {
                $this->themesByPath[$themePath] = $themeModel;
            }
        }
        return $this;
    }
}
