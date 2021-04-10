define(
    [
        'jquery',
        'uiRegistry'
    ],
    function ($, registry) {
        'use strict';

        return function () {
            var originalSubmit = window.AdminOrder.prototype.submit;

            window.AdminOrder.prototype.submit = function () {
                var stripeForm = registry.get('amasty-stripe-saved-cards');
                if ($('#p_method_amasty_stripe').attr('checked') && typeof stripeForm.source === 'undefined') {
                    stripeForm.placeOrderClick();
                } else {
                    originalSubmit.apply(this);
                }
            };
        };
    }
);
