<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_GoogleShopping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Tax attribute model
 *
 * @category   Magento
 * @package    Magento_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magento_GoogleShopping_Model_Attribute_Tax extends Magento_GoogleShopping_Model_Attribute_Default
{
    /**
     * Maximum number of tax rates per product supported by google shopping api
     */
    const RATES_MAX = 100;

    /**
     * @var Magento_Tax_Helper_Data|null
     */
    protected $_taxData = null;

    /**
     * Config
     *
     * @var Magento_GoogleShopping_Model_Config
     */
    protected $_config;

    /**
     * @param Magento_Catalog_Model_ProductFactory $productFactory
     * @param Magento_GoogleShopping_Model_Config $config
     * @param Magento_Tax_Helper_Data $taxData
     * @param Magento_GoogleShopping_Helper_Data $gsData
     * @param Magento_GoogleShopping_Helper_Product $gsProduct
     * @param Magento_GoogleShopping_Helper_Price $gsPrice
     * @param Magento_Core_Model_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_GoogleShopping_Model_Resource_Attribute $resource
     * @param Magento_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Magento_Catalog_Model_ProductFactory $productFactory,
        Magento_GoogleShopping_Model_Config $config,
        Magento_Tax_Helper_Data $taxData,
        Magento_GoogleShopping_Helper_Data $gsData,
        Magento_GoogleShopping_Helper_Product $gsProduct,
        Magento_GoogleShopping_Helper_Price $gsPrice,
        Magento_Core_Model_Context $context,
        Magento_Core_Model_Registry $registry,
        Magento_GoogleShopping_Model_Resource_Attribute $resource,
        Magento_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_config = $config;
        $this->_taxData = $taxData;
        parent::__construct($productFactory, $gsData, $gsProduct, $gsPrice, $context, $resource, $resource,
            $resourceCollection, $data);
    }

    /**
     * Set current attribute to entry (for specified product)
     *
     * @param Magento_Catalog_Model_Product $product
     * @param Magento_Gdata_Gshopping_Entry $entry
     * @return Magento_Gdata_Gshopping_Entry
     */
    public function convertAttribute($product, $entry)
    {
        $entry->cleanTaxes();
        if ($this->_taxData->getConfig()->priceIncludesTax()) {
            return $entry;
        }

        $calc = $this->_taxData->getCalculator();
        $customerTaxClass = $calc->getDefaultCustomerTaxClass($product->getStoreId());
        $rates = $calc->getRatesByCustomerAndProductTaxClasses($customerTaxClass, $product->getTaxClassId());
        $targetCountry = $this->_config->getTargetCountry($product->getStoreId());
        $ratesTotal = 0;
        foreach ($rates as $rate) {
            if ($targetCountry == $rate['country']) {
                $regions = $this->_parseRegions($rate['state'], $rate['postcode']);
                $ratesTotal += count($regions);
                if ($ratesTotal > self::RATES_MAX) {
                    throw new Magento_Core_Exception(__("Google shopping only supports %1 tax rates per product", self::RATES_MAX));
                }
                foreach ($regions as $region) {
                    $entry->addTax(array(
                        'tax_rate' =>     $rate['value'] * 100,
                        'tax_country' =>  empty($rate['country']) ? '*' : $rate['country'],
                        'tax_region' =>   $region
                    ));
                }
            }
        }

        return $entry;
    }

    /**
     * Retrieve array of regions characterized by provided params
     *
     * @param string $state
     * @param string $zip
     * @return array
     */
    protected function _parseRegions($state, $zip)
    {
        return (!empty($zip) && $zip != '*') ? $this->_parseZip($zip) : (($state) ? array($state) : array('*'));
    }

    /**
     * Retrieve array of regions characterized by provided zip code
     *
     * @param string $zip
     * @return array
     */
    protected function _parseZip($zip)
    {
        if (strpos($zip, '-') == -1) {
            return array($zip);
        } else {
            return $this->_gsData->zipRangeToZipPattern($zip);
        }
    }
}
