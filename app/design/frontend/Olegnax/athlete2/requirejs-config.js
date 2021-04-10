/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            "jquery/hoverintent": "lib/jquery-hoverintent/jquery.hoverIntent.min",
            "velocity": "lib/velocity/velocity.min",
            "jquery/animation.transition.end": "js/animation.transition.end",
            "OXsticky": "js/sticky",
            "OXmodal": "js/modal",
            "OxParallax": "js/parallax",
            "OXmodalMinicart": "js/modal-minicart",
            "OXmodalWishlist": "js/modal-wishlist",
            "mobileMenu": "js/mobile-menu",
            "Athlete2/modal": "js/modal",
            "jquery/lazyload": "js/jquery.lazyload",
            "tippy": "js/tippy.min",
            "owl.carousel": 'js/owl.carousel/owl.carousel.min',
            "AtOwlCarousel": 'js/owl.carousel',
            "AtProductValidate": 'js/validate-product',
            'AtloopOwlAddtocart': 'js/loopaddtocart-owl.carousel',
            "waypoints": "js/waypoints",
            "sticky-sidebar": "js/sticky-sidebar",
            "oxslide": "Magento_Bundle/js/oxslide",
            "photoswipe": "lib/photoswipe/photoswipe",
            "photoswipe-ui": "lib/photoswipe/photoswipe-ui-default",
            "photoswipe-init": "js/photoswipe",
            "OXExpand": "js/expand",
            "OXmobileNoSlider": "js/mobile-noslider",
        }
    },
    "paths": {
        "waypoints": "js/waypoints",
        "jquery/lazyload": "js/jquery.lazyload"
    },
    "shim": {
        "js/waypoints": ["jquery"],
        "js/jquery.lazyload": ["jquery"]
    },
    config: {
        mixins: {
            'Magento_Catalog/js/catalog-add-to-cart': {
                'js/mixins/catalog-add-to-cart': true
            },
            'Cynoinfotech_FreeShippingMessage/js/catalog-add-to-cart': {
                'js/mixins/catalog-add-to-cart-CFSM': true
            }
        }
    }
};
