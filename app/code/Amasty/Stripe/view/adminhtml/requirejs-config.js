var config = {
    config: {
        mixins: {
            'Magento_Sales/order/create/scripts': {
                'Amasty_Stripe/js/view/customer/order/scripts-mixin': !window.amasty_stripe_disabled
            }
        }
    }
};