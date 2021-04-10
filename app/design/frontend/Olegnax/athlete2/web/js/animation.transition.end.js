/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
], function ($) {
    'use strict';
    var OXgetMaxOfArray = function (array) {
        return Math.max.apply(null, array);
    };
    $.fn.onAnimationAction = function (type, callback, animationName, useTimer) {
        var $this = $(this),
            animations,
            events,
            type = type || 'end';
        switch (type) {
            case 'start':
                animations = ['animationstart', 'oAnimationStart', 'msAnimationStart', 'webkitAnimationStart'];
                break;
            case 'end':
            default:
                animations = ['animationend', 'oAnimationEnd', 'msAnimationEnd', 'webkitAnimationEnd'];
        }

        animations = animations.map(function (a) {
            return a + '.animation' + (animationName ? '-' + (Array.isArray(animationName) ? animationName.join('') : animationName
            ) : '')
        });
        events = animations.join(' ');

        return $this.off(events).on(events, function (event) {
            var event = event.originalEvent || event,
                $this = $(this),
                path = event.path || (event.composedPath && event.composedPath());
            if (path) {
                path = path.shift();
                if (path && !$(path).is($this)) {
                    return;
                }
            }
            if (animationName && (("object" === typeof animationName && -1 === animationName.indexOf(event.animationName)) || ("string" === typeof animationName && !(new RegExp('^' + animationName, 'i')).test(event.animationName)))) {
                return;
            }
            $this.off(events);
            callback && callback.apply($this, [event, event.animationName, $this.css(event.animationName)]);
        });
    };
    $.fn.onTransitionAction = function (type, callback, propertyName, useTimer) {
        var $this = $(this),
            transitionduration = OXgetMaxOfArray(Object.values($this.css(['transition-duration', '-o-transition-duration', '-ms-transition-duration', '-webkit-transition-duration', '-moz-transition-duration']) || {}).filter(function (a) {
                return a || '0s' == a
            }).filter(function (v, i, a) {
                return a.indexOf(v) === i
            }).map(function (a) {
                if (/^([0-9\.]+)s$/i.exec(a)) {
                    return parseFloat(a.replace(/[^0-9\.]/g, ''));
                } else if (/^([0-9\.]+)ms$/i.exec(a)) {
                    return parseFloat(a.replace(/[^0-9\.]/g, '')) / 1000;
                }
                return parseFloat(a);
            })),
            transitiondelay = OXgetMaxOfArray(Object.values($this.css(['transition-delay', '-o-transition-delay', '-ms-transition-delay', '-webkit-transition-delay', '-moz-transition-delay']) || {}).filter(function (a) {
                return a || '0s' == a
            }).filter(function (v, i, a) {
                return a.indexOf(v) === i
            }).map(function (a) {
                if (/^([0-9\.]+)s$/i.exec(a)) {
                    return parseFloat(a.replace(/[^0-9\.]/g, ''));
                } else if (/^([0-9\.]+)ms$/i.exec(a)) {
                    return parseFloat(a.replace(/[^0-9\.]/g, '')) / 1000;
                }
                return parseFloat(a);
            })),
            transitionpropertys = Object.values($(this).css(['transition-property', '-o-transition-property', '-ms-transition-property', '-webkit-transition-property', '-moz-transition-property']) || {}).filter(function (a) {
                return a || 'none' == a
            }).filter(function (v, i, a) {
                return a.indexOf(v) === i
            }).map(function (a) {
                return a.split(', ')
            }),
            transitions,
            events,
            timerId,
            type = type || 'end';
        transitionpropertys = (function (a) {
            var b = [];
            $.each(a, function (i, v) {
                $.each(v, function (j, k) {
                    b.push(k);
                });
            });
            return b;
        })(transitionpropertys);
        switch (type) {
            case 'start':
                transitions = ['transitionstart', 'oTransitionStart', 'msTransitionStart', 'webkitTransitionStart'];
                break;
            case 'end':
            default:
                transitions = ['transitionend', 'oTransitionEnd', 'msTransitionEnd', 'webkitTransitionEnd'];
        }
        if (!transitionduration || !transitionpropertys.length) {
            return $this;
        }

        if (-1 === transitionpropertys.indexOf('all')) {
            if ("undefined" === typeof propertyName) {

            } else {

            }

            if (1 < transitionpropertys.length) {
                if ("undefined" === typeof propertyName) {
                    propertyName = transitionpropertys;
                } else if (-1 === transitionpropertys.indexOf(propertyName)) {
                    return $this;
                }
            } else if ("undefined" === typeof propertyName) {
                propertyName = transitionpropertys.shift();
            }
        }

        transitions = transitions.map(function (a) {
            return a + '.transition' + (propertyName ? '-' + (Array.isArray(propertyName) ? propertyName.join('') : propertyName
            ) : '')
        });
        events = transitions.join(' ');
        if (transitionduration && useTimer) {
            var _transitionduration = transitionduration + transitiondelay;
            _transitionduration = _transitionduration * (1 > _transitionduration ? 2 : 1.1);
            timerId = setTimeout(function () {
                $this.off(events);
                console.warn('onTransitionAction: Timer runs before event!', $this);
                callback && callback.apply($this);
            }, _transitionduration);
        }

        return $this.off(events).on(events, function (event) {
            var event = event.originalEvent || event,
                $this = $(this),
                path = event.path || (event.composedPath && event.composedPath());
            if (path) {
                path = path.shift();
                if (path && !$(path).is($this)) {
                    return;
                }
            }
            if (propertyName && (("object" === typeof propertyName && -1 === propertyName.indexOf(event.propertyName)) || ("string" === typeof propertyName && propertyName !== event.propertyName))) {
                return;
            }

            if (timerId) {
                clearTimeout(timerId);
            }
            $this.off(events);
            callback && callback.apply($this, [event, event.propertyName, $this.css(event.propertyName)]);
        });
    };
    $.fn.onAnimationEnd = function (callback, animationName, useTimer) {
        $(this).onAnimationAction('end', callback, animationName, useTimer);
    };
    $.fn.onAnimationStart = function (callback, animationName, useTimer) {
        $(this).onAnimationAction('start', callback, animationName, useTimer);
    };
    $.fn.onTransitionStart = function (callback, propertyName, useTimer) {
        $(this).onTransitionAction('start', callback, propertyName, useTimer);
    };
    $.fn.onTransitionEnd = function (callback, propertyName, useTimer) {
        $(this).onTransitionAction('end', callback, propertyName, useTimer);
    };
});