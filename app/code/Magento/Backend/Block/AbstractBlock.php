<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Backend\Block;

/**
 * Backend abstract block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractBlock extends \Magento\View\Block\AbstractBlock
{
    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = array()
    ) {
        \Magento\View\Block\parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }
}
