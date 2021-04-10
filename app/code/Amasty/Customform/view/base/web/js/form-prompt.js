define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, confirm, $t) {
    'use strict';

    $.widget('mage.amcformPrompt', {
        options: {
            modalClass: 'amcform-popup-block',
            responsive: true,
            title: $t('This form can be submitted only once. Ready to proceed?'),
            cancellationLink: '',
            action: {},
            isShowed: false
        },

        _create: function () {
            var self = this,
                options = this.options,
                form = $(this.element).closest('.amform-form');

            options.buttons = [{
                text: $t('No'),
                class: 'amcform-button -primary',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $t('Yes'),
                class: 'amcform-button -fill',
                click: function () {
                    this.closeModal();
                    form.submit();
                }
            }];

            this.element.click(function (e) {
                e.preventDefault();
                if (!self.options.isShowed) {
                    confirm(options);
                    self.options.isShowed = true;
                } else {
                    form.submit();
                }
            });
        }
    });

    return $.mage.amcformPrompt
});
