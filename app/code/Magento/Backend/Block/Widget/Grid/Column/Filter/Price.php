<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Range grid column filter
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Magento_Backend_Block_Widget_Grid_Column_Filter_Price extends Magento_Backend_Block_Widget_Grid_Column_Filter_Abstract
{
    /**
     * @var array
     */
    protected $_currencyList = null;

    /**
     * @var Magento_Directory_Model_Currency
     */
    protected $_currencyModel = null;

    /**
     * @var Magento_Directory_Model_Currency_DefaultLocator
     */
    protected $_currencyLocator = null;

    /**
     * @param Magento_Backend_Block_Context $context
     * @param Magento_Core_Model_Resource_Helper_Mysql4 $resourceHelper
     * @param Magento_Directory_Model_Currency $currencyModel
     * @param Magento_Directory_Model_Currency_DefaultLocator $currencyLocator
     * @param array $data
     */
    public function __construct(
        Magento_Backend_Block_Context $context,
        Magento_Core_Model_Resource_Helper_Mysql4 $resourceHelper,
        Magento_Directory_Model_Currency $currencyModel,
        Magento_Directory_Model_Currency_DefaultLocator $currencyLocator,
        array $data = array()
    ) {
        parent::__construct($context, $resourceHelper, $data);
        $this->_currencyModel = $currencyModel;
        $this->_currencyLocator = $currencyLocator;
    }

    /**
     * Retrieve html
     *
     * @return string
     */
    public function getHtml()
    {
        $html  = '<div class="range">';
        $html .= '<div class="range-line">'
            . '<input type="text" name="'
            . $this->_getHtmlName()
            . '[from]" id="' . $this->_getHtmlId() . '_from" placeholder="' . __('From') . '" value="'
            . $this->getEscapedValue('from') . '" class="input-text no-changes"  '
            . $this->getUiId('filter', $this->_getHtmlName(), 'from') . '/></div>';
        $html .= '<div class="range-line">'
            . '<input type="text" name="'
            . $this->_getHtmlName() . '[to]" id="' . $this->_getHtmlId() . '_to" placeholder="' . __('To')
            . '" value="'.$this->getEscapedValue('to')
            . '" class="input-text no-changes" ' . $this->getUiId('filter', $this->_getHtmlName(), 'to') . '/></div>';

        if ($this->getDisplayCurrencySelect()) {
            $html .= '<div class="range-line">' . $this->_getCurrencySelectHtml() . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Retrieve display currency select
     *
     * @return bool|mixed
     */
    public function getDisplayCurrencySelect()
    {
        if (!is_null($this->getColumn()->getData('display_currency_select'))) {
            return $this->getColumn()->getData('display_currency_select');
        } else {
            return true;
        }
    }

    /**
     * Retrieve currency affect
     *
     * @return bool|mixed
     */
    public function getCurrencyAffect()
    {
        if (!is_null($this->getColumn()->getData('currency_affect'))) {
            return $this->getColumn()->getData('currency_affect');
        } else {
            return true;
        }
    }

    /**
     * Retrieve currency select html
     *
     * @return string
     */
    protected function _getCurrencySelectHtml()
    {
        $value = $this->getEscapedValue('currency');
        if (!$value) {
            $value = $this->_getColumnCurrencyCode();
        }

        $html  = '';
        $html .= '<select name="'.$this->_getHtmlName().'[currency]" id="'.$this->_getHtmlId().'_currency">';
        foreach ($this->_getCurrencyList() as $currency) {
            $html .= '<option value="' . $currency . '" '
                . ($currency == $value ? 'selected="selected"' : '').'>' . $currency . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Retrieve list of currencies
     *
     * @return array|null
     */
    protected function _getCurrencyList()
    {
        if (is_null($this->_currencyList)) {
            $this->_currencyList = $this->_currencyModel->getConfigAllowCurrencies();
        }
        return $this->_currencyList;
    }

    /**
     * Retrieve filter value
     *
     * @param null $index
     * @return mixed|null
     */
    public function getValue($index=null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }
        $value = $this->getData('value');
        if ((isset($value['from']) && strlen($value['from']) > 0)
            || (isset($value['to']) && strlen($value['to']) > 0)
        ) {
            return $value;
        }
        return null;
    }

    /**
     * Retrieve filter condition
     *
     * @return array|mixed|null
     */
    public function getCondition()
    {
        $value = $this->getValue();

        if (isset($value['currency']) && $this->getCurrencyAffect()) {
            $displayCurrency = $value['currency'];
        } else {
            $displayCurrency = $this->_getColumnCurrencyCode();
        }
        $rate = $this->_getRate($displayCurrency, $this->_getColumnCurrencyCode());

        if (isset($value['from'])) {
            $value['from'] *= $rate;
        }

        if (isset($value['to'])) {
            $value['to'] *= $rate;
        }

        $this->prepareRates($displayCurrency);
        return $value;
    }

    /**
     * Retrieve column currency code
     *
     * @return string
     */
    protected function _getColumnCurrencyCode()
    {
        return $this->getColumn()->getCurrencyCode()?
            $this->getColumn()->getCurrencyCode() : $this->_currencyLocator->getDefaultCurrency($this->_request);
    }

    /**
     * Get currency rate
     *
     * @param $fromRate
     * @param $toRate
     * @return float
     */
    protected function _getRate($fromRate, $toRate)
    {
        return $this->_currencyModel->load($fromRate)->getAnyRate($toRate);
    }

    /**
     * Prepare currency rates
     *
     * @param $displayCurrency
     */
    public function prepareRates($displayCurrency)
    {
        $storeCurrency = $this->_getColumnCurrencyCode();

        $rate = $this->_getRate($storeCurrency, $displayCurrency);
        if ($rate) {
            $this->getColumn()->setRate($rate);
            $this->getColumn()->setCurrencyCode($displayCurrency);
        }
    }
}
