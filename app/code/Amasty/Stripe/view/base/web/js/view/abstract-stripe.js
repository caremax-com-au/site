/*browser:true*/
/*global define*/
/*global Stripe*/
define([
    'jquery',
    'mage/translate',
    'uiComponent',
], function ($, $t, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            stripe: null,
            cardElement: null,
            cardMessagesObserver: false,
            token: null,
            response: null,
            paymentIntents: null
        },

        initObservable: function () {
            this._super().observe('cardMessagesObserver');

            return this;
        },

        /**
         * Initialize Stripe element
         */
        initStripe: function (sdkUrl, publicKey) {
            require([sdkUrl], function () {
                // Initialise Stripe
                this.stripe = Stripe(publicKey);

                // Initialise elements
                var elements = this.stripe.elements();

                var style = {
                    base: {
                        color: '#32325d',
                        lineHeight: '18px',
                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    },

                };
                this.cardElement = elements.create('card', {style: style, hidePostalCode: true});
                this.cardElement.mount('#amasty_stripe_card_data');
                this.cardElement.on('change', this.onFieldChange());
            }.bind(this));

            return this;
        },

        /**
         * Return field change event handler
         */
        onFieldChange: function () {
            var errorMessage = this.cardMessagesObserver;

            return function (event) {
                errorMessage(
                    event.error ? event.error.message : false
                );
            };
        },

        /**
         * Return field's error message observable
         */
        getErrorMessageObserver: function () {
            return this.cardMessagesObserver;
        },

        getExpDate: function (savedCard) {
            return this.getExpMonth(savedCard) + '/' + this.getExpYear(savedCard);
        },

        getExpMonth: function (savedCard) {
            return savedCard.three_d_secure
                ? savedCard.three_d_secure.exp_month
                : savedCard.exp_month
                    ? savedCard.exp_month
                    : savedCard.card.exp_month;
        },

        getExpYear: function (savedCard) {
            return savedCard.three_d_secure
                ? savedCard.three_d_secure.exp_year
                : savedCard.exp_year
                    ? savedCard.exp_year
                    : savedCard.card.exp_year;
        },

        getBrand: function (savedCard) {
            return savedCard.three_d_secure
                ? savedCard.three_d_secure.brand
                : savedCard.brand
                    ? savedCard.brand
                    : savedCard.card.brand;
        },

        getClass: function (savedCard) {
            var className = this.getBrand(savedCard);
            className = '-' + className.toLowerCase().replace(/\ .*/, '');

            return className;
        },

        getSourceId: function (savedCard) {
            return savedCard.three_d_secure
                ? savedCard.three_d_secure.card
                : savedCard.id
                    ? savedCard.id
                    : savedCard.card.id;
        },

        getLast4: function (savedCard) {
            return savedCard.three_d_secure
                ? savedCard.three_d_secure.last4
                : savedCard.last4
                    ? savedCard.last4
                    : savedCard.card.last4;
        },

        getThreeDSecure: function (savedCard) {
            return savedCard.three_d_secure
                ? savedCard.three_d_secure.three_d_secure
                : savedCard.card
                    ? savedCard.card.three_d_secure
                    : 'optional';
        }
    });
});