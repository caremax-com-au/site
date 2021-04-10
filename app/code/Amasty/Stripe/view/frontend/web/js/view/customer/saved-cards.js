/*browser:true*/
/*global define*/
define([
    'jquery',
    'Amasty_Stripe/js/view/abstract-stripe',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
], function ($, Component, $t, messageList) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Stripe/customer/saved-cards',
            sdkUrl: null,
            publicKey: null,
            abstractStripe: null,
            deleteCardUrl: null,
            addCardUrl: null,
            billingAddress: null,
            savedCards: [],
            visible: false,
            isLoading: false,
            stripeTokens: {}
        },

        initObservable: function () {
            this._super().observe('savedCards visible isLoading');

            return this;
        },

        initialize: function () {
            this._super();
            this.savedCards(this.cardsData);
            this.visible(this.cardsData.length);
        },

        /**
         * Initialize Stripe element
         */
        initStripe: function () {
            if (this.billingAddress) {
                this.abstractStripe = this._super(this.sdkUrl, this.publicKey)
            }
        },

        isSaveCardsEnable: function () {
            return this.enableSaveCards;
        },

        deleteClick: function (savedCard) {
            this.isLoading(true);

            var postData = {
                source: savedCard.id,
            };

            $.ajax({
                type: 'POST',
                url: this.deleteCardUrl,
                data: postData,
                dataType: 'json'
            }).success(function (data) {
                this.savedCards(data);
                this.visible(data.length);
            }.bind(this))
                .complete(function () {
                    this.isLoading(false);
                }.bind(this));
        },

        addNewCard: function () {
            this.isLoading(true);

            this.abstractStripe.stripe.createPaymentMethod('card', this.abstractStripe.cardElement, this.billingAddress).then(function (result) {
                if (result.error) {
                    console.log(result.error.message);
                    this.isLoading(false);

                    return;
                }

                var cardKey = result.paymentMethod.id;
                var token = result.paymentMethod.id + ':' + result.paymentMethod.card.brand + ':' + result.paymentMethod.card.last4;
                this.stripeTokens[cardKey] = token;

                $.ajax({
                    type: 'POST',
                    url: this.addCardUrl,
                    data: {source: token},
                    dataType: 'json'
                }).success(function (data) {
                    this.savedCards(data);
                    this.initStripe();
                    this.visible(data.length);
                }.bind(this))
                    .complete(function () {
                        this.isLoading(false);
                    }.bind(this));
            }.bind(this));
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
            return;
        },
    });
});
