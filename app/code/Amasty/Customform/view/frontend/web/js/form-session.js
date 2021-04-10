define([
    'jquery',
    'jquery/validate',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use_strict';

    $.widget('mage.amFormSession', {
        options: {
            urlSession: '',
            formId: 0,
            selectors: {
                input: '.form-control, .amform-date, .amform-time',
                field: '.field'
            }
        },
        classes: {
            hasContent: '-has-content',
            active: '-active',
            error: '-error'
        },
        _create: function () {
            this.initialization();
        },

        initialization: function () {
            var self = this;

            self.animateField();

            $.ajax({
                url: self.options.urlSession,
                dataType: 'json',
                type: 'post',
                data: {'form_id': self.options.formId},
                success: function(response) {
                    self.checkFieldType(response);
                },
                error: function(error) {
                    console.log(error)
                }
            });
        },

        checkFieldType: function (fields) {
            var formData = this.element,
                field = '';

            $.each(fields, function (name, value) {
                field = formData.find('[data-amcform-js="' + name + '"]');

                if (field.length) {
                    switch (field.attr('type')) {
                        case 'select':
                            var optionItems = field.children();
                            $.each(optionItems, function (item, option) {
                                $(option).prop('selected', false);
                                var currentOptValue = $(option).val();
                                if (currentOptValue === value || (Array.isArray(value) && value.includes(currentOptValue))) {
                                    $(this).prop('selected', true);
                                }
                            });
                            break;
                        case 'radio':
                            var checkedField = value.split('-'),
                                selector = name + '-' + (checkedField[1] - 1);

                            $('#' + selector).prop('checked', true);
                            break;
                        case 'checkbox':
                            field.prop('checked', false);
                            $.each(value, function (index, val) {
                                var checkedField = val.split('-'),
                                    selector = name + '-' + (checkedField[1] - 1);

                                $('#' + selector).prop('checked', true);
                            });
                            break;
                        default:
                            field.val(value);
                    }
                }
            });
        },

        animateField: function () {
            var self = this,
                input = $(self.options.selectors.input),
                activeClass = self.classes.active,
                hasContentClass = self.classes.hasContent;

            input.each(function () {
                if (this.value) {
                    $(this).closest(self.options.selectors.field).addClass(hasContentClass);
                }
            });

            input.on('focusin', function() {
                var parent = $(this).closest(self.options.selectors.field);
                parent.addClass(activeClass);
            });

            input.on('focusout', function() {
                var parent = $(this).closest(self.options.selectors.field);
                parent.removeClass(activeClass);
            });

            input.on('change', function() {
                var parent = $(this).closest(self.options.selectors.field);

                if (this.value) {
                    parent.addClass(hasContentClass);
                } else {
                    parent.removeClass(hasContentClass);
                }
            })
        }
    });

    return $.mage.amFormSession;
});
