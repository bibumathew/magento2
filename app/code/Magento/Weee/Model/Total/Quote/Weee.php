<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Weee
 * @copyright   {copyright}
 * @license     {license_link}
 */


namespace Magento\Weee\Model\Total\Quote;

class Weee extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    /**
     * Weee module helper object
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData;

    /**
     * @var Magento_Core_Model_Store
     */
    protected $_store;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * Flag which notify what tax amount can be affected by fixed porduct tax
     *
     * @var bool
     */
    protected $_isTaxAffected;

    /**
     * Initialize Weee totals collector
     *
     * @param Magento_Weee_Helper_Data $weeeData
     * @param Magento_Tax_Helper_Data $taxData
     */
    public function __construct(
        Magento_Weee_Helper_Data $weeeData,
        Magento_Tax_Helper_Data $taxData
    ) {
        $this->_weeeData = $weeeData;
        $this->_config = Mage::getSingleton('Magento_Tax_Model_Config');
        parent::__construct($taxData);
        $this->setCode('weee');
    }

    /**
     * Collect Weee taxes amount and prepare items prices for taxation and discount
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        \Magento\Sales\Model\Quote\Address\Total\AbstractTotal::collect($address);
        $this->_isTaxAffected = false;
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $address->setAppliedTaxesReset(true);
        $address->setAppliedTaxes(array());

        $this->_store = $address->getQuote()->getStore();
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $this->_resetItemData($item);
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_resetItemData($child);
                    $this->_process($address, $child);
                }
                $this->_recalculateParent($item);
            } else {
                $this->_process($address, $item);
            }
        }

        if ($this->_isTaxAffected) {
            $address->unsSubtotalInclTax();
            $address->unsBaseSubtotalInclTax();
        }

        return $this;
    }

    /**
     * Calculate item fixed tax and prepare information for discount and recular taxation
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    protected function _process(\Magento\Sales\Model\Quote\Address $address, $item)
    {
        if (!$this->_weeeData->isEnabled($this->_store)) {
            return $this;
        }

        $attributes = $this->_weeeData->getProductWeeeAttributes(
            $item->getProduct(),
            $address,
            $address->getQuote()->getBillingAddress(),
            $this->_store->getWebsiteId()
        );

        $applied = array();
        $productTaxes = array();

        $totalValue         = 0;
        $baseTotalValue     = 0;
        $totalRowValue      = 0;
        $baseTotalRowValue  = 0;

        foreach ($attributes as $k=>$attribute) {
            $baseValue      = $attribute->getAmount();
            $value          = $this->_store->convertPrice($baseValue);
            $rowValue       = $value*$item->getTotalQty();
            $baseRowValue   = $baseValue*$item->getTotalQty();
            $title          = $attribute->getName();

            $totalValue         += $value;
            $baseTotalValue     += $baseValue;
            $totalRowValue      += $rowValue;
            $baseTotalRowValue  += $baseRowValue;

            $productTaxes[] = array(
                'title'         => $title,
                'base_amount'   => $baseValue,
                'amount'        => $value,
                'row_amount'    => $rowValue,
                'base_row_amount'=> $baseRowValue,
                /**
                 * Tax value can't be presented as include/exclude tax
                 */
                'base_amount_incl_tax'      => $baseValue,
                'amount_incl_tax'           => $value,
                'row_amount_incl_tax'       => $rowValue,
                'base_row_amount_incl_tax'  => $baseRowValue,
            );

            $applied[] = array(
                'id'        => $attribute->getCode(),
                'percent'   => null,
                'hidden'    => $this->_weeeData->includeInSubtotal($this->_store),
                'rates'     => array(array(
                    'base_real_amount'=> $baseRowValue,
                    'base_amount'   => $baseRowValue,
                    'amount'        => $rowValue,
                    'code'          => $attribute->getCode(),
                    'title'         => $title,
                    'percent'       => null,
                    'position'      => 1,
                    'priority'      => -1000+$k,
                ))
            );
        }

        $item->setWeeeTaxAppliedAmount($totalValue)
            ->setBaseWeeeTaxAppliedAmount($baseTotalValue)
            ->setWeeeTaxAppliedRowAmount($totalRowValue)
            ->setBaseWeeeTaxAppliedRowAmnt($baseTotalRowValue);

        $this->_processTaxSettings($item, $totalValue, $baseTotalValue, $totalRowValue, $baseTotalRowValue)
            ->_processTotalAmount($address, $totalRowValue, $baseTotalRowValue)
            ->_processDiscountSettings($item, $totalValue, $baseTotalValue);

        $this->_weeeData->setApplied($item, array_merge($this->_weeeData->getApplied($item), $productTaxes));
        if ($applied) {
            $this->_saveAppliedTaxes($address, $applied,
               $item->getWeeeTaxAppliedAmount(),
               $item->getBaseWeeeTaxAppliedAmount(),
               null
            );
        }

    }

    /**
     * Check if discount should be applied to weee and add weee to discounted price
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param   float $value
     * @param   float $baseValue
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    protected function _processDiscountSettings($item, $value, $baseValue)
    {
        if ($this->_weeeData->isDiscounted($this->_store)) {
            $this->_weeeData->addItemDiscountPrices($item, $baseValue, $value);
        }
        return $this;
    }

    /**
     * Add extra amount which should be taxable by regular tax
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param   float $value
     * @param   float $baseValue
     * @param   float $rowValue
     * @param   float $baseRowValue
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    protected function _processTaxSettings($item, $value, $baseValue, $rowValue, $baseRowValue)
    {
        if ($this->_weeeData->isTaxable($this->_store) && $rowValue) {
            if (!$this->_config->priceIncludesTax($this->_store)) {
                $item->setExtraTaxableAmount($value)
                    ->setBaseExtraTaxableAmount($baseValue)
                    ->setExtraRowTaxableAmount($rowValue)
                    ->setBaseExtraRowTaxableAmount($baseRowValue);
            }
            $item->unsRowTotalInclTax()
                ->unsBaseRowTotalInclTax()
                ->unsPriceInclTax()
                ->unsBasePriceInclTax();
            $this->_isTaxAffected = true;
        }
        return $this;
    }

    /**
     * Proces row amount based on FPT total amount configuration setting
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   float $rowValue
     * @param   float $baseRowValue
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    protected function _processTotalAmount($address, $rowValue, $baseRowValue)
    {
        if ($this->_weeeData->includeInSubtotal($this->_store)) {
            $address->addTotalAmount('subtotal', $rowValue);
            $address->addBaseTotalAmount('subtotal', $baseRowValue);
            $this->_isTaxAffected = true;
        } else {
            $address->setExtraTaxAmount($address->getExtraTaxAmount() + $rowValue);
            $address->setBaseExtraTaxAmount($address->getBaseExtraTaxAmount() + $baseRowValue);
        }
        return $this;
    }

    /**
     * Recalculate parent item amounts based on children results
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    protected function _recalculateParent(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {

    }

    /**
     * Reset information about FPT for shopping cart item
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    protected function _resetItemData($item)
    {
        $this->_weeeData->setApplied($item, array());

        $item->setBaseWeeeTaxDisposition(0);
        $item->setWeeeTaxDisposition(0);

        $item->setBaseWeeeTaxRowDisposition(0);
        $item->setWeeeTaxRowDisposition(0);

        $item->setBaseWeeeTaxAppliedAmount(0);
        $item->setBaseWeeeTaxAppliedRowAmnt(0);

        $item->setWeeeTaxAppliedAmount(0);
        $item->setWeeeTaxAppliedRowAmount(0);
    }

    /**
     * Fetch FPT data to address object for display in totals block
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Weee\Model\Total\Quote\Weee
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        return $this;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }

    /**
     * No aggregated label for fixed product tax
     *
     * TODO: fix
     */
    public function getLabel()
    {
        return '';
    }
}
