/**
 * {license_notice}
 *
 * @category    Rma
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
(function($) {
    "use strict";
    $.widget('mage.rmaCreate', {

        /**
         * options with default values
         */
        options: {
            //Template defining selectors
            templateRegistrant: '#template-registrant',
            registrantOptions: '#registrant-options',
            //Template selectors for adding and removing rows
            addItemToReturn: 'add-item-to-return',
            btnRemove: 'btn-remove',
            row: '#row',
            addRow: 'add-row',
            //Return item information selectors
            qtyReqBlock: '#qty_requested_block',
            remQtyBlock: '#remaining_quantity_block',
            remQty: '#remaining_quantity',
            reasonOtherRow: '#reason_other',
            reasonOtherInput: '#items:reason_other',
            radioItem: '#radio:item',
            orderItemId: '#item:order_item_id',
            itemsItem: 'items:item',
            itemsReason: 'items:reason',
            //Default counters and server side variables
            liIndex: 0,
            availableQuantity: 0,
            formDataPost: null,
            firstItemId: null,
            productType: null,
            prodTypeBundle: null
        },

        /**
         * Initialize rma create form
         * @private
         */
        _create: function() {
            //On document ready related tasks
            $($.proxy(this._ready, this));
        },

        /**
         * Process and loop thru all form data to create "Items to return" with preselected value. This is used for failed submit.
         * For first time this will add a default row without remove icon/button
         * @private
         */
        _ready: function() {
            this._processFormDataArr(this.options.formDataPost);
            //If no form data , then add default row for Return Item
            if (this.options.liIndex === 0) {
                this._addRegistrant();
            }
        },

        /**
         * Parse form data and re-create the return item information row preserving the submitted values
         * @private
         * @param {Object} formDataArr
         */
        _processFormDataArr: function(formDataArr) {
            if (formDataArr) {
                var formDataArrlen = formDataArr.length;
                for (var i = 0; i < formDataArrlen; i++) {
                    //Add a row
                    this._addRegistrant();
                    //Set the previously selected values
                    for (var key in formDataArr[i]) {
                        if (formDataArr[i].hasOwnProperty(key)) {
                            if (key === 'order_item_id') {
                                this._setFieldById(this.options.itemsItem + i, formDataArr[i][key]);
                                this._showBundle(i, formDataArr[i][key]);
                                this._setFieldById(this.options.orderItemId.substring(1) + i + '_' + formDataArr[i][key]);
                            } else if (key === 'items') {
                                for (var itemKey in formDataArr[i][key]) {
                                    if (formDataArr[i][key].hasOwnProperty(itemKey)) {
                                        this._setFieldById('items[' + i + '][' + formDataArr[i].order_item_id + '][checkbox][item][' + itemKey + ']');
                                        this._setFieldById('items[' + i + '][' + formDataArr[i].order_item_id + '][checkbox][qty][' + itemKey + ']', formDataArr[i][key][itemKey]);
                                        this._setBundleFieldById(itemKey, formDataArr[i].order_item_id, i);
                                        delete formDataArr[i].qty_requested;
                                    }
                                }
                            } else if (key === 'qty_requested' && formDataArr[i][key] !== "") {
                                this._setFieldById('items:' + key + i, formDataArr[i][key]);
                            } else {
                                this._setFieldById('items:' + key + i, formDataArr[i][key]);
                                if (key === 'reason') {
                                    this._showOtherOption(formDataArr[i][key], i);
                                }
                            }
                        }
                    }
                }
            }
        },

        /**
         * Add new return item information row using the template
         * @private
         */
        _addRegistrant: function() {
            this._setUpTemplate(this.options.liIndex, this.options.templateRegistrant, this.options.registrantOptions);
            this._showBundle(this.options.liIndex, this.options.firstItemId);
            this._showQuantity(this.options.productType, this.options.liIndex, this.options.availableQuantity);
            //Increment after rows are added
            this.options.liIndex++;
        },

        /**
         * Remove return item information row
         * @private
         * @param {string} liIndex - return item information row index
         * @return {boolean}
         */
        _removeRegistrant: function(liIndex) {
            $(this.options.row + liIndex).remove();
            return false;
        },

        /**
         * Show bundle row for bundle product type
         * @private
         * @param {string} index - return item information row bundle index
         * @param {string} itemId - bundle item id
         * @return {boolean}
         */
        _showBundle: function(index, itemId) {
            $('div[id^="radio\\:item' + index + '_"]').each(function() {
                var $this = $(this);
                if ($this.attr('id')) {
                    $this.parent().hide();
                }
            });

            $('input[id^="items[' + index + ']"]').prop('disabled', true);

            var rItem = $(this._esc(this.options.radioItem) + index + '_' + itemId),
                rOrderItemId = $(this._esc(this.options.orderItemId) + index + '_' + itemId);

            if (rItem.length) {
                rItem.parent().show();
                this._enableBundle(index, itemId);
            }

            if (rOrderItemId.length) {
                var typeQty = rOrderItemId.attr('rel'),
                    position = typeQty.lastIndexOf('_');
                this._showQuantity(typeQty.substring(0, position), index, typeQty.substr(position + 1));
            }
        },

        /**
         * Show quantity block for bundled products
         * @private
         * @param {string} type - product type
         * @param {string} index - return item information row index
         * @param {string} qty - quantity of item specified
         */
        _showQuantity: function(type, index, qty) {
            var qtyReqBlock = $(this.options.qtyReqBlock + '_' + index),
                remQtyBlock = $(this.options.remQtyBlock + '_' + index),
                remQty = $(this.options.remQty + '_' + index);

            if (type === this.options.prodTypeBundle) {
                if (qtyReqBlock.length) {
                    qtyReqBlock.hide();
                }
                if (remQtyBlock.length) {
                    remQtyBlock.hide();
                }
            } else {
                if (qtyReqBlock.length) {
                    qtyReqBlock.show();
                }
                if (remQtyBlock.length) {
                    remQtyBlock.show();
                }
                if (remQty.length) {
                    remQty.text(qty);
                }
            }
        },

        /**
         * Enable bundle and its items
         * @private
         * @param {string} index - return item information row index
         * @param {string} bid - bundle type id
         */
        _enableBundle: function(index, bid) {
            $('input[id^="items[' + index + '][' + bid + '][checkbox][item]["]').prop('disabled', false);
            $('input[id^="items[' + index + '][' + bid + '][checkbox][qty]["]').prop('disabled', function() {
                return !this.value;
            });
        },

        /**
         * Set the value on given element
         * @private
         * @param {string} domId
         * @param {string} value
         */
        _setFieldById: function(domId, value) {
            var x = $('#' + this._esc(domId));
            if (x.length) {
                if (x.is(':checkbox')) {
                    x.attr('checked', true);
                } else if (x.is('option')) {
                    x.attr('selected', 'selected');
                } else {
                    x.val(value);
                }
            }
        },

        /**
         * Used to recreate bundle fields and pre select submitted values on server side errors
         * @private
         * @param id {string}
         * @param bundleID {string}
         * @param index {string} - return item information row index
         */
        _setBundleFieldById: function(id, bundleID, index) {
            this._showBundle(index, bundleID);
            this._showBundleInput(id, bundleID, index);
            this._showQuantity('bundle', index, 0);
        },

        /**
         * Toggle "Other" options
         * @private
         * @param value
         * @param index - return item information row index
         */
        _showOtherOption: function(value, index) {
            var resOtherRow = this.options.reasonOtherRow,
                resOtherInput = this._esc(this.options.reasonOtherInput);
            if (value === 'other') {
                $(resOtherRow + index).show();
                $(resOtherInput + index).attr('disabled', false);
            } else {
                $(resOtherRow + index).hide();
                $(resOtherInput + index).attr('disabled', true);
            }
        },

        /**
         * Toggle bundled products
         * @param {string} id - bundle id
         * @param {string} bid - bundle type id
         * @param {string} index - return item information row index
         * @private
         */
        _showBundleInput: function(id, bid, index) {
            var qty = this._esc('#items[' + index + '][' + bid + '][checkbox][qty][' + id + ']');
            if ($(this._esc('#items[' + index + '][' + bid + '][checkbox][item][' + id + ']')).is(':checked')) {
                $(qty).show().attr('disabled', false);
            } else {
                $(qty).hide().attr('disabled', true);
            }
        },

        /**
         * Initialize and create markup for Return Item Information row
         * using the template
         * @private
         * @param {string} index - current index/count of the created template. This will be used as the id
         * @param {string} templateId - template markup selector
         * @param {string} containerId - container where the template will be injected
         * @return {*}
         */
        _setUpTemplate: function(index, templateId, containerId) {
            var li = $('<li></li>');
            li.addClass('fields').attr('id', 'row' + index);
            $(templateId).tmpl([{_index_: index}]).appendTo(li);
            $(containerId).append(li);
            // skipping first row
            if (index !== 0) {
                li.addClass(this.options.addRow);
            } else {
                //Hide the close button for first row
                $('#' + this.options.btnRemove + '0').hide();
            }
            //Binding template-wide events handlers
            this.element.on('click', 'a, input:checkbox', $.proxy(this._handleClick, this))
                .on('change', 'select', $.proxy(this._handleChange, this));

            return li;
        },

        /**
         * Delegated handler for click
         * @private
         * @param {Object} e - Native event object
         * @return {(null|boolean)}
         */
        _handleClick: function(e) {
            var currElem = $(e.currentTarget);

            if (currElem.attr('id') === this.options.addItemToReturn) {
                if (e.handled !== true) {
                    this._addRegistrant();
                    e.handled = true;
                    return false;
                }

            } else if (currElem.hasClass(this.options.btnRemove)) {
                //Extract index
                this._removeRegistrant(currElem.parent().attr('id').replace(this.options.btnRemove, ''));
                return false;

            } else if (currElem.attr('type') === 'checkbox') {
                var args = currElem.data("args");
                if (args) {
                    this._showBundleInput(args.item, args.bundleId, args.index);
                }
            }
        },

        /**
         * Delegated handler for change
         * @private
         * @param {Object} e - Native event object
         */
        _handleChange: function(e) {
            var currElem = $(e.currentTarget),
                currId = currElem.attr('id'),
                args = currElem.data("args");
            if (args && currId) {
                if (currId.substring(0, 10) === this.options.itemsItem) {
                    this._showBundle(args.index, currElem.val());
                } else if (currId.substring(0, 12) === this.options.itemsReason) {
                    this._showOtherOption(currElem.val(), args.index);
                }
                return false;
            }
        },

        /*
         * Utility function to add escape chars for jquery selector strings
         * @private
         * @param str - string to be processed
         * @return {string}
         */
        _esc: function(str) {
            if (str) {
                return str.replace(/([ ;&,.+*~\':"!\^$\[\]()=>|\/@])/g, '\\$1');
            } else {
                return str;
            }
        }
    });

})(jQuery);