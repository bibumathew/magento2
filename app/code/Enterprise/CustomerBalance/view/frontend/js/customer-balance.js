/**
 * {license_notice}
 *
 * @category    EE
 * @package     EE_CustomerBalance
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
(function($, undefined) {
    "use strict";
    $.widget('mage.customerBalance', {
        /**
         * Initialize store credit events
         * @private
         */
        _create: function() {
            this.eventData = {
                price: this.options.balance,
                totalPrice: 0
            };
            this.element.on('change', $.proxy(function(e) {
                if ($(e.target).is(':checked')) {
                    this.eventData.price = -1 * this.options.balance;
                } else {
                    if (this.options.amountSubstracted) {
                        this.eventData.price = this.options.usedAmount;
                        this.options.amountSubstracted = false;
                    } else {
                        this.eventData.price = this.options.balance;
                    }
                }
                this.element.trigger('updateCheckoutPrice', this.eventData);
            }, this));
        }
    });
})(jQuery);