/**
 * {license_notice}
 *
 * @category    design
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint jquery:true*/
(function($) {
    'use strict';
    $.widget('vde.customCssPanel', {
        options: {
            saveCustomCssUrl: null,
            downloadCustomCssUrl: null,
            customCssCode: '#custom_code',
            btnUpdateCss: '[data-action="update"]',
            btnDeleteCss: '[data-action="delete"]',
            btnUpdateDownload: '[data-action="download"]'
        },

        updateButtons: function() {
            this._prepareUpdateButton();
        },

        _create: function() {
            this.btnCssUpdate = this.element.find(this.options.btnUpdateCss);
            this.btnCssDelete = this.element.find(this.options.btnDeleteCss);
            this.customCssCode = this.element.find(this.options.customCssCode);
            this.btnUpdateDownload = this.element.find(this.options.btnUpdateDownload);
            this._prepareUpdateButton();
            this._events();
        },

        _events: function() {
            this.btnCssUpdate.on('click', $.proxy(this._updateCustomCss, this));
            this.btnCssDelete.on('click', $.proxy(this._deleteCustomCss, this));
            this.customCssCode.on('input onchange change', $.proxy(this._editCustomCss, this));
            this.btnUpdateDownload.on('click', $.proxy(this._downloadCustomCss, this));
        },

        _editCustomCss: function()
        {
            if ($.trim($(this.customCssCode).val())) {
                this.btnCssUpdate.removeProp('disabled');
            }
        },

        _downloadCustomCss: function() {
            $.mage.redirect(this.options.downloadCustomCssUrl);
        },

        _postUpdatedCustomCssContent: function()
        {
            $.ajax({
                type: 'POST',
                url:  this.options.saveCustomCssUrl,
                data: {custom_css_content: $(this.customCssCode).val()},
                dataType: 'json',
                success: $.proxy(function(response) {
                    this.element.trigger('addMessage', {
                        containerId: '#vde-tab-custom-messages-placeholder',
                        message: response.message
                    });
                    this.element.trigger('refreshIframe');
                    this._prepareUpdateButton();
                }, this),
                error: function() {
                    alert($.mage.__('Error: unknown error.'));
                }
            });
        },

        _updateCustomCss: function()
        {
            this._postUpdatedCustomCssContent();
        },

        _deleteCustomCss: function()
        {
            this.customCssCode.val('');
            this._postUpdatedCustomCssContent();
        },

        _prepareUpdateButton: function()
        {
            if (!$.trim($(this.customCssCode).val())) {
                this.btnCssUpdate.prop('disabled', 'disabled');
                this.btnUpdateDownload.add(this.btnCssDelete).fadeOut();
            } else {
                this.btnCssUpdate.removeProp('disabled');
                this.btnUpdateDownload.add(this.btnCssDelete).fadeIn();
            }
        }
    });
})(window.jQuery);
