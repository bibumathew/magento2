<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Directory
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Currency dropdown block
 *
 * @category   Magento
 * @package    Magento_Directory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Directory\Block;

class Currency extends \Magento\Core\Block\Template
{
    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;
    
    /**
     * Directory url
     *
     * @var \Magento\Directory\Helper\Url
     */
    protected $_directoryUrl = null;

    /**
     * @param \Magento\Directory\Helper\Url $directoryUrl
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Helper\Url $directoryUrl,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_directoryUrl = $directoryUrl;
        $this->_storeManager = $storeManager;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Retrieve count of currencies
     * Return 0 if only one currency
     *
     * @return int
     */
    public function getCurrencyCount()
    {
        return count($this->getCurrencies());
    }

    /**
     * Retrieve currencies array
     * Return array: code => currency name
     * Return empty array if only one currency
     *
     * @return array
     */
    public function getCurrencies()
    {
        $currencies = $this->getData('currencies');
        if (is_null($currencies)) {
            $currencies = array();
            $codes = \Mage::app()->getStore()->getAvailableCurrencyCodes(true);
            if (is_array($codes) && count($codes) > 1) {
                $rates = \Mage::getModel('Magento\Directory\Model\Currency')->getCurrencyRates(
                    \Mage::app()->getStore()->getBaseCurrency(),
                    $codes
                );

                foreach ($codes as $code) {
                    if (isset($rates[$code])) {
                        $currencies[$code] = \Mage::app()->getLocale()
                            ->getTranslation($code, 'nametocurrency');
                    }
                }
            }

            $this->setData('currencies', $currencies);
        }
        return $currencies;
    }

    /**
     * Retrieve Currency Swith URL
     *
     * @return string
     */
    public function getSwitchUrl()
    {
        return $this->getUrl('directory/currency/switch');
    }

    /**
     * Return URL for specified currency to switch
     *
     * @param string $code Currency code
     * @return string
     */
    public function getSwitchCurrencyUrl($code)
    {
        return $this->_directoryUrl->getSwitchCurrencyUrl(array('currency' => $code));
    }

    /**
     * Retrieve Current Currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        if (is_null($this->_getData('current_currency_code'))) {
            // do not use \Mage::app()->getStore()->getCurrentCurrencyCode() because of probability
            // to get an invalid (without base rate) currency from code saved in session
            $this->setData('current_currency_code', \Mage::app()->getStore()->getCurrentCurrency()->getCode());
        }

        return $this->_getData('current_currency_code');
    }

    /**
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
