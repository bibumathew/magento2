<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Asset;

/**
 * Minified page asset
 */
class Minified implements MergeableInterface
{

    /**
     * @var LocalInterface
     */
    protected $originalAsset;

    /**
     * @var \Magento\Code\Minifier
     */
    protected $minifier;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var \Magento\View\Url
     */
    protected $viewUrl;

    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @param LocalInterface $asset
     * @param \Magento\Code\Minifier $minifier
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\Logger $logger
     */
    public function __construct(
        LocalInterface $asset,
        \Magento\Code\Minifier $minifier,
        \Magento\View\Url $viewUrl,
        \Magento\Logger $logger
    ) {
        $this->originalAsset = $asset;
        $this->minifier = $minifier;
        $this->viewUrl = $viewUrl;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (empty($this->url)) {
            $this->process();
        }
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->originalAsset->getContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        if (empty($this->file)) {
            $this->process();
        }
        return $this->file;
    }

    /**
     * Minify content of child asset
     */
    protected function process()
    {
        $originalFile = $this->originalAsset->getSourceFile();

        try {
            $this->file = $this->minifier->getMinifiedFile($originalFile);
        } catch (\Exception $e) {
            $this->logger->logException(new \Magento\Exception('Could not minify file: ' . $originalFile, 0, $e));
            $this->file = $originalFile;
        }
        if ($this->file == $originalFile) {
            $this->url = $this->originalAsset->getUrl();
        } else {
            $this->url = $this->viewUrl->getPublicFileUrl($this->file);
        }
    }
}
