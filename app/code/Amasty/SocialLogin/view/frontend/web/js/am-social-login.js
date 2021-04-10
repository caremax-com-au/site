define([
    "jquery",
    "Amasty_SocialLogin/js/am-reload-header"
], function ($, amReloadHeader) {
    'use strict';

    $.widget('mage.amSocialLoginAjax',  {
        options: {
            redirect_duration: 2000,
            error_selector: '',
            tab_wrapper: '[data-am-js="am-tabs-wrapper"]',
            content_wrapper: '[data-am-js="am-content-wrapper"]'
        },

        _create: function () {
            this.init();
        },

        init: function () {
            this.socialLoginClick();
        },

        socialLoginClick: function () {
            var self = this;

            this.socialLoginObserve();
            if (window.amResult !== undefined) {
                return;
            }

            window.amResult = function (data, context) {
                var parent;
                $(context.options.error_selector).hide();

                if (data.result === 1) {
                    $(context.options.tab_wrapper).hide();

                    parent = $(context.options.content_wrapper).html('')
                        .addClass('amsl-login-success');
                    data.messages.forEach(function (message) {
                        $('<div/>').html(message).appendTo(parent);
                    });
                    parent.show();

                    amReloadHeader.customRedirect(data.redirect_data);
                } else {
                    parent = $(context.options.error_selector);
                    parent.html('');
                    data.messages.forEach(function (message) {
                        $('<div/>').html(message).appendTo(parent);
                    });
                    parent.slideDown();
                    if (data.redirect_data && data.redirect_data.redirectWithError) {
                        amReloadHeader.customRedirect(data.redirect_data);
                    }
                }
            };
        },

        socialLoginObserve: function () {
            var self = this;

            $('[data-am-js="amsl-button"]').unbind('click').click(function (event) {
                self.options.error_selector = $(event.target).parents('.amsl-social-login').find('[data-am-js="am-social-error"]');
                window.addEventListener("message", function(event) {
                    if (event.data['redirect_data']) {
                        window.amResult(event.data, this);
                    }
                }.bind(self));
                window.open(
                    event.currentTarget.href + '&isAjax=true',
                    event.currentTarget.title,
                    self.getPopupParams()
                );
                event.stopPropagation();
                event.preventDefault();

                return false;
            });
        },

        getPopupParams: function (w, h, l, t) {
            var screenX = typeof window.screenX !== 'undefined' ? window.screenX : window.screenLeft,
                screenY = typeof window.screenY !== 'undefined' ? window.screenY : window.screenTop,
                outerWidth = typeof window.outerWidth !== 'undefined' ? window.outerWidth : document.body.clientWidth,
                outerHeight = typeof window.outerHeight !== 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
                width = w ? w : 500,
                height = h ? h : 420,
                left = l ? l : parseInt(screenX + ((outerWidth - width) / 2), 10),
                top = t ? t : parseInt(screenY + ((outerHeight - height) / 2.5), 10);

            return (
                'width=' + width +
                ',height=' + height +
                ',left=' + left +
                ',top=' + top
            );
        }
    });

    return $.mage.amSocialLoginAjax;
});
