define([
    'uiComponent'
], function (Component) {
    'use strict';
    return Component.extend({
        defaults: {
            maxRound: 628,
            modules: {
                parent: "${$.parentName}"
            }
        },

        getLinearGradient: function () {
            return this.parent().widgetId + '-circle-gradient';
        },

        offsetDays: function (maxValue) {
            var totalDays = Math.floor((this.parent().targetTime - this.parent().startTime) / (60 * 60 * 24));
            return maxValue * (totalDays - this.parent().days()) / totalDays;
        },

        offsetHour: function (maxValue) {
            return maxValue * (24 - this.parent().hours()) / 24;
        },

        offsetMin: function (maxValue) {
            return maxValue * (60 - this.parent().min()) / 60;
        },

        offsetSec: function (maxValue) {
            return maxValue * (60 - this.parent().sec()) / 60;
        },

        getRotate: function (value) {
            return ('rotate(' + Math.floor(360 - value) + 'deg').toString();
        }
    });
});
