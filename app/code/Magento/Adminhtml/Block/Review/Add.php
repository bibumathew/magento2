<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml add Review main block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Adminhtml\Block\Review;

class Add extends \Magento\Adminhtml\Block\Widget\Form\Container
{
    protected function _construct()
    {
        parent::_construct();

        $this->_controller = 'review';
        $this->_mode = 'add';

        $this->_updateButton('save', 'label', __('Save Review'));
        $this->_updateButton('save', 'id', 'save_button');

        $this->_updateButton('reset', 'id', 'reset_button');

        $this->_formScripts[] = '
            toggleParentVis("add_review_form");
            toggleVis("save_button");
            toggleVis("reset_button");
        ';

        $this->_formInitScripts[] = '
            //<![CDATA[
            var review = function() {
                return {
                    productInfoUrl : null,
                    formHidden : true,

                    gridRowClick : function(data, click) {
                        if(Event.findElement(click,\'TR\').title){
                            review.productInfoUrl = Event.findElement(click,\'TR\').title;
                            review.loadProductData();
                            review.showForm();
                            review.formHidden = false;
                        }
                    },

                    loadProductData : function() {
                        var con = new Ext.lib.Ajax.request(\'POST\', review.productInfoUrl, {success:review.reqSuccess,failure:review.reqFailure}, {form_key:FORM_KEY});
                    },

                    showForm : function() {
                        toggleParentVis("add_review_form");
                        toggleVis("productGrid");
                        toggleVis("save_button");
                        toggleVis("reset_button");
                    },

                    updateRating: function() {
                        elements = [$("select_stores"), $("rating_detail").getElementsBySelector("input[type=\'radio\']")].flatten();
                        $(\'save_button\').disabled = true;
                        var params = Form.serializeElements(elements);
                        if (!params.isAjax) {
                            params.isAjax = "true";
                        }
                        if (!params.form_key) {
                            params.form_key = FORM_KEY;
                        }
                        new Ajax.Updater("rating_detail", "'.$this->getUrl('*/*/ratingItems').'", {parameters:params, evalScripts: true,  onComplete:function(){ $(\'save_button\').disabled = false; } });
                    },

                    reqSuccess :function(o) {
                        var response = Ext.util.JSON.decode(o.responseText);
                        if( response.error ) {
                            alert(response.message);
                        } else if( response.id ){
                            $("product_id").value = response.id;

                            $("product_name").innerHTML = \'<a href="' . $this->getUrl('catalog/product/edit') . 'id/\' + response.id + \'" target="_blank">\' + response.name + \'</a>\';
                        } else if( response.message ) {
                            alert(response.message);
                        }
                    }
                }
            }();

             Event.observe(window, \'load\', function(){
                 if ($("select_stores")) {
                     Event.observe($("select_stores"), \'change\', review.updateRating);
                 }
           });
           //]]>
        ';
    }

    public function getHeaderText()
    {
        return __('Add New Review');
    }
}
