<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * RMA Return Block
 *
 * @category    Magento
 * @package     Magento_Rma
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rma\Block;

class Link extends \Magento\Page\Block\Link\Current
{
    /**
     * @var Magento_Rma_Helper_Data
     *
     * @var \Magento\Rma\Helper\Data
     */
    protected $_rmaHelper = null;

    /**
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param Magento_Rma_Helper_Data $rmaHelper
     * @param array $data
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Magento_Rma_Helper_Data $rmaHelper,
        array $data = array()
    ) {
        parent::__construct($coreData, $context, $data);
        $this->_rmaHelper = $rmaHelper;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if ($this->_rmaHelper->isEnabled()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
