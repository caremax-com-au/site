/*
 * @author      Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'matchMedia',
    'owl.carousel',
], function ($, mediaCheck) {
    'use strict';

    $.widget('mage.OXmobileNoSlider', {
        options: {
            mediaBreakpoint: 768,
            items: 1,
        },
        _create: function () {
            this.zoom = '';
            mediaCheck({
                media: '(min-width:' + this.options.mediaBreakpoint + 'px)',
                entry: $.proxy(this._toggleDesktopMode, this),
                exit: $.proxy(this._toggleMobileMode, this),
            });
        },
        _toggleDesktopMode: function () {
            this.element.trigger('destroy.owl.carousel').removeClass('owl-carousel owl-loaded').find('.owl-stage-outer').children().unwrap();
            this.element.prepend(this.zoom);
        },
        _toggleMobileMode: function () {
            let $zoom = this.element.find('#ox-zoom-cursor');
            if ($zoom.length) {
                this.zoom = $zoom.detach()
            }
            let options = $.extend({}, this.options);
            this.element.addClass('owl-carousel').owlCarousel(options);
        },
    });

    return $.mage.OXmobileNoSlider;
});