/********************************************
 * REVOLUTION 6.2.0 EXTENSION - PANZOOM
 * @version: 6.2.0 (18.02.2020)
 * @requires rs6.main.js
 * @author ThemePunch
*********************************************/
(function($) {
	"use strict";
	var
		_R = jQuery.fn.revolution;

///////////////////////////////////////////
// 	EXTENDED FUNCTIONS AVAILABLE GLOBAL  //
///////////////////////////////////////////
jQuery.extend(true,_R, {

	preparePanZoom : function(l) {

	},

	stopPanZoom : function(l) {
		if (l.data('pztl')!=undefined) l.data('pztl').pause();
	},

	startPanZoom :  function(l,id,prgs,cid,prepare) {
		if (_R[id].sliderType==="carousel") {
			if (_R[id].carousel.justify && _R[id].carousel.slide_widths===undefined) _R.setCarouselDefaults(id,true);
			if (!_R[id].carousel.justify) {
				if (_R[id].carousel.slide_width===undefined) _R[id].carousel.slide_width = _R[id].carousel.stretch!==true ? _R[id].gridwidth[_R[id].level]*(_R[id].bw===0 ? 1 : _R[id].bw) : _R[id].conw;
				if (_R[id].carousel.slide_height===undefined) _R[id].carousel.slide_height = _R[id].carousel.stretch!==true ? _R[id].gridheight[_R[id].level]*(_R[id].bw===0 ? 1 : _R[id].bw) : _R[id].conh;
			}
			if (_R[id].CWMDONE!==true) {
				_R.contWidthManager(id);
				_R.setSize(id);
			}
		}

		var d = l.data(),
			i = l.find('rs-sbg'),
			s = i.data('lazyload') || i.data('src'),
			i_a = d.owidth / d.oheight,
			cw = _R[id].sliderType==="carousel" ?  _R[id].carousel.justify ? _R[id].carousel.slide_widths[(cid!==undefined ?  cid : _R[id].carousel.focused===undefined ? 0 : _R[id].carousel.focused)] : _R[id].carousel.slide_width : _R[id].canvas.width(),
			ch = _R[id].canvas.height(),
			c_a = cw / ch;

		_R[id].panzoomTLs = _R[id].panzoomTLs===undefined ? {} : _R[id].panzoomTLs;
		_R[id].panzoomBGs = _R[id].panzoomBGs===undefined ? {} : _R[id].panzoomBGs;
		if (_R[id].panzoomBGs[cid]===undefined) _R[id].panzoomBGs[cid] = l;

		if (d.panzoom===undefined || d.panzoom===null) return;
		if (l.data('pztl')) l.data('pztl').kill();

		prgs = prgs || 0;

		// NO KEN BURN IMAGE EXIST YET
		if (l.find('.rs-pzimg').length==0) {
			var mediafilter = i.data('mediafilter');
			mediafilter = mediafilter === undefined ? "" : mediafilter;
			l.append('<rs-pzimg-wrap class="'+mediafilter+'" style="z-index:25;width:100%;height:100%;top:0px;left:0px;position:absolute;display:block"><img class="rs-pzimg" src="'+s+'" style="position:absolute;" width="'+d.owidth+'" height="'+d.oheight+'"></rs-pzimg-wrap>');
			l.data('pzimg',l.find('.rs-pzimg'));
		}

		var getPZSides = function(w,h,f,cw,ch,ho,vo) {
					var tw = w * f,
						th = h * f,
						hd = Math.abs(cw-tw),
						vd = Math.abs(ch-th),
						s = new Object();
					s.l = (0-ho)*hd;
					s.r = s.l + tw;
					s.t = (0-vo)*vd;
					s.b = s.t + th;
					s.h = ho;
					s.v = vo;
					return s;
				},

		getPZCorners = function(d,cw,ch,o) {

			var p = d.bgposition.split(" ") || "center center",
				ho = p[0] == "center"  ? "50%" : p[0] == "left" || p [1] == "left" ? "0%" : p[0]=="right" || p[1] =="right" ? "100%" : p[0],
				vo = p[1] == "center" ? "50%" : p[0] == "top" || p [1] == "top" ? "0%" : p[0]=="bottom" || p[1] =="bottom" ? "100%" : p[1];

			ho = parseInt(ho,0)/100 || 0;
			vo = parseInt(vo,0)/100 || 0;

			var sides = new Object();

			sides.start = getPZSides(o.start.width,o.start.height,o.start.scale,cw,ch,ho,vo);
			sides.end = getPZSides(o.start.width,o.start.height,o.end.scale,cw,ch,ho,vo);


			return sides;
		},
		getPZValues = function(d) {
			var attrs = d.panzoom.split(";"),
				_ = {duration:10, ease:'none', scalestart:1, scaleend:1, rotatestart:0.01, rotateend:0, blurstart:0, blurend:0, offsetstart:"0/0", offsetend:"0/0"};
			for (var k in attrs) {
				if (!attrs.hasOwnProperty(k)) continue;
				var _bas = attrs[k].split(":"),
					key = _bas[0],
					val = _bas[1];
				switch (key) {
					case "d": _.duration = parseInt(val,0) / 1000; break;
					case "e": _.ease = val;break;
					case "ss": _.scalestart=parseInt(val,0)/100;break;
					case "se": _.scaleend=parseInt(val,0)/100;break;
					case "rs": _.rotatestart=parseInt(val,0);break;
					case "re": _.rotateend=parseInt(val,0);break;
					case "bs": _.blurstart=parseInt(val,0);break;
					case "be": _.blurend=parseInt(val,0);break;
					case "os": _.offsetstart=val;break;
					case "oe": _.offsetend=val;break;
				}
			}
			_.offsetstart = _.offsetstart.split("/") || [0,0];
			_.offsetend = _.offsetend.split("/") || [0,0];
			_.rotatestart = _.rotatestart===0 ? 0.01 : _.rotatestart;
			d.panvalues = _;

			d.bgposition = d.bgposition == "center center" ? "50% 50%" : d.bgposition;
			return _;
		},
		pzCalcL = function(cw,ch,d) {

			var _ = d.panvalues === undefined ? jQuery.extend(true,{},getPZValues(d)) : jQuery.extend(true,{},d.panvalues),
				ofs = _.offsetstart,
				ofe = _.offsetend;
			var o = {start:{
								width:cw,
								height:cw / d.owidth*d.oheight,
								rotation:_.rotatestart+"deg",
								scale:_.scalestart,
								transformOrigin:d.bgposition },
					starto:{},
					end:{	rotation:_.rotateend+"deg",
							scale:_.scaleend },
					endo:{}},
				sw = cw*_.scalestart,
				sh = sw/d.owidth * d.oheight,
				ew = cw*_.scaleend,
				eh = ew/d.owidth * d.oheight;

			if (o.start.height<ch) {
				var newf = ch / o.start.height;
				o.start.height = ch;
				o.start.width = o.start.width*newf;
			}


			// MAKE SURE THAT OFFSETS ARE NOT TOO HIGH
			var c = getPZCorners(d,cw,ch,o);

			ofs[0] = parseFloat(ofs[0]) + c.start.l;
			ofe[0] = parseFloat(ofe[0]) + c.end.l;

			ofs[1] = parseFloat(ofs[1]) + c.start.t;
			ofe[1] = parseFloat(ofe[1]) + c.end.t;

			var iws = c.start.r - c.start.l,
				ihs	= c.start.b - c.start.t,
				iwe = c.end.r - c.end.l,
				ihe	= c.end.b - c.end.t;

			ofs[0] = ofs[0]>0 ? 0 : iws + ofs[0] < cw ? cw-iws : ofs[0];
			ofe[0] = ofe[0]>0 ? 0 : iwe + ofe[0] < cw ? cw-iwe : ofe[0];

			ofs[1] = ofs[1]>0 ? 0 : ihs + ofs[1] < ch ? ch-ihs : ofs[1];
			ofe[1] = ofe[1]>0 ? 0 : ihe + ofe[1] < ch ? ch-ihe : ofe[1];

			o.starto.x = ofs[0]+"px";
			o.starto.y = ofs[1]+"px";
			o.endo.x = ofe[0]+"px";
			o.endo.y = ofe[1]+"px";
			o.end.ease = o.endo.ease = _.ease;
			o.end.force3D = o.endo.force3D = true;
			return o;
		};


		if (_R[id].panzoomTLs[cid]!==undefined) _R[id].panzoomTLs[cid].kill();


		if (l.data('pztl')!=undefined) {
			l.data('pztl').kill();
			l.removeData('pztl');
		}

		var k = l.data('pzimg'),
			kw = k.parent(),
			anim = pzCalcL(cw,ch,d);

		_R[id].panzoomTLs[cid] =  tpGS.gsap.timeline();

		_R[id].panzoomTLs[cid].pause();

		anim.start.transformOrigin = "0% 0%";
		anim.starto.transformOrigin = "0% 0%";


		d.panvalues.duration = d.panvalues.duration===NaN || d.panvalues.duration===undefined ? 10 : d.panvalues.duration;

		tpGS.gsap.set(k,{width:anim.start.width, height:anim.start.height});
		delete anim.start.width;
		delete anim.start.height;
		if (prepare==="prepare") tpGS.gsap.fromTo(k,0.5,{autoAlpha:0},{autoAlpha:1});

		_R[id].panzoomTLs[cid].add(tpGS.gsap.fromTo(k,d.panvalues.duration,anim.start,anim.end),0);
		_R[id].panzoomTLs[cid].add(tpGS.gsap.fromTo(kw,d.panvalues.duration,anim.starto,anim.endo),0);


		// ADD BLUR EFFECT ON THE ELEMENTS
		if (d.panvalues.blurstart!==undefined && d.panvalues.blurend!==undefined &&  (d.panvalues.blurstart!==0 || d.panvalues.blurend!==0)) {
			var blurElement = {a:d.panvalues.blurstart},
				blurElementEnd = {a:d.panvalues.blurend, ease:anim.endo.ease},
				blurAnimation =  tpGS.gsap.to(blurElement, d.panvalues.duration, blurElementEnd);

			blurAnimation.eventCallback("onUpdate", function(kw) {
				tpGS.gsap.set(kw,{filter:'blur('+blurElement.a+'px)',webkitFilter:'blur('+blurElement.a+'px)'});
			},[kw]);
				tpGS.gsap.set(kw,{filter:'blur('+blurElement.a+'px)',webkitFilter:'blur('+blurElement.a+'px)'});
			_R[id].panzoomTLs[cid].add(blurAnimation,0);
		}

		_R[id].panzoomTLs[cid].progress(prgs);
		if (prepare==='play' || prepare==="first") _R[id].panzoomTLs[cid].play();

		//l.data('pztl',pztl);
	}
});

})(jQuery);