/**
 * {license_notice}
 *
 * @category    gift message options toggle
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint jquery:true*/
(function($) {
    "use strict";
    $.widget('mage.giftOptions', {
        options: {
            mageError: 'mage-error',
            noDisplay: 'no-display',
            requiredEntry: 'required-entry'
        },

        /**
         * Initial toggle of the various gift options after widget instantiation.
         * @private
         */
        _init: function() {
            this._toggleVisibility();
        },

        /**
         * Bind a click handler to the widget's context element.
         * @private
         */
        _create: function() {
            this.element.on('click', $.proxy(this._toggleVisibility, this));
            $(this.element.data('selector').id).find('.giftmessage-area')
                .on('change', $.proxy(this._toggleRequired, this));
        },

        /**
         * Toggle the visibility of the widget's context element's selector(s).
         * @private
         * @param event {Object} - Click event. Target is a checkbox.
         */
        _toggleVisibility: function(event) {
            var checkbox = event ? $(event.target) : this.element,
                container = $(checkbox.data('selector').id);
            if (checkbox.is(':checked')) {
                container.show()
                    .find('.giftmessage-area:not(:visible)').each(function(x, element) {
                        if ($(element).val().length > 0) {
                            $(element).change();
                            container.find('a').click();
                        }
                    });
            } else {
                var _this = this;
                container.hide()
                    .find('.input-text:not(.giftmessage-area)').each(function(x, element) {
                        $(element).val(element.defaultValue).removeClass(_this.options.mageError)
                            .next('div.' + _this.options.mageError).remove();
                    }).end()
                    .find('.giftmessage-area').val('').change().end()
                    .find('.select').val('').change().end()
                    .find('.checkbox:checked').prop('checked', false).click().prop('checked', false).end()
                    .find('a').parent().next().addClass(this.options.noDisplay).end()
                    .find('.price-box').addClass(this.options.noDisplay).end();
            }
        },

        /**
         * Make the From and To input fields required if a gift message has been written.
         * @private
         * @param event {Object} - Change event. Target is a textarea.
         */
        _toggleRequired: function(event) {
            var textArea = $(event.target),
                length = textArea.val().length;
            textArea.closest('li').prev('.fields')
                .find('.input-text').toggleClass(this.options.requiredEntry, length > 0);
        }
    });
})(jQuery);
