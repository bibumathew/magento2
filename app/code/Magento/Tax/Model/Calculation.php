<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Tax Calculation Model
 */
class Magento_Tax_Model_Calculation extends Magento_Core_Model_Abstract
{
    const CALC_TAX_BEFORE_DISCOUNT_ON_EXCL      = '0_0';
    const CALC_TAX_BEFORE_DISCOUNT_ON_INCL      = '0_1';
    const CALC_TAX_AFTER_DISCOUNT_ON_EXCL       = '1_0';
    const CALC_TAX_AFTER_DISCOUNT_ON_INCL       = '1_1';

    const CALC_UNIT_BASE                        = 'UNIT_BASE_CALCULATION';
    const CALC_ROW_BASE                         = 'ROW_BASE_CALCULATION';
    const CALC_TOTAL_BASE                       = 'TOTAL_BASE_CALCULATION';

    protected $_rates                           = array();
    protected $_ctc                             = array();
    protected $_ptc                             = array();

    protected $_rateCache                       = array();
    protected $_rateCalculationProcess          = array();

    /**
     * @var Magento_Customer_Model_Customer|bool
     */
    protected $_customer;

    protected $_defaultCustomerTaxClass;

    /**
     * Customer data
     *
     * @var Magento_Customer_Helper_Data
     */
    protected $_customerData = null;

    /**
     * Core store config
     *
     * @var Magento_Core_Model_Store_Config
     */
    protected $_coreStoreConfig;

    /**
     * @var Magento_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento_Customer_Model_GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var Magento_Customer_Model_Session
     */
    protected $_customerSession;

    /**
     * @var Magento_Customer_Model_CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var Magento_Tax_Model_Resource_Class_CollectionFactory
     */
    protected $_classesFactory;

    /**
     * @param Magento_Customer_Helper_Data $customerData
     * @param Magento_Core_Model_Context $context
     * @param Magento_Core_Model_Registry $registry
     * @param Magento_Core_Model_Store_Config $coreStoreConfig
     * @param Magento_Core_Model_StoreManagerInterface $storeManager
     * @param Magento_Customer_Model_GroupFactory $groupFactory
     * @param Magento_Customer_Model_Session $customerSession
     * @param Magento_Customer_Model_CustomerFactory $customerFactory
     * @param Magento_Tax_Model_Resource_Class_CollectionFactory $classesFactory
     * @param Magento_Tax_Model_Resource_Calculation $resource
     * @param Magento_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Magento_Customer_Helper_Data $customerData,
        Magento_Core_Model_Context $context,
        Magento_Core_Model_Registry $registry,
        Magento_Core_Model_Store_Config $coreStoreConfig,
        Magento_Core_Model_StoreManagerInterface $storeManager,
        Magento_Customer_Model_GroupFactory $groupFactory,
        Magento_Customer_Model_Session $customerSession,
        Magento_Customer_Model_CustomerFactory $customerFactory,
        Magento_Tax_Model_Resource_Class_CollectionFactory $classesFactory,
        Magento_Tax_Model_Resource_Calculation $resource,
        Magento_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_customerData = $customerData;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_storeManager = $storeManager;
        $this->_groupFactory = $groupFactory;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_classesFactory = $classesFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Magento_Tax_Model_Resource_Calculation');
    }

    /**
     * Specify customer object which can be used for rate calculation
     *
     * @param   Magento_Customer_Model_Customer $customer
     * @return  Magento_Tax_Model_Calculation
     */
    public function setCustomer(Magento_Customer_Model_Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    public function getDefaultCustomerTaxClass($store = null)
    {
        if ($this->_defaultCustomerTaxClass === null) {
            $defaultCustomerGroup = $this->_customerData->getDefaultCustomerGroupId($store);
            /** @var $customerGroup Magento_Customer_Model_Group */
            $customerGroup = $this->_groupFactory->create();
            $this->_defaultCustomerTaxClass = $customerGroup->getTaxClassId($defaultCustomerGroup);
        }
        return $this->_defaultCustomerTaxClass;
    }

    /**
     * Get customer object
     *
     * @return  Magento_Customer_Model_Customer|bool
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            if ($this->_customerSession->isLoggedIn()) {
                $this->_customer = $this->_customerSession->getCustomer();
            } elseif ($this->_customerSession->getCustomerId()) {
                /** @var $customer Magento_Customer_Model_Customer */
                $customer = $this->_customerFactory->create();
                $this->_customer = $customer->load($this->_customerSession->getCustomerId());
            } else {
                $this->_customer = false;
            }
        }
        return $this->_customer;
    }

    /**
     * Delete calculation settings by rule id
     *
     * @param   int $ruleId
     * @return  Magento_Tax_Model_Calculation
     */
    public function deleteByRuleId($ruleId)
    {
        $this->_getResource()->deleteByRuleId($ruleId);
        return $this;
    }

    /**
     * Get calculation rates by rule id
     *
     * @param   int $ruleId
     * @return  array
     */
    public function getRates($ruleId)
    {
        if (!isset($this->_rates[$ruleId])) {
            $this->_rates[$ruleId] = $this->_getResource()->getDistinct('tax_calculation_rate_id', $ruleId);
        }
        return $this->_rates[$ruleId];
    }

    /**
     * Get allowed customer tax classes by rule id
     *
     * @param   int $ruleId
     * @return  array
     */
    public function getCustomerTaxClasses($ruleId)
    {
        if (!isset($this->_ctc[$ruleId])) {
            $this->_ctc[$ruleId] = $this->_getResource()->getDistinct('customer_tax_class_id', $ruleId);
        }
        return $this->_ctc[$ruleId];
    }

    /**
     * Get allowed product tax classes by rule id
     *
     * @param   int $ruleId
     * @return  array
     */
    public function getProductTaxClasses($ruleId)
    {
        if (!isset($this->_ptc[$ruleId])) {
            $this->_ptc[$ruleId] = $this->getResource()->getDistinct('product_tax_class_id', $ruleId);
        }
        return $this->_ptc[$ruleId];
    }

    /**
     * Aggregate tax calculation data to array
     *
     * @return array
     */
    protected function _formCalculationProcess()
    {
        $title = $this->getRateTitle();
        $value = $this->getRateValue();
        $id = $this->getRateId();

        $rate = array('code'=>$title, 'title'=>$title, 'percent'=>$value, 'position'=>1, 'priority'=>1);

        $process = array();
        $process['percent'] = $value;
        $process['id'] = "{$id}-{$value}";
        $process['rates'][] = $rate;

        return array($process);
    }

    /**
     * Get calculation tax rate by specific request
     *
     * @param   Magento_Object $request
     * @return  float
     */
    public function getRate($request)
    {
        if (!$request->getCountryId() || !$request->getCustomerClassId() || !$request->getProductClassId()) {
            return 0;
        }

        $cacheKey = $this->_getRequestCacheKey($request);
        if (!isset($this->_rateCache[$cacheKey])) {
            $this->unsRateValue();
            $this->unsCalculationProcess();
            $this->unsEventModuleId();
            $this->_eventDispatcher->dispatch('tax_rate_data_fetch', array('request' => $request, 'sender' => $this));
            if (!$this->hasRateValue()) {
                $rateInfo = $this->_getResource()->getRateInfo($request);
                $this->setCalculationProcess($rateInfo['process']);
                $this->setRateValue($rateInfo['value']);
            } else {
                $this->setCalculationProcess($this->_formCalculationProcess());
            }
            $this->_rateCache[$cacheKey] = $this->getRateValue();
            $this->_rateCalculationProcess[$cacheKey] = $this->getCalculationProcess();
        }
        return $this->_rateCache[$cacheKey];
    }

    /**
     * Get cache key value for specific tax rate request
     *
     * @param   $request
     * @return  string
     */
    protected function _getRequestCacheKey($request)
    {
        $key = $request->getStore() ? $request->getStore()->getId() . '|' : '';
        $key.= $request->getProductClassId() . '|' . $request->getCustomerClassId() . '|'
            . $request->getCountryId() . '|'. $request->getRegionId() . '|' . $request->getPostcode();
        return $key;
    }

    /**
     * Get tax rate based on store shipping origin address settings
     * This rate can be used for conversion store price including tax to
     * store price excluding tax
     *
     * @param Magento_Object $request
     * @param null|string|bool|int|Magento_Core_Model_Store $store
     * @return float
     */
    public function getStoreRate($request, $store=null)
    {
        $storeRequest = $this->getRateOriginRequest($store)
            ->setProductClassId($request->getProductClassId());
        return $this->getRate($storeRequest);
    }

    /**
     * Get request object for getting tax rate based on store shipping original address
     *
     * @param   null|string|bool|int|Magento_Core_Model_Store $store
     * @return  Magento_Object
     */
    public function getRateOriginRequest($store = null)
    {
        $request = new Magento_Object();
        $request->setCountryId($this->_coreStoreConfig->getConfig(Magento_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID, $store))
            ->setRegionId($this->_coreStoreConfig->getConfig(Magento_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID, $store))
            ->setPostcode($this->_coreStoreConfig->getConfig(Magento_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE, $store))
            ->setCustomerClassId($this->getDefaultCustomerTaxClass($store))
            ->setStore($store);
        return $request;
    }

    /**
     * Get request object with information necessary for getting tax rate
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @param   null|bool|Magento_Object $shippingAddress
     * @param   null|bool||Magento_Object $billingAddress
     * @param   null|int $customerTaxClass
     * @param   null|int $store
     * @return  Magento_Object
     */
    public function getRateRequest(
        $shippingAddress = null,
        $billingAddress = null,
        $customerTaxClass = null,
        $store = null)
    {
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address    = new Magento_Object();
        $customer   = $this->getCustomer();
        $basedOn    = $this->_coreStoreConfig->getConfig(Magento_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);

        if (($shippingAddress === false && $basedOn == 'shipping')
            || ($billingAddress === false && $basedOn == 'billing')) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId())
                && $basedOn == 'billing')
                || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId())
                && $basedOn == 'shipping')
            ){
                if ($customer) {
                    $defBilling = $customer->getDefaultBillingAddress();
                    $defShipping = $customer->getDefaultShippingAddress();

                    if ($basedOn == 'billing' && $defBilling && $defBilling->getCountryId()) {
                        $billingAddress = $defBilling;
                    } else if ($basedOn == 'shipping' && $defShipping && $defShipping->getCountryId()) {
                        $shippingAddress = $defShipping;
                    } else {
                        $basedOn = 'default';
                    }
                } else {
                    $basedOn = 'default';
                }
            }
        }

        switch ($basedOn) {
            case 'billing':
                $address = $billingAddress;
                break;
            case 'shipping':
                $address = $shippingAddress;
                break;
            case 'origin':
                $address = $this->getRateOriginRequest($store);
                break;
            case 'default':
                $address
                    ->setCountryId($this->_coreStoreConfig->getConfig(
                        Magento_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                        $store))
                    ->setRegionId($this->_coreStoreConfig->getConfig(Magento_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store))
                    ->setPostcode($this->_coreStoreConfig->getConfig(
                        Magento_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                        $store));
                break;
        }

        if (is_null($customerTaxClass) && $customer) {
            $customerTaxClass = $customer->getTaxClassId();
        } elseif (($customerTaxClass === false) || !$customer) {
            $customerTaxClass = $this->getDefaultCustomerTaxClass($store);
        }

        $request = new Magento_Object();
        $request
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setPostcode($address->getPostcode())
            ->setStore($store)
            ->setCustomerClassId($customerTaxClass);
        return $request;
    }

    /**
     * Compare data and rates for two tax rate requests for same products (product tax class ids).
     * Returns true if requests are similar (i.e. equal taxes rates will be applied to them)
     *
     * Notice:
     * a) productClassId MUST be identical for both requests, because we intend to check selling SAME products to DIFFERENT locations
     * b) due to optimization productClassId can be array of ids, not only single id
     *
     * @param   Magento_Object $first
     * @param   Magento_Object $second
     * @return  bool
     */
    public function compareRequests($first, $second)
    {
        $country = $first->getCountryId() == $second->getCountryId();
        // "0" support for admin dropdown with --please select--
        $region  = (int)$first->getRegionId() == (int)$second->getRegionId();
        $postcode= $first->getPostcode() == $second->getPostcode();
        $taxClass= $first->getCustomerClassId() == $second->getCustomerClassId();

        if ($country && $region && $postcode && $taxClass) {
            return true;
        }
        /**
         * Compare available tax rates for both requests
         */
        $firstReqRates = $this->_getResource()->getRateIds($first);
        $secondReqRates = $this->_getResource()->getRateIds($second);
        if ($firstReqRates === $secondReqRates) {
            return true;
        }

        /**
         * If rates are not equal by ids then compare actual values
         * All product classes must have same rates to assume requests been similar
         */
        $productClassId1 = $first->getProductClassId(); // Save to set it back later
        $productClassId2 = $second->getProductClassId(); // Save to set it back later

        // Ids are equal for both requests, so take any of them to process
        $ids = is_array($productClassId1) ? $productClassId1 : array($productClassId1);
        $identical = true;
        foreach ($ids as $productClassId) {
            $first->setProductClassId($productClassId);
            $rate1 = $this->getRate($first);

            $second->setProductClassId($productClassId);
            $rate2 = $this->getRate($second);

            if ($rate1 != $rate2) {
                $identical = false;
                break;
            }
        }

        $first->setProductClassId($productClassId1);
        $second->setProductClassId($productClassId2);

        return $identical;
    }

    protected function _getRates($request, $fieldName, $type)
    {
        $result = array();
        /** @var $classes Magento_Tax_Model_Resource_Class_Collection */
        $classes = $this->_classesFactory->create();
        $classes->addFieldToFilter('class_type', $type)->load();
        /** @var $class Magento_Tax_Model_Class */
        foreach ($classes as $class) {
            $request->setData($fieldName, $class->getId());
            $result[$class->getId()] = $this->getRate($request);
        }
        return $result;
    }

    public function getRatesForAllProductTaxClasses($request)
    {
        return $this->_getRates($request, 'product_class_id', Magento_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
    }
    public function getRatesForAllCustomerTaxClasses($request)
    {
        return $this->_getRates($request, 'customer_class_id', Magento_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
    }

    /**
     * Get information about tax rates applied to request
     *
     * @param   Magento_Object $request
     * @return  array
     */
    public function getAppliedRates($request)
    {
        $cacheKey = $this->_getRequestCacheKey($request);
        if (!isset($this->_rateCalculationProcess[$cacheKey])) {
            $this->_rateCalculationProcess[$cacheKey] = $this->_getResource()->getCalculationProcess($request);
        }
        return $this->_rateCalculationProcess[$cacheKey];
    }

    public function reproduceProcess($rates)
    {
        return $this->getResource()->getCalculationProcess(null, $rates);
    }

    public function getRatesByCustomerTaxClass($customerTaxClass)
    {
        return $this->getResource()->getRatesByCustomerTaxClass($customerTaxClass);
    }

    public function getRatesByCustomerAndProductTaxClasses($customerTaxClass, $productTaxClass)
    {
        return $this->getResource()->getRatesByCustomerTaxClass($customerTaxClass, $productTaxClass);
    }

    /**
     * Calculate rated tax amount based on price and tax rate.
     * If you are using price including tax $priceIncludeTax should be true.
     *
     * @param   float $price
     * @param   float $taxRate
     * @param   boolean $priceIncludeTax
     * @param   boolean $round
     * @return  float
     */
    public function calcTaxAmount($price, $taxRate, $priceIncludeTax = false, $round = true)
    {
        $taxRate = $taxRate/100;

        if ($priceIncludeTax) {
            $amount = $price*(1-1/(1+$taxRate));
        } else {
            $amount = $price*$taxRate;
        }

        if ($round) {
            return $this->round($amount);
        }

        return $amount;
    }

    /**
     * Truncate number to specified precision
     *
     * @param   float $price
     * @param   int $precision
     * @return  float
     */
    public function truncate($price, $precision=4)
    {
        $exp = pow(10,$precision);
        $price = floor($price*$exp)/$exp;
        return $price;
    }

    /**
     * Round tax amount
     *
     * @param   float $price
     * @return  float
     */
    public function round($price)
    {
        return $this->_storeManager->getStore()->roundPrice($price);
    }

    /**
     * Round price up
     *
     * @param   float $price
     * @return  float
     */
    public function roundUp($price)
    {
        return ceil($price*100)/100;
    }
}
