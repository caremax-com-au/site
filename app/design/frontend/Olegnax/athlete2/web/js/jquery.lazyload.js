/*!
 * Lazy Load - JavaScript plugin for lazy loading images
 *
 * Copyright (c) 2007-2019 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://appelsiini.net/projects/lazyload
 *
 * Version: 2.0.0-rc.2
 *
 */

!function(t,e){"object"==typeof exports?module.exports=e(t):"function"==typeof define&&define.amd?define([],e):t.LazyLoad=e(t)}("undefined"!=typeof global?global:this.window||this.global,function(t){"use strict";"function"==typeof define&&define.amd&&(t=window);const e={src:"data-original",srcset:"data-originalset",selector:".lazy",root:null,load:null,rootMargin:"0px",threshold:0},s=function(){let t={},e=!1,o=0,n=arguments.length;"[object Boolean]"===Object.prototype.toString.call(arguments[0])&&(e=arguments[0],o++);let i=function(o){for(let n in o)Object.prototype.hasOwnProperty.call(o,n)&&(e&&"[object Object]"===Object.prototype.toString.call(o[n])?t[n]=s(!0,t[n],o[n]):t[n]=o[n])};for(;o<n;o++)i(arguments[o]);return t};function o(t,o){this.settings=s(e,o||{}),this.images=t||document.querySelectorAll(this.settings.selector),this.observer=null,this.init()}if(o.prototype={init:function(){if(!t.IntersectionObserver)return void this.loadImages();let e=this,s={root:this.settings.root,rootMargin:this.settings.rootMargin,threshold:[this.settings.threshold]};this.observer=new IntersectionObserver(function(t){Array.prototype.forEach.call(t,function(t){if(t.isIntersecting||t.intersectionRatio>.2){e.observer.unobserve(t.target);let s=t.target.getAttribute(e.settings.src),o=t.target.getAttribute(e.settings.srcset);"img"===t.target.tagName.toLowerCase()?(e.settings.load&&(t.target.onload=function(){e.settings.load.call(t.target,e.settings)}),s&&(t.target.src=s),o&&(t.target.srcset=o)):(t.target.style.backgroundImage="url("+s+")",e.settings.load&&e.settings.load.call(t.target,e.settings))}})},s),Array.prototype.forEach.call(this.images,function(t){e.observer.observe(t)})},loadAndDestroy:function(){this.settings&&(this.loadImages(),this.destroy())},loadImages:function(){if(!this.settings)return;let t=this;Array.prototype.forEach.call(this.images,function(e){let s=e.getAttribute(t.settings.src),o=e.getAttribute(t.settings.srcset);"img"===e.tagName.toLowerCase()?(t.settings.load&&(e.onload=function(){t.settings.load.call(e,t.settings)}),s&&(e.src=s),o&&(e.srcset=o)):(e.style.backgroundImage="url('"+s+"')",t.settings.load&&t.settings.load.call(e,t.settings))})},destroy:function(){this.settings&&(this.observer.disconnect(),this.settings=null)}},t.lazyload=function(t,e){return new o(t,e)},t.jQuery){const e=t.jQuery;e.fn.lazyload=function(t){return(t=t||{}).attribute=t.attribute||"data-original",new o(e.makeArray(this),t),this}}return o});