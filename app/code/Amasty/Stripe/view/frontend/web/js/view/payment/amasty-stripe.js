define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        var config = window.checkoutConfig.payment,
            amastyStripeCode = 'amasty_stripe';

        if (config[amastyStripeCode] && config[amastyStripeCode].isActive) {
            rendererList.push(
                {
                    type: amastyStripeCode,
                    component: 'Amasty_Stripe/js/view/payment/method-renderer/stripe-cc-form'
                }
            );
        }

        return Component.extend({});
    }
);
