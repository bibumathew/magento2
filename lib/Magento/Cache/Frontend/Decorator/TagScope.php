<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Cache frontend decorator that limits the cleaning scope within a tag
 */
class Magento_Cache_Frontend_Decorator_TagScope extends Magento_Cache_Frontend_Decorator_Bare
{
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    private $_tag;

    /**
     * @param Magento_Cache_FrontendInterface $frontend
     * @param string $tag Cache tag name
     */
    public function __construct(Magento_Cache_FrontendInterface $frontend, $tag)
    {
        parent::__construct($frontend);
        $this->_tag = $tag;
    }

    /**
     * Retrieve cache tag name
     *
     * @return string
     */
    public function getTag()
    {
        return $this->_tag;
    }

    /**
     * Enforce marking with a tag
     *
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = array(), $lifeTime = null)
    {
        $tags[] = $this->_tag;
        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * Limit the cleaning scope within a tag
     *
     * {@inheritdoc}
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, array $tags = array())
    {
        if ($mode == Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
            $result = false;
            foreach ($tags as $tag) {
                if (parent::clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($tag, $this->_tag))) {
                    $result = true;
                }
            }
        } else {
            if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
                $mode = Zend_Cache::CLEANING_MODE_MATCHING_TAG;
                $tags = array($this->_tag);
            } else {
                $tags[] = $this->_tag;
            }
            $result = parent::clean($mode, $tags);
        }
        return $result;
    }
}