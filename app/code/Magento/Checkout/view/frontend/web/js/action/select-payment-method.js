/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        '../model/quote',
        '../model/url-builder',
        '../model/step-navigator',
        'Magento_Ui/js/model/errorlist',
        'mage/storage'
    ],
    function(quote, urlBuilder, navigator, errorList, storage) {
        return function (paymentMethodCode, additionalData) {
            // TODO add support of additional payment data for more complex payments
            var paymentMethodData = {
                "cartId": quote.getQuoteId(),
                "method": {
                    "method": paymentMethodCode,
                    "po_number": null,
                    "cc_owner": null,
                    "cc_number": null,
                    "cc_type": null,
                    "cc_exp_year": null,
                    "cc_exp_month": null,
                    "additional_data": null
                }
            };
            return storage.put(
                urlBuilder.createUrl('/carts/:quoteId/selected-payment-methods', {quoteId: quote.getQuoteId()}),
                JSON.stringify(paymentMethodData)
            ).done(
                function() {
                    quote.setPaymentMethod(paymentMethodCode);
                    navigator.setCurrent('paymentMethod').goNext();
                }
            ).error(
                function(response) {
                    var error = JSON.parse(response.responseText);
                    errorList.add(error.message);
                    quote.setPaymentMethod(null);
                }
            )
        }
    }
);
