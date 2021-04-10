/*browser:true*/
/* global define, FORM_KEY */
define([
    'jquery',
    'mage/translate',
    'Amasty_Stripe/js/view/abstract-stripe',
    'mage/url',
    'mage/backend/notification',
    'mage/cookies'
], function ($, $t, Component, url, notification) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Stripe/customer/order/stripe-form',
            sdkUrl: null,
            publicKey: null,
            abstractStripe: null,
            selectedCard: 'new-card',
            savedCards: [],
            threedSecureAlways: null,
            visible: false,
            isLoading: false,
            currency: null,
            cardSourceId: null,
            dataSecret: null,
            paymentIntent: null,
            secretUrl: null,
            source: null
        },

        initObservable: function () {
            this._super().observe('selectedCard savedCards visible isLoading cardSourceId');

            return this;
        },

        initialize: function () {
            this._super();
            this.savedCards(this.cardsData);
            this.selectedCard.subscribe(function (newValue) {
                if (newValue === 'new-card') {
                    this.visible(true);
                } else {
                    this.visible(false);
                }
            }.bind(this));
            if (this.cardsData.length) {
                this.visible(this.cardsData.length);
            } else {
                this.visible(true);
            }
        },

        isSavedCardsExist: function () {
            return this.savedCards().length > 0;
        },

        /**
         * Initialize Stripe element
         */
        initStripe: function () {
            this.abstractStripe = this._super(this.sdkUrl, this.publicKey)
        },

        /**
         * Place the order
         *
         * @param {Object} data
         */
        placeOrderClick: function () {
            if (!this.abstractStripe.stripe || !this.abstractStripe.cardElement) {
                console.error('Stripe or CardElement not found');
                return false;
            }
            if ($('#edit_form').valid()) {
                $('body').trigger('processStart');
                this.getClientSecret();
            }
        },

        getClientSecret: function () {
            var $wrapper,
                self = this,
                formKey = $.mage.cookies.get('form_key') !== null ? $.mage.cookies.get('form_key') : FORM_KEY;

            $.get(
                url.build(this.secretUrl),
                {
                    form_key: formKey
                },
                function (response) {
                    if (response.success) {
                        this.dataSecret = response.clientSecret;
                        this.paymentIntent = response.paymentIntent;
                        this.actionAfterClientSecret();
                    }
                    if (response.error) {
                        console.log(response);


                        notification().add({
                            error: true,
                            message: response.message,

                            /**
                             * @param {String} message
                             */
                            insertMethod: function (message) {
                                $wrapper = jQuery('<div/>').html(message);

                                jQuery('.page-main-actions').after($wrapper);
                            }
                        });
                        $('body').trigger('processStop');
                    }
                }.bind(this),
                "json"
            );
        },

        getCardData: function() {
            var billingAddress = $('#edit_form').serializeArray();
            if (billingAddress) {
                var firstname = null,
                    lastname = null,
                    cardData = {
                        billing_details: {
                            name: null,
                            phone: null,
                            address: {
                                postal_code: null,
                                country: null,
                                city: null,
                                state: null,
                                line1: null,
                                line2: null
                            }
                        }
                    };
                $.each(billingAddress, function (index, value) {
                    switch (value.name) {
                        case 'order[billing_address][firstname]':
                            firstname = value.value;
                            break;
                        case 'order[billing_address][lastname]':
                            lastname = value.value;
                            break;
                        case 'order[billing_address][telephone]':
                            cardData.billing_details.phone = value.value;
                            break;
                        case 'order[billing_address][postcode]':
                            cardData.billing_details.address.postal_code = value.value;
                            break;
                        case 'order[billing_address][country_id]':
                            cardData.billing_details.address.country = value.value;
                            break;
                        case 'order[billing_address][city]':
                            cardData.billing_details.address.city = value.value;
                            break;
                        case 'order[billing_address][region]':
                            cardData.billing_details.address.state = value.value;
                            break;
                        case 'order[billing_address][street[0]]':
                            cardData.billing_details.address.line1 = value.value;
                            break;
                        case 'order[billing_address][street[1]]':
                            cardData.billing_details.address.line2 = value.value;
                            break;
                    }
                });

                cardData.billing_details.name = firstname + ' ' + lastname;

                return cardData;
            }
        },

        actionAfterClientSecret: function() {
            var cardData = {};
            var cardDataSecret = {
                payment_method_data: this.getCardData()
            };
            var selectedCardData = null;
            if (this.selectedCard() && this.selectedCard() !== 'new-card') {
                cardDataSecret = {
                    payment_method: this.selectedCard()
                };
                selectedCardData = this.selectedCard();
            }

            this.threeDSecureProcess(this.paymentIntent, cardDataSecret, selectedCardData, cardData);
        },

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
            }

            $('body').trigger('processStop');
            order.submit();
        },

        /**
         * Handle error from stripe
         */
        handleStripeError: function (result) {
            $('body').trigger('processStop');
            var message = result.error.message;
            if (result.error.type == 'validation_error') {
                message = $t('Please verify you card information.');
            }
            alert(message);

            return;
        },

        /**
         * Set source
         *
         * @param {String} source
         */
        setSource: function (source) {
            this.cardSourceId(source);
            this.source = source;
        },
    });
});
