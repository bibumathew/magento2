<?php
/**
 * Language switcher
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
namespace Magento\Page\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

class Switcher extends Block
{
    /**
     * Select store
     *
     * @param string $name
     */
    public function selectStoreView($name)
    {
        $this->_rootElement->find('button', LOCATOR::SELECTOR_TAG_NAME)->click();
        $this->_rootElement->find($name, Locator::SELECTOR_LINK_TEXT)->click();
    }
} 