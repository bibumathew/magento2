<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * HTML select element block
 */
class Magento_CatalogInventory_Block_Adminhtml_Form_Field_Stock extends Magento_Data_Form_Element_Select
{
    const QUANTITY_FIELD_HTML_ID = 'qty';

    /**
     * Quantity field element
     *
     * @var Magento_Data_Form_Element_Text
     */
    protected $_qty;

    /**
     * Is product composite (grouped or configurable)
     *
     * @var bool
     */
    protected $_isProductComposite;

    /**
     * Text element factory
     *
     * @var Magento_Data_Form_Element_TextFactory
     */
    protected $_factoryText;

    /**
     * Construct
     * 
     * @param Magento_Core_Helper_Data $coreData
     * @param Magento_Data_Form_Element_Factory $factoryElement
     * @param Magento_Data_Form_Element_CollectionFactory $factoryCollection
     * @param Magento_Data_Form_Element_TextFactory $factoryText
     * @param array $attributes
     */
    public function __construct(
        Magento_Core_Helper_Data $coreData,
        Magento_Data_Form_Element_Factory $factoryElement,
        Magento_Data_Form_Element_CollectionFactory $factoryCollection,
        Magento_Data_Form_Element_TextFactory $factoryText,
        array $attributes = array()
    ) {
        $this->_factoryText = $factoryText;
        $this->_qty = isset($attributes['qty']) ? $attributes['qty'] : $this->_createQtyElement();
        unset($attributes['qty']);
        parent::__construct($coreData, $factoryElement, $factoryCollection, $attributes);
        $this->setName($attributes['name']);
    }

    /**
     * Create quantity field
     *
     * @return Magento_Data_Form_Element_Text
     */
    protected function _createQtyElement()
    {
        /** @var \Magento_Data_Form_Element_Text $element */
        $element = $this->_factoryText->create();
        $element->setId(self::QUANTITY_FIELD_HTML_ID)->setName('qty')->addClass('validate-number input-text');
        return $element;
    }

    /**
     * Join quantity and in stock elements' html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->_disableFields();
        return $this->_qty->getElementHtml() . parent::getElementHtml()
            . $this->_getJs(self::QUANTITY_FIELD_HTML_ID, $this->getId());
    }

    /**
     * Set form to quantity element in addition to current element
     *
     * @param $form
     * @return Magento_Data_Form
     */
    public function setForm($form)
    {
        $this->_qty->setForm($form);
        return parent::setForm($form);
    }

    /**
     * Set value to quantity element in addition to current element
     *
     * @param $value
     * @return Magento_Data_Form_Element_Select
     */
    public function setValue($value)
    {
        if (is_array($value) && isset($value['qty'])) {
            $this->_qty->setValue($value['qty']);
        }
        parent::setValue(is_array($value) && isset($value['is_in_stock']) ? $value['is_in_stock'] : $value);
        return $this;
    }

    /**
     * Set name to quantity element in addition to current element
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_qty->setName($name . '[qty]');
        parent::setName($name . '[is_in_stock]');
    }

    /**
     * Get whether product is configurable or grouped
     *
     * @return bool
     */
    protected function _isProductComposite()
    {
        if ($this->_isProductComposite === null) {
            $this->_isProductComposite = $this->_qty->getForm()->getDataObject()->isComposite();
        }
        return $this->_isProductComposite;
    }

    /**
     * Disable fields depending on product type
     *
     * @return Magento_CatalogInventory_Block_Adminhtml_Form_Field_Stock
     */
    protected function _disableFields()
    {
        if (!$this->_isProductComposite() && $this->_qty->getValue() === null) {
            $this->setDisabled('disabled');
        }
        if ($this->_isProductComposite()) {
            $this->_qty->setDisabled('disabled');
        }
        return $this;
    }

    /**
     * Get js for quantity and in stock synchronisation
     *
     * @param $quantityFieldId
     * @param $inStockFieldId
     * @return string
     */
    protected function _getJs($quantityFieldId, $inStockFieldId)
    {
        // @codingStandardsIgnoreStart
        return "
            <script>
                jQuery(function($) {
                    var qty = $('#{$quantityFieldId}'),
                        productType = $('#product_type_id').val(),
                        stockAvailabilityField = $('#{$inStockFieldId}'),
                        manageStockField = $('#inventory_manage_stock'),
                        useConfigManageStockField = $('#inventory_use_config_manage_stock');

                    var disabler = function(event) {
                        var hasVariation = $('[data-panel=product-variations]').is('.opened');
                        if ((productType == 'configurable' && hasVariation)
                            || productType == 'grouped'
                            || productType == 'bundle'//@TODO move this check to Magento_Bundle after refactoring as widget
                            || hasVariation
                        ) {
                            return;
                        }
                        var manageStockValue = (qty.val() === '') ? 0 : 1;
                        stockAvailabilityField.prop('disabled', !manageStockValue);
                        if (manageStockField.val() != manageStockValue && !(event && event.type == 'keyup')) {
                            if (useConfigManageStockField.val() == 1) {
                                useConfigManageStockField.removeAttr('checked').val(0);
                            }
                            manageStockField.toggleClass('disabled', false).prop('disabled', false);
                            manageStockField.val(manageStockValue);
                        }
                    };

                    //Associated fields
                    var fieldsAssociations = {
                        '$quantityFieldId' : 'inventory_qty',
                        '$inStockFieldId'  : 'inventory_stock_availability'
                    };
                    //Fill corresponding field
                    var filler = function() {
                        var id = $(this).attr('id');
                        if ('undefined' !== typeof fieldsAssociations[id]) {
                            $('#' + fieldsAssociations[id]).val($(this).val());
                        } else {
                            $('#' + getKeyByValue(fieldsAssociations, id)).val($(this).val());
                        }

                        if ($('#inventory_manage_stock').length) {
                            fireEvent($('#inventory_manage_stock').get(0), 'change');
                        }
                    };
                    //Get key by value from object
                    var getKeyByValue = function(object, value) {
                        var returnVal = false;
                        $.each(object, function(objKey, objValue){
                            if (value === objValue) {
                                returnVal = objKey;
                            }
                        });
                        return returnVal;
                    };
                    $.each(fieldsAssociations, function(generalTabField, advancedTabField) {
                        $('#' + generalTabField + ', #' + advancedTabField)
                            .bind('focus blur change keyup click', filler)
                            .bind('keyup change blur', disabler);
                        filler.call($('#' + generalTabField));
                        filler.call($('#' + advancedTabField));
                    });
                    disabler();
                });
            </script>
        ";
        // @codingStandardsIgnoreEnd
    }
}
