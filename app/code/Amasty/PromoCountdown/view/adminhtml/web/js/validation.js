require([
        'jquery',
        'mage/translate',
        'jquery/validate'
    ],
    function ($) {
        $('.validate-date-period-min').change(function () {
            $(this).validation().validation('isValid');
        });

        $('.validate-date-period-max').change(function () {
            $('.validate-date-period-min').validation().validation('isValid');
        });

        $.validator.addMethod(
            'validate-date-period-min',
            function (minValue) {
                var max = $('.validate-date-period-max');

                if (max && max.length && max.first().val()) {
                    return minValue < max.first().val();
                }

                return true;
            },
            $.mage.__('The Start Time can not be more or equal than the End Time')
        );
    }
);
