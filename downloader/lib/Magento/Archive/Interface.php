<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     \Magento\Archive
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Interface for work with archives
 *
 * @category    Magento
 * @package     \Magento\Archive
 * @author      Magento Core Team <core@magentocommerce.com>
 */
interface \Magento\Archive\ArchiveInterface
{
    /**
    * Pack file or directory.
    *
    * @param string $source
    * @param string $destination
    * @return string
    */
    public function pack($source, $destination);

    /**
    * Unpack file or directory.
    *
    * @param string $source
    * @param string $destination
    * @return string
    */
    public function unpack($source, $destination);
}
