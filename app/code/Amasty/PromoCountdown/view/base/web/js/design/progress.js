define([
    'uiComponent'
], function (Component) {
    'use strict';
    return Component.extend({
        defaults: {
            modules: {
                parent: "${$.parentName}"
            }
        },

        getCurrentPercent: function () {
            var totalSeconds = this.parent().targetTime - this.parent().startTime;

            return (1 - this.parent().secondsLeft() / totalSeconds) * 100;
        },

        getCurrentPercentString: function () {
            return this.getCurrentPercent() + '%';
        }
    });
});
