/**
 * {license_notice}
 *
 * @category    mage file change/delete
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
(function($) {
    $.widget('mage.fileOption', {
        options: {
        },

        _create: function() {
            this.fileDeleteFlag = this.fileChangeFlag = false;
            this.inputField = this.element.find('input[name=' + this.options.fileName + ']')[0];
            this.inputFieldAction = this.element.find('input[name=' + this.options.fieldNameAction + ']')[0];
            this.fileNameSpan = this.element.parent('dd').find('.' + this.options.fileNamed);

            $(this.options.changeFileSelector).on('click', $.proxy(function() {
                this._toggleFileChange();
            }, this));
            $(this.options.deleteFileSelector).on('click', $.proxy(function() {
                this._toggleFileDelete();
            }, this));
        },

        _toggleFileChange: function() {
            this.element.toggle();
            this.fileChangeFlag = !this.fileChangeFlag;
            if (!this.fileDeleteFlag) {
                $(this.inputFieldAction).attr('value', this.fileChangeFlag ? 'save_new' : 'save_old');
                this.inputField.disabled = !this.fileChangeFlag;
            }
        },

        _toggleFileDelete: function() {
            this.fileDeleteFlag = $(this.options.deleteFileSelector + ':checked').val();
            $(this.inputFieldAction).attr('value',
                this.fileDeleteFlag ? '' : this.fileChangeFlag ? 'save_new' : 'save_old');
            this.inputField.disabled = this.fileDeleteFlag || !this.fileChangeFlag;
            this.fileNameSpan.css('text-decoration', this.fileDeleteFlag ? 'line-through' : 'none');
        }
    });
})(jQuery);

