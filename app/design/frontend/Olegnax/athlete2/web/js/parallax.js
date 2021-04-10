define( [
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
], function ( $ ) {
    'use strict';
    $.fn.centred = function ( so ) {
        var sd = {
            v: null,
            h: null
        },
            s = $.extend( true, { }, sd, so );
        return $( this ).each( function () {
            var a = $( this ),
                h = a.outerHeight(),
                w = a.outerWidth(),
                h2 = h / 2,
                w2 = w / 2;
            if ( 'bottom' === s.v || 'top' === s.v ) {
                var hh = 'top' === s.v ? Math.ceil( h2 ) : Math.ceil( h2 );
                $( this ).css( 'margin-' + s.v, -hh );
            }
            if ( 'left' === s.h || 'right' === s.h ) {
                var ww = 'right' === s.h ? Math.ceil( w2 ) : Math.ceil( w2 );
                $( this ).css( 'margin-' + s.h, -ww );
            }
        } );
    }

    $.widget( 'mage.OxParallax', {
        options: {
            height: null,
            type: 1,
            offset: 0,

            classText: '.parallax-text',
            classBackground: '.bg-layer',
            classPrefixParallax: 'effect-parallax-',
        },

        _create: function () {
            this.options.height = parseInt( this.options.height, 10 );
            this.options.type = parseInt( this.options.type, 10 );
            this.text = this.element.find( this.options.classText );
            var $this_text_h = this.text.height(),
                effect = 'effect' + this.options.type;
            if ( $this_text_h > this.options.height ) {
                this.options.height = $this_text_h + 80;
            }
            this.element.addClass( this.options.classPrefixParallax + this.options.type ).stop().animate( {
                height: this.options.height
            }, 1200 );
            this.text.centred( {
                v: 'top'
            } ).stop().animate( {
                opacity: 1
            }, 900 );
            if ( 'function' == typeof this[effect] ) {
                this[effect]();
            }
            var update = this.update;
            this.window.on( 'scroll resize', function () {

                requestAnimationFrame( update );
            } );

        },
        update: function () {},
        effect1: function () {
            var _self = this,
                $window = this.window;

            this.update = function () {
                var scrollTop = $window.scrollTop(),
                    _scrollTop = scrollTop,
                    _height = _self.element.offset().top + parseInt( _self.options.offset, 10 ),
                    $this_text_k = 1;
                _scrollTop = _scrollTop - _height;

                if ( 0 > _scrollTop ) {
                    _scrollTop = 0;
                }
                $this_text_k = _scrollTop / ( _self.options.height - _height );
                _self.element.css( {
                    'transform': 'translateY(' + _scrollTop * 0.3 + 'px)'
                } );
                _self.text.css( {
                    transform: 'translateY(' + scrollTop * 0.09 * ( 1 + $this_text_k ) + 'px)',
                    opacity: 1 - $this_text_k
                } ).toggle( 1 - $this_text_k > 0.01 );
            }
        },
        effect2: function () {
            this.background = this.element.find( this.options.classBackground );
            var _self = this,
                $window = this.window,
                speed = 1.5,
                $this_bg_h = _self.options.height * ( speed - 1 ),
                $this_bg_st = ( speed - 1 ) * 100;
            _self.background.css( 'height', 'calc(100% + ' + $this_bg_h + 'px)' );
            update = function () {
                var $window_h = $window.height(),
                    $window_st = $window.scrollTop(),
                    $this_ost = _self.element.offset().top,
                    _height = _self.element.offset().top + parseInt( _self.options.offset, 10 ),
                    ty = ( $window_st ) / ( $this_ost + _self.options.height );
                ty = 0 > ty ? 0 : ty;
                if ( 0 > ty || 1 < ty ) {
                    return;
                }
                _self.background.css( {
                    'top': $this_bg_st * ( ty - 1 ) + '%'
                } );
                return true;
            }
        },
    } );

    return $.mage.OxParallax;
} );