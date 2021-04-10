define([
    'uiComponent'
], function (Component) {
    'use strict';
    return Component.extend({
        defaults: {
            maxRound: 157,
            modules: {
                parent: "${$.parentName}"
            }
        },

        offsetDays: function () {
            var totalDays = Math.floor((this.parent().targetTime - this.parent().startTime) / (60 * 60 * 24));
            return this.maxRound * (totalDays - this.parent().days()) / totalDays;
        },

        offsetHour: function () {
            return this.maxRound * (24 - this.parent().hours()) / 24;
        },

        offsetMin: function () {
            return this.maxRound * (60 - this.parent().min()) / 60;
        },

        offsetSec: function () {
            return this.maxRound * (60 - this.parent().sec()) / 60;
        }
    });
});
