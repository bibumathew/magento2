/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*
    TODO: Apply JStrim to reduce file size
*/
(function() {
    tinymce.create('tinymce.plugins.MagentowidgetPlugin', {
        /**
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            ed.addCommand('mceMagentowidget', function() {
                ed.windowManager.open({
                    file : ed.settings.magentowidget_url,
                    width : 1024,
                    height : 800,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });

            // Register Varienimages button
            ed.addButton('magentowidget', {
                title : 'magentowidget.insert_image',
                cmd : 'mceMagentowidget',
                image : url + '/img/icon.gif'
            });

            // Add a node change handler, selects the button in the UI when a image is selected
            ed.onNodeChange.add(function(ed, cm, n) {
                cm.setActive('magentowidget', n.nodeName == 'IMG');
                // cm.setActive('magentowidget', false);
                cm.setActive('advimage', false);
                cm.setActive('image', false);
            });
        },

        getInfo : function() {
            return {
                longname : 'Magento Widget Manager Plugin for TinyMCE 3.x',
                author : 'Magento Core Team',
                authorurl : 'http://magentocommerce.com',
                infourl : 'http://magentocommerce.com',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('magentowidget', tinymce.plugins.MagentowidgetPlugin);
})();
