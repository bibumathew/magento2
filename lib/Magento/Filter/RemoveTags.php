<?php
/**
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */

namespace Magento\Filter;

/**
 * Remove tags from string
 */
class RemoveTags implements \Zend_Filter_Interface
{
    /**
     * Convert html entities
     *
     * @param array $matches
     * @return string
     */
    protected function _convertEntities($matches)
    {
        return htmlentities($matches[0]);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $value = preg_replace_callback(
            "# <(?![/a-z]) | (?<=\s)>(?![a-z]) #xi",
            array($this, '_convertEntities'),
            $value
        );
        $value = strip_tags($value);
        return htmlspecialchars_decode($value);
    }
}
