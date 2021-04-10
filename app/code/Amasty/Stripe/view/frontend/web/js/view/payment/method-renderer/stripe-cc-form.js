/* browser:true */
define(
    [
        'underscore',
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Amasty_Stripe/js/view/abstract-stripe',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/model/customer',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'mage/url',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (
        _,
        $,
        ko,
        Component,
        AbstractStripe,
        quote,
        additionalValidators,
        fullScreenLoader,
        customer,
        $t,
        messageList,
        url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amasty_Stripe/payment/form',
                source: null,
                paymentMethod: null,
                isCustomerLoggedIn: customer.isLoggedIn,
                abstractStripe: null,
                dataSecret: null,
                paymentIntent: null,
                selectedCard: ko.observable('new-card'),
                visible: ko.observable(function () {
                    return this.selectedCard() === 'new-card';
                }),
                saveCard: ko.observable(false),
                savedCards: ko.observable([])
            },

            initialize: function () {
                this._super();
                var config = window.checkoutConfig.payment[this.getCode()];

                if (!config) {
                    return;
                }

                this.savedCards(config.savedCards);
                this.selectedCard.subscribe(function (newValue) {
                    if (newValue === 'new-card') {
                        this.visible(true);
                    } else {
                        this.visible(false);
                    }
                }.bind(this));
            },

            /**
             * Initialize Stripe element
             */
            initStripe: function () {
                var config = window.checkoutConfig.payment[this.getCode()];

                if (!config) {
                    return;
                }

                this.abstractStripe = AbstractStripe().initStripe(config.sdkUrl, config.publicKey);
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () {
                return this.getCode() === this.isChecked();
            },

            isSaveCardsEnable: function () {
                return window.checkoutConfig.payment.amasty_stripe.enableSaveCards;
            },

            /**
             * Return field's error message observable
             */
            getErrorMessageObserver: function () {
                return AbstractStripe().getErrorMessageObserver();
            },

            /**
             * Return field change event handler
             */
            onFieldChange: function () {
                return AbstractStripe().onFieldChange();
            },

            isSavedCardsExist: function () {
                return this.savedCards().length > 0;
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'source': this.source,
                        'payment_method': this.paymentMethod,
                        'save_card': this.saveCard()
                    }
                };

                return data;
            },

            /**
             * Set source
             *
             * @param {String} source
             */
            setSource: function (source) {
                this.source = source;
            },

            setPaymentMethod: function (paymentMethod) {
                this.paymentMethod = paymentMethod;
            },

            getExpDate: function (savedCard) {
                return AbstractStripe().getExpDate(savedCard);
            },

            getBrand: function (savedCard) {
                return AbstractStripe().getBrand(savedCard);
            },

            getLast4: function (savedCard) {
                return AbstractStripe().getLast4(savedCard);
            },

            getThreeDSecure: function (savedCard) {
                return AbstractStripe().getThreeDSecure(savedCard);
            },

            getClass: function (savedCard) {
                return AbstractStripe().getClass(savedCard);
            },

            getSourceId: function (savedCard) {
                return AbstractStripe().getSourceId(savedCard);
            },

            /**
             * Place the order
             *
             * @param {Object} data
             */
            placeOrderClick: function () {
                fullScreenLoader.startLoader();

                if (!this.validate() || !additionalValidators.validate()) {
                    fullScreenLoader.stopLoader();

                    return false;
                }

                this.getClientSecret();
            },

            /**
             * Process after getting client secret key
             *
             * @returns {boolean}
             */
            actionAfterClientSecret: function () {
                var config = window.checkoutConfig.payment[this.getCode()];

                if (!this.abstractStripe.stripe || !this.abstractStripe.cardElement) {
                    console.error('Stripe or CardElement not found');

                    return false;
                }

                var cardData = {},
                    billingAddress = quote.billingAddress();

                if (billingAddress) {
                    cardData.billing_details = {
                        name: billingAddress.firstname + ' ' + billingAddress.lastname,
                        address: {
                            postal_code: billingAddress.postcode,
                            country: billingAddress.countryId
                        }
                    };

                    if (billingAddress.telephone) {
                        cardData.billing_details.phone = billingAddress.telephone;
                    }

                    if (billingAddress.city) {
                        cardData.billing_details.address.city = billingAddress.city;
                    }

                    if (billingAddress.region) {
                        cardData.billing_details.address.state = billingAddress.region;
                    }

                    if (billingAddress.street && billingAddress.street[0]) {
                        cardData.billing_details.address.line1 = billingAddress.street[0];

                        if (billingAddress.street[1]) {
                            cardData.billing_details.address.line2 = billingAddress.street[1];
                        }
                    }

                    cardData.metadata = { saveCard: this.saveCard() };
                }

                var cardDataSecret = {
                        payment_method_data: {
                            billing_details: {
                                name: billingAddress.firstname + ' ' + billingAddress.lastname,
                                address: {
                                    line1: billingAddress.street[0],
                                    line2: billingAddress.street[1],
                                    city: billingAddress.city,
                                    postal_code: billingAddress.postcode,
                                    country: billingAddress.countryId,
                                    state: billingAddress.region
                                }
                            }
                        }
                    },

                    selectedCardData = null;

                if (this.selectedCard() && this.selectedCard() !== 'new-card') {
                    cardDataSecret = {
                        payment_method: this.selectedCard()
                    };
                    selectedCardData = this.selectedCard();
                }

                this.createPaymentMethod(cardData);
                this.threeDSecureProcess(this.paymentIntent, cardDataSecret, selectedCardData, cardData);
            },

            /**
             * Save new payemnt method
             *
             * @param cardData
             */
            createPaymentMethod: function (cardData) {
                this.abstractStripe.stripe.createPaymentMethod('card', this.abstractStripe.cardElement, cardData).then(function (result) {
                    if (result.error) {
                        console.log(result.error.message);

                        return;
                    }

                    var token = result.paymentMethod.id + ':' + result.paymentMethod.card.brand + ':' + result.paymentMethod.card.last4;

                    this.setPaymentMethod(token);
                }.bind(this));
            },

            /**
             * Create payment intent and get client Secret
             */
            getClientSecret: function () {
                var self = this;

                $.get(
                    url.build('amstripe/checkout_paymentintents/data'),
                    {
                        form_key: $.cookie('form_key')
                    },
                    function (response) {
                        if (response.success) {
                            this.dataSecret = response.clientSecret;
                            this.paymentIntent = response.paymentIntent;
                            this.actionAfterClientSecret();
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
            },

            /**
             * Processing with three D secure V2
             * @param sourceId
             * @param cardDataSecret
             * @param selectedCardData
             * @param cardData
             */
            threeDSecureProcess: function (sourceId, cardDataSecret, selectedCardData, cardData) {
                if (this.selectedCard() && this.selectedCard() !== 'new-card') {
                    this.abstractStripe.stripe.handleCardPayment(this.dataSecret, cardDataSecret).then(function (result) {
                        if (result.error) {
                            return this.handleStripeError(result);
                        }

                        return this.handleStripeSuccess(result, selectedCardData);
                    }.bind(this));
                } else {
                    this.abstractStripe.stripe.handleCardPayment(this.dataSecret, this.abstractStripe.cardElement, cardDataSecret).then(function (result) {
                        if (result.error) {
                            return this.handleStripeError(result);
                        }

                        return this.handleStripeSuccess(result, selectedCardData, cardData);
                    }.bind(this));
                }
            },

            /**
             * Handle success from stripe
             */
            handleStripeSuccess: function (result, selectedCardData, cardData) {
                if (!result.paymentIntent) {
                    this.setSource(result.source.id);
                } else {
                    this.setSource(result.paymentIntent.id);

                    if (this.selectedCard() && this.selectedCard() !== 'new-card') {
                        this.setPaymentMethod(this.selectedCard());
                    }
                }

                fullScreenLoader.stopLoader();
                this.placeOrder();
            },

            /**
             * Handle error from stripe
             */
            handleStripeError: function (result) {
                var message = result.error.message;

                if (result.error.type == 'validation_error') {
                    message = $t('Please verify you card information.');
                }

                messageList.addErrorMessage({
                    message: message
                });
                fullScreenLoader.stopLoader();
            },

            getImageStripe: function () {
                var imageUrl = window.checkoutConfig.payment.amasty_stripe.imageUrl;

                return imageUrl;
            }
        });
    }
);
