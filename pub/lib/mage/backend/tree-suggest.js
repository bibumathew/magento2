/**
 * {license_notice}
 *
 * @category    mage
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint jquery:true browser:true*/
(function($) {
    'use strict';
    $.extend(true, $, {
        // @TODO: Move method 'treeToList' in file with utility functions
        mage: {
            treeToList: function(list, nodes, level, path) {
                $.each(nodes, function() {
                    if ($.type(this) === 'object') {
                        list.push({
                            label: this.label,
                            id: this.id,
                            level: level,
                            item: this,
                            path: path + this.label
                        });
                        if (this.children) {
                            $.mage.treeToList(list, this.children, level + 1, path + this.label + ' / ' );
                        }
                    }
                });
                return list;
            }
        }
    });

    var hover_node = $.jstree._instance.prototype.hover_node;
    var dehover_node = $.jstree._instance.prototype.dehover_node;
    var select_node = $.jstree._instance.prototype.select_node;
    var init = $.jstree._instance.prototype.init;
    $.extend(true, $.jstree._instance.prototype, {
        /**
         * @override
         */
        init: function() {
            this.get_container()
                .show()
                .on('keydown', $.proxy(function(e) {
                if (e.keyCode === $.ui.keyCode.ENTER) {
                    var o = this.data.ui.hovered || this.data.ui.last_selected || -1;
                    this.select_node(o, true);
                }
            }, this));
            init.call(this);
        },

        /**
         * @override
         */
        hover_node: function(obj) {
            hover_node.apply(this, arguments);
            obj = this._get_node(obj);
            if (!obj.length) {
                return false;
            }
            this.get_container().trigger('hover_node', [{item: obj.find('a:first')}]);
        },

        /**
         * @override
         */
        dehover_node: function() {
            dehover_node.call(this);
            this.get_container().trigger('dehover_node');
        },

        /**
         * @override
         */
        select_node: function(o) {
            select_node.apply(this, arguments);
            (o ? $(o) : this.data.ui.last_selected).trigger('select_tree_node');
        }
    });

    $.widget('mage.treeSuggest', $.mage.suggest, {
        widgetEventPrefix: "suggest",
        options: {
            template: '{{if items.length}}{{if $data.allShown()}}' +
                '{{if typeof nested === "undefined"}}' +
                '<div style="display:none;" data-mage-init="{&quot;jstree&quot;:{&quot;plugins&quot;:' +
                '[&quot;themes&quot;,&quot;html_data&quot;,&quot;ui&quot;,&quot;hotkeys&quot;],' +
                '&quot;themes&quot;:{&quot;theme&quot;:&quot;default&quot;,&quot;dots&quot;:' +
                'false,&quot;icons&quot;:false}}}">{{/if}}' +
                '<ul>{{each items}}' +
                '<li{{if $data.itemSelected($value)}} class="mage-suggest-selected"{{/if}}>' +
                '<a href="#" {{html optionData($value)}}>${$value.label}</a>' +
                '{{if $value.children && $value.children.length}}' +
                '{{html renderTreeLevel($value.children)}}' +
                '{{/if}}' +
                '</li>{{/each}}</ul>' +
                '{{if typeof nested === "undefined"}}</div>{{/if}}' +
                '{{else}}' +
                '<ul data-mage-init="{&quot;menu&quot;:[]}">' +
                '{{each items}}' +
                '{{if !$data.itemSelected($value)}}<li {{html optionData($value)}}>' +
                '<a href="#"><span class="category-label">${$value.label}<span>' +
                '<span class="category-path">${$value.path}<span></a></li>{{/if}}' +
                '{{/each}}</ul>' +
                '{{/if}}{{else}}<span class="mage-suggest-no-records">${noRecordsText}</span>{{/if}}',
            controls: {
                selector: ':ui-menu, .jstree',
                eventsMap: {
                    focus: ['menufocus', 'hover_node'],
                    blur: ['menublur', 'dehover_node'],
                    select: ['menuselect', 'select_tree_node']
                }
            }
        },
        /**
         * @override
         */
        _bind: function() {
            this._super();
            this._on({
                keydown: function(event) {
                    var keyCode = $.ui.keyCode;
                    switch (event.keyCode) {
                        case keyCode.LEFT:
                        case keyCode.RIGHT:
                            if (this.isDropdownShown()) {
                                event.preventDefault();
                                this._proxyEvents(event);
                            }
                    }
                }
            });
            this._on({
                focus: this.search
            });
        },

        /**
         * @override
         */
        search: function(e) {
            if (!this._recentItems && !this._value()) {
                var term = this._value();
                if (this._term !== term) {
                    this._term = term;
                }
                this._showAll(e);
            } else {
                this._superApply(arguments);
            }
        },

        /**
         * @override
         */
        _prepareDropdownContext: function() {
            var context = this._superApply(arguments),
                optionData = context.optionData,
                templateName = this.templateName;
                context.optionData = function(item) {
                    item = $.extend({}, item);
                    delete item.children;
                    return optionData(item);
                };
            return $.extend(context, {
                renderTreeLevel: function(children) {
                    var _context = $.extend({}, this.data, {items: children, nested: true});
                    return $('<div>').append($.tmpl(templateName, _context)).html();
                }
            });
        },

        /**
         * @override
         */
        _renderDropdown: function(e, items, context) {
            if(context &&!context._allShown) {
                items = this.filter($.mage.treeToList([], items, 0, ''), this._term);
            }
            var control = this.dropdown.find(this._control.selector);
            if (control.length && control.hasClass('jstree')) {
                control.jstree("destroy");
            }
            this._superApply([e, items, context]);
        }
    });
})(jQuery);
