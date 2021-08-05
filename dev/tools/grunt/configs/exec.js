/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

var combo = require('./combo'),
    themes = require('../tools/files-router').get('themes'),
    _      = require('underscore'),
    root = typeof self == 'object' && self.self === self && self ||
        typeof global == 'object' && global.global === global && global || Function('return this')() || {};
root._ = _;

var themeOptions = {};

_.each(themes, function(theme, name) {
    themeOptions[name] = {
        cmd: combo.collector.bind(combo, name)
    };
});

var execOptions = {
    all : {
        cmd: function () {
            var cmdPlus = (/^win/.test(process.platform) == true) ? ' & ' : ' && ',
                command;

            command = _.map(themes, function(theme, name) {
                return combo.collector(name);
            }).join(cmdPlus);

            return 'echo ' + command;
        }
    }
};

/**
 * Execution into cmd
 */
module.exports = _.extend(themeOptions, execOptions);
