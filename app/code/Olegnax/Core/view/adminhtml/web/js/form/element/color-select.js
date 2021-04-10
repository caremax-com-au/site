/*
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) $year Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'jquery',
    'jquery/colorpicker/js/colorpicker'
], function (Element, utils, $) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            label: '',
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },
        initialize: function () {
            this._super();
        },
        initColorPickerCallback: function (element) {
            var self = this,
                $element = $(element);
            $element.on('change', function () {
                let $this = $(this);
                $this.css('background-color', $this.val());
            });
            $element.trigger('change');
            $(element).ColorPicker({
                onSubmit: function (hsb, hex, rgb, el) {
                    self.value('#' + hex);
                    $(el).trigger('change').ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                }
            }).bind('keyup', function () {
                $(this).ColorPickerSetColor(this.value);
            });
        }
    });
});