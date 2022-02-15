/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/
define([
    'squire', 'jquery', 'ko'
], function (Squire, $, ko) {
    'use strict';

    describe('Magento_Customer/js/customer-global-session-loader', function () {
        var injector = new Squire(),
            customer = ko.observable({}),
            mocks = {
                'Magento_Customer/js/customer-data': {
                    get: jasmine.createSpy('get', function () {
                        return customer;
                    }).and.callThrough(),
                    reload: jasmine.createSpy(),
                    getInitCustomerData: function () {}
                }
            },
            deferred,
            customerSessionLoader;

        beforeEach(function (done) {
            $('body').append('<div id="customerMenu" class="customer-menu">Customer Menu</div>');
            injector.mock(mocks);
            injector.require(['Magento_Customer/js/customer-global-session-loader'], function (instance) {
                customerSessionLoader = instance;
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}

            customer({});
            $('#customerMenu').remove();
        });

        describe('Check customer data preparation process', function () {
            it('Tests that customer data is NOT checked before initialization', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    return deferred.promise();
                });
                expect(customerSessionLoader()).toBe(undefined);

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('customer');
                expect(mocks['Magento_Customer/js/customer-data'].getInitCustomerData).toHaveBeenCalled();
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
            });

            it('Tests that customer data reloads if customer first name is not there', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    deferred.resolve();

                    return deferred.promise();
                });
                customer({
                    _data: null
                });
                customerSessionLoader();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('customer');
                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalledWith([], false);
            });

            it('Tests that customer data is checked only after initialization', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    return deferred.promise();
                });
                customer({
                    firstname: "First Name"
                });
                customerSessionLoader();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('customer');
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();

                deferred.resolve();

                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalledWith([], false);
            });

            it('Tests that customer data does not reloads if it has first name defined', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    deferred.resolve();

                    return deferred.promise();
                });
                customer({
                    firstname: "First Name"
                });
                customerSessionLoader();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('customer');
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
            });
        });
    });
});
