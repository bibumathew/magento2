<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Rma_Block_Return_Tracking_Package extends Magento_Shipping_Block_Tracking_Popup
{
    /**
     * @var Magento_Rma_Helper_Data
     */
    protected $_rmaData;

    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Magento_Rma_Helper_Data $rmaData
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Core_Block_Template_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_Core_Model_LocaleInterface $locale
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Magento_Rma_Helper_Data $rmaData,
        Magento_Core_Helper_Data $coreData,
        Magento_Core_Block_Template_Context $context,
        Magento_Core_Model_Registry $registry,
        Magento_Core_Model_LocaleInterface $locale,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->_rmaData = $rmaData;
        $this->_storeManager = $storeManager;
        parent::__construct($coreData, $context, $registry, $locale, $data);
    }

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setPackageInfo($this->_coreRegistry->registry('rma_package_shipping'));
    }

    /**
     * Get packages of RMA
     *
     * @return array
     */
    public function getPackages()
    {
        return unserialize($this->getPackageInfo()->getPackages());
    }

    /**
     * Print package url for creating pdf
     *
     * @return string
     */
    public function getPrintPackageUrl()
    {
        $data['hash'] = $this->getRequest()->getParam('hash');
        return $this->getUrl('*/*/packageprint', $data);
    }

    /**
     * Return name of container type by its code
     *
     * @param string $code
     * @return string
     */
    public function getContainerTypeByCode($code)
    {
        $carrierCode = $this->getPackageInfo()->getCarrierCode();
        $carrier = $this->_rmaData->getCarrier($carrierCode, $this->_storeManager->getStore()->getId());
        if ($carrier) {
            $containerTypes = $carrier->getContainerTypes();
            $containerType = !empty($containerTypes[$code]) ? $containerTypes[$code] : '';
            return $containerType;
        }
        return '';
    }
}
