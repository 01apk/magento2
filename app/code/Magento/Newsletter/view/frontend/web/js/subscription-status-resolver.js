/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    return function (email, deferred) {
        return $.getJSON(
            urlBuilder.build('newsletter/ajax/status'),
            {
                email: email
            }
        ).done(function (response) {
            if (response.errors || !response.subscribed) {
                deferred.reject();
            } else {
                deferred.resolve();
            }
        }).fail(function () {
            deferred.reject();
        });
    };
});
