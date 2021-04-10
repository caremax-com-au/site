define([
    'Amasty_SocialLogin/vendor/amcharts/amcharts',
], function () {
    'use strict';

    return function(config) {
        require([
            'jquery',
            'uiRegistry',
            "Amasty_SocialLogin/vendor/amcharts/pie"
        ], function ($, registry) {
            var dataProvider = config.dataProvider,
                isInitAmchart = false;

            registry.get('customer_listing.customer_listing.customer_columns', function () {
                registry.get('customer_listing.customer_listing_data_source').on('reloaded', function () {
                    if (!isInitAmchart) {
                        $('[data-amsl="loading"]').removeClass('-loading');

                        var chart = AmCharts.makeChart("amslogin-pie", {
                            "type": "pie",
                            "balloonText": "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
                            "labelText": "[[title]]<br>[[percents]]%",
                            "pullOutRadius": "10%",
                            "startRadius": "25%",
                            "baseColor": "",
                            "colors": [
                                "#2dca9b",
                                "#adc9ff",
                                "#5b81cc",
                                "#3fd7d7",
                                "#6fbdff",
                                "#98dc6e",
                                "#e1db52",
                                "#856ad2"
                            ],
                            "groupedAlpha": 0,
                            "hoverAlpha": 0.84,
                            "labelTickAlpha": 0.5,
                            "pullOutEffect": "easeOutSine",
                            "startEffect": "easeOutSine",
                            "startDuration": 0.5,
                            "fontFamily": "'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif",
                            "fontSize": 12,
                            "theme": "default",
                            "dataProvider": dataProvider,
                            "valueField": "value",
                            "titleField": "label",
                            "touchClickDuration": 1,
                            "allLabels": [],
                            "titles": [],
                            "balloon": {
                                "fixedPosition": true
                            },
                            "export": {
                                "enabled": true
                            }
                        });

                        isInitAmchart = true;
                    }
                });
            });
        });
    }
});
