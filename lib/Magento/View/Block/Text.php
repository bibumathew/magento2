<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\View\Block;

class Text extends \Magento\View\Block\AbstractBlock
{
    public function setText($text)
    {
        $this->setData('text', $text);
        return $this;
    }

    public function getText()
    {
        return $this->getData('text');
    }

    public function addText($text, $before=false)
    {
        if ($before) {
            $this->setText($text.$this->getText());
        } else {
            $this->setText($this->getText().$text);
        }
    }

    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }

        return $this->getText();
    }
}
