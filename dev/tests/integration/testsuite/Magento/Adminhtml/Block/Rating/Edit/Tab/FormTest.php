<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Rating\Block\Adminhtml\Rating\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(
            'Magento\Rating\Block\Adminhtml\Rating\Edit\Tab\Form',
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\Layout')
                ->createBlock('Magento\Rating\Block\Adminhtml\Rating\Edit\Tab\Form')
        );
    }
}
