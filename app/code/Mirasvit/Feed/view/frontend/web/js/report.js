define([
    'jquery',
    'jquery/jquery.cookie'
], function ($) {
    'use strict';

    $.widget('mirasvit.feedReport', {
        options: {
            url: null
        },

        _create: function () {
            var feedId = $.urlParam('ff');
            var product = $.urlParam('fp');
            var currentDate = new Date();
            var session = $.cookie('feed_session');

            if (!session) {
                session = '' + Math.floor(currentDate.getTime() / 1000) + Math.floor(Math.random() * 10000001);
            }

            if (session && feedId > 0 && product > 0) {
                $.cookie('feed_session', session, {expires: 365, path: '/'});
                $.cookie('feed_id', feedId, {expires: 365, path: '/'});

                var url = this.options.url + '?rnd=' + Math.floor(Math.random() * 10000001) + "&feed=" + feedId + "&session=" + session + "&product=" + product;
                $.ajax(url);
            }
        }
    });

    $.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        } else {
            return results[1] || 0;
        }
    };

    return $.mirasvit.feedReport;
});