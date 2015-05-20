/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    'use strict';

    $.widget('mage.step-wizard', $.ui.tabs, {
        options: {
            collapsible: false,
            disabled: [1,2,3],
            event: "click"
        },
        _create: function() {
            this._dialog();
            this._control();
            this.options.active = this.options.active >= 0 ? this.options.active : 0;
            this._super();
        },
        _dialog: function() {
            var dialog = this.element.parent();
            $('#dialog').dialog();
        },
        _control: function() {
            var self = this;
            this.element.find('.btn-wrap-next').on('click', function(event){
                self._activate(self.options.active + 1);
            });
            this.element.find('.btn-wrap-prev').on('click', function(event){
                self._activate(self.options.active - 1);
            })
        },
        load: function(index, event) {
            this._disabledTabs(index);
            this._super(index, event);
        },
        _disabledTabs: function(index) {
            var disabled = [];
            for(var i=0; this.tabs.length >= i; i++) {
                if([index, index + 1, index - 1].indexOf(i) > -1) {
                    continue;
                }
                disabled.push(i);
            }
            this._setupDisabled( disabled );
        }
    });

    return $.mage["step-wizard"];
});
