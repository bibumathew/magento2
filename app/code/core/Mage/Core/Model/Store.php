<?php
/**
 * Store
 *
 * @package    Mage
 * @subpackage Core
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Core_Model_Store extends Mage_Core_Model_Abstract
{
    protected $_priceFilter;

    protected $_website;

    protected $_configCache = array();

    protected $_dirCache = array();

    protected $_urlCache = array();

    public function __construct()
    {
        parent::__construct();
    }

    protected function _construct()
    {
        $this->_init('core/store');
    }

    public function load($id, $field=null)
    {
        if (!is_numeric($id) && is_null($field)) {
            $this->getResource()->load($this, $id, 'code');
            return $this;
        }
        return parent::load($id, $field);
    }

    public function getId()
    {
        if (is_null(parent::getId())) {
            $this->setId($this->getConfig('system/store/id'));
        }
        return parent::getId();
    }

    public function getConfig($path) {
        if (!isset($this->_configCache[$path])) {

            $config = Mage::getConfig()->getNode('stores/'.$this->getCode().'/'.$path);
            if (!$config) {
                #throw Mage::exception('Mage_Core', 'Invalid store configuration path: '.$path);
                Mage::log('Invalid store configuration path: '.$path);
                return false;
            }
            if (!$config->children()) {
                $value = $this->processSubst((string)$config);
            } else {
                $value = array();
                foreach ($config->children() as $k=>$v) {
                    if ($v->children()) {
                        $value[$k] = $v;
                    } else {
                        $value[$k] = $this->processSubst((string)$v);
                    }
                }
            }
            $this->_configCache[$path] = $value;
        }
        return $this->_configCache[$path];
    }

    /**
     * Enter description here...
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        if (empty($this->_website)) {
            $this->_website = Mage::getModel('core/website')->load($this->getConfig('system/website/id'));
        }
        return $this->_website;
    }

    /**
     * Get store directory by type
     *
     * @param   string $type
     * @return  string
     */
    public function getDir($type)
    {
        if (isset($this->_dirCache[$type])) {
            return $this->_dirCache[$type];
        }
        $dir = $this->getConfig("system/filesystem/$type");
        if (!$dir) {
            $dir = $this->getDefaultDir($type);
        }

        if (!$dir) {
            throw Mage::exception('Mage_Core', 'Invalid base dir type specified: '.$type);
        }

        switch ($type) {
            case 'var': case 'session': case 'cache_config': case 'cache_layout': case 'cache_block':
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                break;
        }

        $dir = str_replace('/', DS, $dir);

        $this->_dirCache[$type] = $dir;

        return $dir;
    }

    public function getDefaultDir($type)
    {
        $dir = Mage::getRoot();
        switch ($type) {
            case 'etc':
                $dir = Mage::getRoot().DS.'etc';
                break;

            case 'code':
                $dir = Mage::getRoot().DS.'code';
                break;

            case 'var':
                $dir = $this->getTempVarDir();
                break;

            case 'session':
                $dir = $this->getDir('var').DS.'session';
                break;

            case 'cache_config':
                $dir = $this->getDir('var').DS.'cache'.DS.'config';
                break;

            case 'cache_layout':
                $dir = $this->getDir('var').DS.'cache'.DS.'layout';
                break;

            case 'cache_block':
                $dir = $this->getDir('var').DS.'cache'.DS.'block';
                break;

        }
        return $dir;
    }

    public function getTempVarDir()
    {
        return (!empty($_ENV['TMP']) ? $_ENV['TMP'] : '/tmp').'/magento/var';
    }

    /**
     * Get store url
     *
     * @param   array $params
     * @return  string
     */
    public function getUrl($params)
    {
        if (!is_array($params)) {
            $params = array();
        }
        $cacheKey = md5(serialize($params));
        if (isset($this->_urlCache[$cacheKey])) {
            return $this->_urlCache[$cacheKey];
        }

        if (!empty($_SERVER['HTTPS'])) {
            if (empty($params['_secure']) || !empty($params['_type']) && ('skin'===$params['_type'] || 'js'===$params['_type'])) {
                $params['_secure'] = true;
            }
        }

        $config = $this->getConfig('web/'.(empty($params['_secure']) ? 'unsecure' : 'secure'));
        $protocol = $config['protocol'];
        $host = $config['host'];
        $port = $config['port'];

        if (empty($params['_type'])) {
            $basePath = $config['base_path'];
#echo '1: '.$basePath.'<hr>';
        } else {
            $basePath = $this->getConfig('web/url/'.$params['_type']);
#echo '2: '.$basePath.'<hr>';
        }

        $url = $protocol.'://'.$host;
        $url .= ('http'===$protocol && 80===$port || 'https'===$protocol && 443===$port) ? '' : ':'.$port;
        $url .= empty($basePath) ? '/' : $basePath;

        $this->_urlCache[$cacheKey] = $url;

        return $url;
    }

    public function processSubst($str)
    {
        if (!is_string($str)) {
            return $str;
        }
        if (strpos($str, '{{base_path}}')!==false) {
            $str = str_replace('{{base_path}}', $this->getDefaultBasePath(), $str);
        }
        return $str;
    }

    public function getDefaultBasePath()
    {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if (empty($basePath) || "\\"==$basePath || "/"==$basePath) {
            $basePath = '/';
        } else {
            $basePath .= '/';
        }
        return $basePath;
    }

    /**
     * Get default store currency code
     *
     * @return string
     */
    public function getDefaultCurrencyCode()
    {
        $result = $this->getConfig('general/currency/default');
        return $result;
    }

    /**
     * Set current store currency code
     *
     * @param   string $code
     * @return  string
     */
    public function setCurrentCurrencyCode($code)
    {
        $code = strtoupper($code);
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            Mage::getSingleton('core/session')->setCurrencyCode($code);
        }
        return $this;
    }

    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        $code = Mage::getSingleton('core/session')->getCurrencyCode();
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            return $code;
        }
        return $this->getDefaultCurrencyCode();
    }

    /**
     * Get allowed store currency codes
     *
     * @return array
     */
    public function getAvailableCurrencyCodes()
    {
        return explode(',', $this->getConfig('general/currency/allow'));
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param   double $price
     * @return  double
     */
    public function convertPrice($price, $format=false)
    {
        if ($this->getCurrentCurrency() && $this->getDefaultCurrency()) {
            $value = $this->getDefaultCurrency()->convert($price, $this->getCurrentCurrency());
        } else {
            $value = $price;
        }
		if ($format) {
        	$value = $this->getCurrentCurrency()->format($value);
        }
        return $value;
    }

    /**
     * Format price with currency filter (taking rate into consideration)
     *
     * @param   double $price
     * @return  string
     */
    public function formatPrice($price)
    {
        return $this->getPriceFilter()->filter($price);
    }

    /**
     * Get store price filter
     *
     * @return unknown
     */
    public function getPriceFilter()
    {
        if (!$this->_priceFilter) {
            if ($this->getDefaultCurrency() && $this->getCurrentCurrency()) {
                $this->_priceFilter = $this->getCurrentCurrency()->getFilter();
                $this->_priceFilter->setRate($this->getDefaultCurrency()->getRate($this->getCurrentCurrency()));
            }
            elseif($this->getDefaultCurrency()) {
                $this->_priceFilter = $this->getDefaultCurrency()->getFilter();
            }
            else {
                $this->_priceFilter = new Varien_Filter_Sprintf('%s', 2);
            }
        }
        return $this->_priceFilter;
    }

    public function getDatashareStores($key)
    {
        // TODO store level data sharing configuration in next version
        // if ($stores = $this->getConfig('advanced/datashare/'.$key)) {
        if ($stores = $this->getWebsite()->getConfig('advanced/datashare/'.$key)) {
            return explode(',', $stores);
        }
        return array();
    }

    public function getLanguageCode()
    {
        return $this->getConfig('general/local/language');
    }

    public function updateDatasharing()
    {
    	$this->getResource()->updateDatasharing();
    }
}
