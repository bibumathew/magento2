<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Pbridge result payment block
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Block_Adminhtml_Sales_Order_Create_Result extends Magento_Adminhtml_Block_Template
{
    /**
     * Pbridge data
     *
     * @var Enterprise_Pbridge_Helper_Data
     */
    protected $_pbridgeData = null;

    /**
     * @param Enterprise_Pbridge_Helper_Data $pbridgeData
     * @param Magento_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Enterprise_Pbridge_Helper_Data $pbridgeData,
        Magento_Backend_Block_Template_Context $context,
        array $data = array()
    ) {
        $this->_pbridgeData = $pbridgeData;
        parent::__construct($context, $data);
    }

    /**
     * Return JSON array of Payment Bridge incoming data
     *
     * @return string
     */
    public function getJsonHiddenPbridgeParams()
    {
        return $this->_coreData->jsonEncode(
            $this->_pbridgeData->getPbridgeParams()
        );
    }
}
