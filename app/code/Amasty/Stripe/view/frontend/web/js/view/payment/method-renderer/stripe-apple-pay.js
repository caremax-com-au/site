/*
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */

/* browser:true */
/* global Stripe */
define(
    [
        'jquery',
        'Amasty_Stripe/js/view/payment/method-renderer/stripe-cc-form',
        'Magento_Checkout/js/model/quote',
        'mage/translate',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Ui/js/model/messageList',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        $,
        Component,
        quote,
        $t,
        totals,
        resourceUrlManager,
        storage,
        redirectOnSuccessAction,
        messageList,
        url,
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            item: {
                method: 'amasty_stripe'
            },
            selectedAddress: {},
            selectedShippingMethod: '',
            dataSecret: null,
            paymentIntent: null,
            defaults: {
                template: 'Amasty_Stripe/payment/method-renderer/stripe-apple-pay',
                source: null,
                stripe: null,
                pennyCurrencies: [
                    'bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
                    'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof'
                ]
            },

            /**
             * @returns {exports.initialize}
             */
            initialize: function () {
                this._super();

                return this;
            },

            /**
             * @returns {void}
             */
            initStripe: function () {
                var config = window.checkoutConfig.payment[this._getCode()];

                if (!config || !config.isApplePayEnabled) {
                    return;
                }

                require([ config.sdkUrl ], function () {
                    // Initialise Stripe
                    this.stripe = Stripe(config.publicKey);

                    // Initialise elements
                    var elements = this.stripe.elements(),
                        quoteTotals = quote.totals();

                    this.paymentRequest = this.stripe.paymentRequest({
                        country: 'US',
                        currency: quoteTotals.quote_currency_code.toLowerCase(),
                        requestPayerName: true,
                        requestPayerEmail: true,
                        requestShipping: !quote.isVirtual(),
                        total: {
                            label: totals.getSegment('grand_total').title,
                            amount: 0,
                        }
                    });
                    this._setPaymentDynamicData();

                    var prButton = elements.create('paymentRequestButton', {
                        paymentRequest: this.paymentRequest
                    });

                    // Check the availability of the Payment Request API first.
                    this.paymentRequest.canMakePayment().then(function (result) {
                        var button = document.getElementById('payment-request-button');

                        if (result) {
                            prButton.mount('#payment-request-button');
                            button.insertAdjacentHTML(
                                'afterend',
                                '<div id="apple-pay-separator" style="text-align: center; padding: 10px 0">'
                                    + $t('OR') + '</div>'
                            );
                            this._subscribeForShippingAddressChange()
                                ._subscribeForShippingOptionChange()
                                ._subscribeForSource()
                                ._subscribeForQuoteChange()
                                ._setPaymentDynamicData();
                        } else {
                            button.style.display = 'none';
                        }
                    }.bind(this));
                }.bind(this));
            },

            /**
             * @returns {this}
             */
            _subscribeForShippingAddressChange: function () {
                this.paymentRequest.on('shippingaddresschange', function (ev) {
                    if (ev.shippingAddress.country !== 'US') {
                        ev.updateWith({ status: $t('invalid_shipping_address') });
                    } else {
                        // Perform server-side request to fetch shipping options
                        this._decorateAddress(ev.shippingAddress);

                        var payload = JSON.stringify({ address: this.selectedAddress });

                        storage.post(this._getUrlForEstimationShippingMethods(quote), payload, false)
                            .fail(function () {
                                ev.updateWith({
                                    status: 'fail',
                                    shippingOptions: []
                                });
                            })
                            .success(function (result) {
                                ev.updateWith({
                                    status: 'success',
                                    shippingOptions: this._decorateShippingRates(result[0]),
                                    displayItems: this._decorateDisplayItemsResponse(result[1]),
                                    total: this._decorateGrandTotal(result[1])
                                });
                            }.bind(this));
                    }
                }.bind(this));

                return this;
            },

            /**
             * @returns {this}
             */
            _decorateAddress: function (address) {
                if (address) {
                    this.selectedAddress = {
                        'street': address.addressLine,
                        'city': address.city,
                        'region': address.region,
                        'country_id': address.country,
                        'postcode': address.postalCode,
                        'company': address.organization,
                        'telephone': address.phone,
                        'firstname': address.recipient.split(' ')[0] || '',
                        'lastname': address.recipient.split(' ')[1] || ''
                    };
                }

                return this;
            },

            /**
             * @returns {this}
             */
            _subscribeForShippingOptionChange: function () {
                this.paymentRequest.on('shippingoptionchange', function (ev) {
                    this.selectedShippingMethod = ev.shippingOption.id;

                    var shippingOption = ev.shippingOption.id.split('|'),
                        payload = JSON.stringify({
                            carrierCode: shippingOption[0],
                            methodCode: shippingOption[1],
                            address: this.selectedAddress
                        });

                    storage.put(this._getUrlForSelectingShippingMethod(quote), payload, false)
                        .fail(function () {
                            ev.updateWith({
                                status: 'fail',
                                shippingOptions: []
                            });
                        })
                        .success(function (result) {
                            ev.updateWith({
                                status: 'success',
                                displayItems: this._decorateDisplayItemsResponse(result),
                                total: this._decorateGrandTotal(result)
                            });
                        }.bind(this));
                }.bind(this));

                return this;
            },

            /**
             * Handle error from stripe
             */
            handleStripeError: function (result) {
                var message = result.error.message;

                if (result.error.type === 'validation_error') {
                    message = $t('Please verify you card information.');
                }

                messageList.addErrorMessage({
                    message: message
                });
                this.dataSecret = null;
                fullScreenLoader.stopLoader();
                return;
            },

            getClientSecret: function () {
                if (!this.dataSecret) {
                    var self = this;

                    return $.get(
                        url.build('amstripe/checkout_paymentintents/data'),
                        {
                            form_key: $.cookie('form_key')
                        },
                        function (response) {
                            if (response.success) {
                                this.dataSecret = response.clientSecret;
                                this.paymentIntent = response.paymentIntent;
                            }

                            if (response.error) {
                                console.log(response);
                                self.messageContainer.addErrorMessage({
                                    message: response.message
                                });
                                fullScreenLoader.stopLoader();
                            }
                        }.bind(this),
                        'json'
                    );
                }
            },
            processThreeDSecureV2: function (result) {
                if (result.error) {
                    return this.handleStripeError(result);
                } else {
                    this._decorateAddress(result.shippingAddress);
                    this.setSource(result.paymentIntent.id);
                    this.isPlaceOrderActionAllowed(false);

                    this.getPlaceOrderDeferredObject()
                        .fail(
                            function (result) {
                                this.isPlaceOrderActionAllowed(true);
                                result.complete('fail');

                                this.handleStripeError({
                                    error: {
                                        type: 'validation_error'
                                    }
                                });
                            }.bind(this)
                        ).done(
                        function () {
                            this.afterPlaceOrder();

                            if (this.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }.bind(this)
                    );

                    return true;
                }
            },

            /**
             * @returns {this}
             */
            _subscribeForSource: function () {
                this.paymentRequest.on('paymentmethod', function (ev) {
                    fullScreenLoader.startLoader();
                    this.getClientSecret().always(function () {
                        this.stripe.confirmCardPayment(
                            this.dataSecret,
                            { payment_method: ev.paymentMethod.id },
                            { handleActions: false }
                        ).then(function (confirmResult) {
                                if (confirmResult.error) {
                                    ev.complete('fail');
                                } else {
                                    ev.complete('success');

                                    if (confirmResult.paymentIntent.status === "requires_action") {
                                        // Let Stripe.js handle the rest of the payment flow.
                                        this.stripe.confirmCardPayment(this.dataSecret).then(function (result) {
                                            this.processThreeDSecureV2(result);
                                        }.bind(this));
                                    } else {
                                        this.processThreeDSecureV2(confirmResult);
                                    }
                                }
                            }.bind(this)
                        );
                    }.bind(this));
                }.bind(this));

                return this;
            },

            /**
             * @returns {Object}
             */
            getData: function () {
                var data = this._super();

                data.additional_data = data.additional_data || {};
                data.additional_data.apple_pay = JSON.stringify({
                    selectedAddress: this.selectedAddress,
                    selectedShippingMethod: this.selectedShippingMethod
                });

                return data;
            },

            /**
             * @returns {this}
             */
            _subscribeForQuoteChange: function () {
                quote.totals.subscribe(this._setPaymentDynamicData.bind(this));

                return this;
            },

            /**
             * @returns {this}
             */
            _setPaymentDynamicData: function () {
                var data = {
                    total: {
                        label: totals.getSegment('grand_total').title,
                        amount: parseInt((this.getCorrectAmount(totals.getSegment('grand_total').value)), 10),
                    },
                    displayItems: this._decorateDisplayItems()
                };

                this.paymentRequest.update(data);

                return this;
            },

            /**
             * @returns {string}
             */
            _getCode: function () {
                return 'amasty_stripe';
            },

            /**
             * @returns {Object}
             */
            _decorateGrandTotal: function (displayItems) {
                var total = {};

                displayItems.forEach(function (item) {
                    if (item.code === 'grand_total') {
                        total.label = item.title;
                        total.amount = this.getCorrectAmount(item.value);
                    }
                }.bind(this));

                return total;
            },

            _decorateDisplayItemsResponse: function (displayItems) {
                var resultArray = [];

                displayItems.forEach(function (item) {
                    resultArray.push({
                        label: item.title,
                        amount: this.getCorrectAmount(item.value)
                    });
                }.bind(this));

                return resultArray;
            },

            /**
             * @returns {Array}
             */
            _decorateDisplayItems: function (shippingPrice) {
                var i,
                    total,
                    amount,
                    resultArray = [];

                if (!totals.totals()) {
                    return resultArray;
                }

                for (i in totals.totals()['total_segments']) { // eslint-disable-line guard-for-in
                    total = totals.totals()['total_segments'][i];

                    if (!total.value && ['grand_total', 'shipping'].indexOf(total.code) === -1) {
                        continue;
                    }

                    amount = this.getCorrectAmount(total.value);

                    resultArray.push({
                        label: total.title,
                        amount: parseInt(amount, 10),
                        pending: total.code === 'shipping' && shippingPrice === undefined
                    });
                }

                return resultArray;
            },

            /**
             * @returns {Array}
             */
            _decorateShippingRates: function (rates) {
                var decoratedRates = [];

                rates.forEach(function (rate) {
                    decoratedRates.push({
                        id: rate['carrier_code'] + '|' + rate['method_code'],
                        label: rate['carrier_title'] + ' - ' + rate['method_title'],
                        amount: this.getCorrectAmount(rate['amount'])
                    });
                }.bind(this));

                // set first shipping method as default
                if (decoratedRates[0]) {
                    this.selectedShippingMethod = decoratedRates[0].id;
                }

                return decoratedRates;
            },

            /**
             * @param {Object} quote
             * @return {*}
             */
            _getUrlForEstimationShippingMethods: function (quote) {
                var params = {};

                if (resourceUrlManager.getCheckoutMethod() == 'guest') {
                    params = { quoteId: quote.getQuoteId() };
                }

                var urls = {
                    'guest': '/guest-carts/:quoteId/estimate-shipping-methods-apple-pay',
                    'customer': '/carts/mine/estimate-shipping-methods-apple-pay'
                };

                return resourceUrlManager.getUrl(urls, params);
            },

            /**
             * @param {Object} quote
             * @return {*}
             */
            _getUrlForSelectingShippingMethod: function (quote) {
                var params = {};

                if (resourceUrlManager.getCheckoutMethod() == 'guest') {
                    params = { quoteId: quote.getQuoteId() };
                }

                var urls = {
                    'guest': '/guest-carts/:quoteId/select-shipping-method-apple-pay',
                    'customer': '/carts/mine/select-shipping-method-apple-pay'
                };

                return resourceUrlManager.getUrl(urls, params);
            },

            /**
             * ApplePay requires to pass the price in cents
             *
             * @param float amount
             * @returns {number}
             */
            getCorrectAmount: function (amount) {
                var quoteTotals = quote.totals();

                if (!this.pennyCurrencies.includes(quoteTotals.quote_currency_code.toLowerCase())) {
                    amount = amount * 100;
                }

                return Math.round(amount);
            }
        });
    }
);
