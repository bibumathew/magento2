<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CatalogEvent
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog Events grid bitmask renderer
 *
 * @category   Magento
 * @package    Magento_CatalogEvent
 */
namespace Magento\CatalogEvent\Block\Adminhtml\Event\Grid\Column\Renderer;

class Bitmask extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public function render(\Magento\Object $row)
    {
        $value = (int) $row->getData($this->getColumn()->getIndex());
        $result = array();
        foreach ($this->getColumn()->getOptions() as $option) {
            if (($value & $option['value']) == $option['value']) {
                $result[] = $option['label'];
            }
        }

        return $this->escapeHtml(implode(', ', $result));
    }
}
