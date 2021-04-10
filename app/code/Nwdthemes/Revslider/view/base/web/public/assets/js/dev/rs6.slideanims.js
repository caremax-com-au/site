/********************************************
 * REVOLUTION 6.2.11 EXTENSION - SLIDE ANIMATIONS
 * @version: 6.2.11 (18.02.2020)
 * @requires rs6.main.js
 * @author ThemePunch
*********************************************/
(function($) {
"use strict";
var _R = jQuery.fn.revolution;
	///////////////////////////////////////////
	// 	EXTENDED FUNCTIONS AVAILABLE GLOBAL  //
	///////////////////////////////////////////
	jQuery.extend(true,_R, {
		animateSlide : function(obj) {
			return animateSlideIntern(obj);
		}
	});

var getSliderTransitionParameters = function(id,reqTrans,SDIR) {



	//MAX 60
	var
		p1i = "power1.in",
		p1o = "power1.out",
		p1io = "power1.inOut",
		p2i = "power2.in",
		p2o = "power2.out",
		p2io = "power2.inOut",
		p3o = "power3.out",
		p3io = "power3.inOut",
		flatT = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45],
		premT = [17,18,19,20,21,22,23,24,25,27],
		nTR =0,
		trC = 1,
		TRindex = 0,
		tA = [ 	 ['boxslide' , 0, 0, 10, 'box',false,null,0,p1o,p1o,1000,6],
				 ['boxrandomrotate' , 0, 1, 10, 'box',false,null,60,p1o,p1o,1000,6],
				 ['boxfade', 1, 0, 10, 'box',false,null,1,p1io,p1io,1000,5],
				 ['slotslide-horizontal', 2, 0, 0, 'horizontal',true,false,2,p2io,p2io,1000,3],
				 ['slotslide-vertical', 3, 0,0,'vertical',true,false,3,p2io,p2io,1000,3],
				 ['curtain-1', 4, 3,0,'horizontal',true,true,4,p1o,p1o,900,5],
				 ['curtain-2', 5, 3,0,'horizontal',true,true,5,p1o,p1o,900,5],
				 ['curtain-3', 6, 3,25,'horizontal',true,true,6,p1o,p1o,900,5],
				 ['slotzoom-horizontal', 7, 0,0,'horizontal',true,true,7,p1o,p1o,1000,7],
				 ['slotzoom-vertical', 8, 0,0,'vertical',true,true,8,p2o,p2o,1000,8],
				 ['slotzoom-mixed', 8, 1,0,'vertical',true,true,59,p2o,p2o,1000,8],
				 ['slotfade-horizontal', 9, 0,0,'horizontal',true,null,9,p2io,p2io,1500,10],
				 ['slotfade-vertical', 10, 0,0 ,'vertical',true,null,10,p2io,p2io,1500,10],
				 ['crossfade-horizontal', 9, 0,0,'horizontal',true,null,9,p2io,p2io,1500,10],
				 ['crossfade-vertical', 10, 0,0 ,'vertical',true,null,10,p2io,p2io,1500,10],
				 ['fade', 11, 0, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['crossfade', 11, 1, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['fadethroughdark', 11, 2, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['fadethroughlight', 11, 3, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['fadethroughtransparent', 11, 4, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['slideleft', 12, 0,1,'horizontal',true,true,12,p3io,p3io,1000,1],
				 ['slideup', 13, 0,1,'horizontal',true,true,13,p3io,p3io,1000,1],
				 ['slidedown', 14, 0,1,'horizontal',true,true,14,p3io,p3io,1000,1],
				 ['slideright', 15, 0,1,'horizontal',true,true,15,p3io,p3io,1000,1],
				 ['slideoverleft', 12, 7,1,'horizontal',true,true,12,p3io,p3io,1000,1],
				 ['slideoverup', 13, 7,1,'horizontal',true,true,13,p3io,p3io,1000,1],
				 ['slideoverdown', 14, 7,1,'horizontal',true,true,14,p3io,p3io,1000,1],
				 ['slideoverright', 15, 7,1,'horizontal',true,true,15,p3io,p3io,1000,1],
				 ['slideremoveleft', 12, 8,1,'horizontal',true,true,12,p3io,p3io,1000,1],
				 ['slideremoveup', 13, 8,1,'horizontal',true,true,13,p3io,p3io,1000,1],
				 ['slideremovedown', 14, 8,1,'horizontal',true,true,14,p3io,p3io,1000,1],
				 ['slideremoveright', 15, 8,1,'horizontal',true,true,15,p3io,p3io,1000,1],
				 ['papercut', 16, 0,0,'vertical',null,true,16,p3io,p3io,1000,2],
				 ['3dcurtain-horizontal', 17, 0,20,'vertical',true,true,17,p1io,p1io,2000,7],
				 ['3dcurtain-vertical', 18, 0,10,'horizontal',true,true,18,p1io,p1io,2000,7],
				 ['cubic', 19, 0,20,'horizontal',false,true,19,p2io,p2io,1000,1],
				 ['cube',19,0,20,'horizontal',false,true,20,p2io,p2io,1000,1],
				 ['flyin', 20, 0,4,'vertical',false,true,21,p3o,p3io,1000,1],
				 ['turnoff', 21, 0,1,'horizontal',false,true,22,p3io,p3io,1000,1],
				 ['incube', 22, 0,20,'horizontal',false,true,23,p2io,p2io,1000,1],
				 ['cubic-horizontal', 23, 0,20,'vertical',false,true,24,p2io,p2io,1000,1],
				 ['cube-horizontal', 23, 0,20,'vertical',false,true,25,p2io,p2io,1000,1],
				 ['incube-horizontal', 24, 0,20,'vertical',false,true,26,p2io,p2io,1000,1],
				 ['turnoff-vertical', 25, 0,1,'horizontal',false,true,27,p2io,p2io,1000,1],
				 ['fadefromright', 12, 1,1,'horizontal',true,true,28,p2io,p2io,1000,1],
				 ['fadefromleft', 15, 1,1,'horizontal',true,true,29,p2io,p2io,1000,1],
				 ['fadefromtop', 14, 1,1,'horizontal',true,true,30,p2io,p2io,1000,1],
				 ['fadefrombottom', 13, 1,1,'horizontal',true,true,31,p2io,p2io,1000,1],
				 ['fadetoleftfadefromright', 12, 2,1,'horizontal',true,true,32,p2io,p2io,1000,1],
				 ['fadetorightfadefromleft', 15, 2,1,'horizontal',true,true,33,p2io,p2io,1000,1],
				 ['fadetobottomfadefromtop', 14, 2,1,'horizontal',true,true,34,p2io,p2io,1000,1],
				 ['fadetotopfadefrombottom', 13, 2,1,'horizontal',true,true,35,p2io,p2io,1000,1],
				 ['parallaxtoright', 15, 3,1,'horizontal',true,true,36,p2io,p2io,1500,1],
				 ['parallaxtoleft', 12, 3,1,'horizontal',true,true,37,p2io,p2io,1500,1],
				 ['parallaxtotop', 14, 3,1,'horizontal',true,true,38,p2io,p2io,1500,1],
				 ['parallaxtobottom', 13, 3,1,'horizontal',true,true,39,p2io,p2io,1500,1],
				 ['scaledownfromright', 12, 4,1,'horizontal',true,true,40,p2io,p2i,1000,1],
				 ['scaledownfromleft', 15, 4,1,'horizontal',true,true,41,p2io,p2i,1000,1],
				 ['scaledownfromtop', 14, 4,1,'horizontal',true,true,42,p2io,p2i,1000,1],
				 ['scaledownfrombottom', 13, 4,1,'horizontal',true,true,43,p2io,p2i,1000,1],
				 ['zoomout', 13, 5,1,'horizontal',true,true,44,p2io,p2io,1000,1],
				 ['zoomin', 13, 6,1,'horizontal',true,true,45,p2io,p2io,1000,1],
				 ['slidingoverlayup', 27, 0,1,'horizontal',true,true,47,p1io,p1o,2000,1],
				 ['slidingoverlaydown', 28, 0,1,'horizontal',true,true,48,p1io,p1o,2000,1],
				 ['slidingoverlayright', 30, 0,1,'horizontal',true,true,49,p1io,p1o,2000,1],
				 ['slidingoverlayleft', 29, 0,1,'horizontal',true,true,50,p1io,p1o,2000,1],
				 ['parallaxcirclesup', 31, 0,1,'horizontal',true,true,51,p2io,p1i,1500,1],
				 ['parallaxcirclesdown', 32, 0,1,'horizontal',true,true,52,p2io,p1i,1500,1],
				 ['parallaxcirclesright', 33, 0,1,'horizontal',true,true,53,p2io,p1i,1500,1],
				 ['parallaxcirclesleft', 34, 0,1,'horizontal',true,true,54,p2io,p1i,1500,1],
				 ['notransition',26,0,1,'horizontal',true,null,46,p2io,p2i,1000,1],
				 ['parallaxright', 15, 3,1,'horizontal',true,true,55,p2io,p2i,1500,1],
				 ['parallaxleft', 12, 3,1,'horizontal',true,true,56,p2io,p2i,1500,1],
				 ['parallaxup', 14, 3,1,'horizontal',true,true,57,p2io,p1i,1500,1],
				 ['parallaxdown', 13, 3,1,'horizontal',true,true,58,p2io,p1i,1500,1],
				 ['grayscale', 11, 5, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['grayscalecross', 11, 6, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['brightness', 11, 7, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['brightnesscross', 11, 8, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['blurlight', 11, 9, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['blurlightcross', 11, 10, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['blurstrong', 11, 9, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1],
				 ['blurstrongcross', 11, 10, 1 ,'horizontal',true,null,11,p2io,p2io,1000,1]
			   ];

	_R[id].duringslidechange = true;

	// CHECK AUTO DIRECTION FOR TRANSITION ARTS
	jQuery.each(["parallaxcircles","slidingoverlay","slide","slideover","slideremove","parallax","parralaxto"],function(i,b) {

		if (reqTrans==b+"horizontal")  reqTrans = SDIR!=1 ? b+"left" : b+"right";
		if (reqTrans==b+"vertical") reqTrans = SDIR!=1 ? b+"up" : b+"down";
	});

	// RANDOM TRANSITIONS
	if (reqTrans == "random") reqTrans = Math.min(Math.round(Math.random()*(tA.length-1)),(tA.length-1));
	else
	if (reqTrans == "random-static") reqTrans = flatT[Math.min(Math.round(Math.random()*flatT.length-1),flatT.length-1)];
	else
	if (reqTrans == "random-premium") reqTrans = premT[Math.min(Math.round(Math.random()*premT.length-1),premT.length-1)];


	//joomla only change: avoid problematic transitions that don't compatible with mootools
	if(_R[id].isJoomla == true && window.MooTools != undefined && [12,13,14,15,16,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45].indexOf(reqTrans) != -1)
		reqTrans = premT[Math.max(0,Math.min(premT.length-1,(Math.round(Math.random() * (premT.length-2) ) + 1)))];

	// FIND THE RIGHT TRANSITION PARAMETERS
	jQuery.each(tA,function(inde,trans) {
		if (trans[0] == reqTrans || trans[7] == reqTrans) {
			nTR = trans[1];
			trC = trans[2];
			TRindex = inde; //indexcounter;
		}
	});



	nTR = Math.max(0,Math.min(30,nTR));

	return {nTR:nTR, TR: tA[TRindex], trC:trC};
};



/*************************************
	-	ANIMATE THE SLIDE  	-
*************************************/


var gSlideTransA = function(a,i) {
	var ret;
	if (jQuery.isArray(a)) {
		if (a.length>=i)
			ret = a[i];
		else
			ret = a[a.length-1];
	} else
		ret = a;


	if (ret!==undefined && jQuery.isNumeric(ret))
		return parseInt(a,0);
	else
		return ret;
};


var animateSlideIntern = function(obj) {

	// GET THE TRANSITION
	var id = obj.id,
		/*ai = _R[id].pr_active_slide.index(),
		ni = _R[id].pr_next_slide.index(),*/
		SDIR = _R[id].sc_indicator=="arrow" ? _R[id].sc_indicator_dir===undefined ? _R[id].sdir : _R[id].sc_indicator_dir : _R[id].sdir,
		_ = obj.recall===true ? jQuery.extend(true,{},_R[id].lastSliderTransition) : getSliderTransitionParameters(id,obj.animation.transition[obj.ntrid],SDIR),
		nTR = _R[id].pr_next_bg && _R[id].pr_next_bg.data('panzoom')!==undefined && (_.nTR<11 || _.nTR==17 || _.nTR===18 || (_.nTR>=27 && _.nTR<=30)) ? 11 : _.nTR,
		bgVideoPausesDuringSlideAnim = false,
		NBGE = _R[id].pr_next_bg!==undefined && _R[id].pr_next_bg.length>0 && _R[id].pr_next_bg[0]!==undefined,
		ABGE = _R[id].pr_active_bg!==undefined && _R[id].pr_active_bg.length>0 && _R[id].pr_active_bg[0]!==undefined;


	// PREPARE VIDEO ANIMATIONS
	if (obj.recall!==true) {
		_R[id].lastSliderAnimation  = jQuery.extend(true,{},obj.animation);
		_R[id].lastSliderTransition = jQuery.extend(true,{},_);
	} else {
		obj.animation = jQuery.extend(true,{},_R[id].lastSliderAnimation);
	}

	// DEFINE THE MASTERSPEED FOR THE SLIDE //
	var ctid = obj.recall===true ? _.ntrid : obj.ntrid || 0,
		MS=gSlideTransA(obj.animation.masterspeed,ctid);


	// ADJUST MASTERSPEED
	MS = MS==="default" || MS==="d" ? _.TR[10] : MS==="random" ? Math.round(Math.random()*1000+300) : MS!=undefined ? parseInt(MS,0) : _.TR[10];
	MS = MS > _R[id].duration ? _R[id].duration : MS;

	//	ADJUST SETTINGS
	_R[id].rotate = gSlideTransA(obj.animation.rotate,ctid);
	_R[id].rotate = _R[id].rotate==undefined || _R[id].rotate=="default" || _R[id].rotate=="d" ? 0 : _R[id].rotate==999 || _R[id].rotate=="random" ? Math.round(Math.random()*360) : _R[id].rotate;
	_R[id].rotate = (window._rs_ie || window._rs_ie9) ? 0 : _R[id].rotate;


	// PREPEARE ONE SLIDE IF SLOTS ARE IN GAME
	if (nTR<11 || nTR===16 || nTR===17 || nTR===18 || (_.nTR>=27 && _.nTR<=30)) {
		_R[id].slots = gSlideTransA(obj.animation.slotamount,ctid);

		_R[id].slots = _R[id].slots==undefined || _R[id].slots=="default" || _R[id].slots=="d" ? _.TR[11] : _R[id].slots=="random" ? Math.round(Math.random()*12+4) : _R[id].slots;
		_R[id].slots = _R[id].slots < 1 ? _.TR[0]=="boxslide" ? Math.round(Math.random()*6+3) : _.TR[0]=="boxslide" || _.TR[0]=="flyin" ? Math.round(Math.random()*4+1) : _R[id].slots : _R[id].slots;
		_R[id].slots = (nTR==4 || nTR==5 || nTR==6) && _R[id].slots<3 ? 3 : _R[id].slots;
		_R[id].slots = _.TR[3] != 0 ? Math.min(_R[id].slots,_.TR[3]) : _R[id].slots;
		_R[id].slots = nTR==9 ? _R[id].width/_R[id].slots : nTR==10 ? _R[id].height/_R[id].slots : _R[id].slots;
		_R[id].slots = jQuery.inArray(nTR,[19,20,21,22,23,24,25,27])>=0 ? 1 : _R[id].slots;
		_R[id].slots = (nTR==3 || nTR==8 || nTR==10) && _.TR[4]==="vertical" ? _R[id].slots+2 : _R[id].slots;
		if (_.TR[6] !=null) prepareOneSlide(_R[id].pr_active_bg,id,_.TR[6],_.TR[4]);
		if (_.TR[5] !=null) prepareOneSlide(_R[id].pr_next_bg,id,_.TR[5],_.TR[4]);
		//_R[id].mtl.delay(0.075);
	}


	// VIDEO NEED TO BE STOPPED DURING SLIDE ANIMATION AT SOME SLIDE ANIMATIONS
	if (jQuery.inArray(nTR,[0,1,2,3,4,5,6,7,8,9,10,17,18,27,28,29,30])>=0) {
		var vid = _R[id].pr_next_bg.find('rs-bgvideo');
		if (vid.length>0) {
			bgVideoPausesDuringSlideAnim = true;
			_R[id].mtl.add(tpGS.gsap.set(vid,{zIndex:0,display:"none"}),0);
			setTimeout(function() {if (_R[id].videoIsPlaying!==undefined && _R[id].videoIsPlaying.length>0 && _R[id].videoIsPlaying[0].id==vid[0].id) _R.stopVideo(vid,id);},MS>50 ? 50 : MS-20);
			setTimeout(function() {if (_R[id].videoIsPlaying!==undefined && _R[id].videoIsPlaying.length>0 && _R[id].videoIsPlaying[0].id==vid[0].id) _R.stopVideo(vid,id);},MS>100 ? 100 : MS-10);
		}
	}

	var OA = nTR===7 || nTR===16 || nTR===8  ? 0 : 1,
		OB = nTR<11 || nTR===17 || nTR===18 ? 0 : 1;


	if (NBGE) {
		var prnbg = _R[id].pr_next_bg.find('rs-sbg');
		if (prnbg[0]!==undefined) _R[id].mtl.add(tpGS.gsap.set(prnbg,{scale:1,rotationX:0, rotationY:0, rotationZ:0, z:0,/*top:0, left:0,*/ x:0, y:0,clearProps:"filter, transform",opacity:OB}),0);
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_bg,{transformOrigin:"50% 50% 0", transformPerspective:600, scale:1,rotationX:0, rotationY:0, rotationZ:0, z:0,autoAlpha:1,/*top:0, left:0,*/ x:0, y:0, clearProps:"filter, transform"}),0);
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_bg.parent(),{backgroundColor:"transparent"}),0);
	}
	if (ABGE) {
		var prabg = _R[id].pr_active_bg.find('rs-sbg');
		if (prabg[0]!==undefined) _R[id].mtl.add(tpGS.gsap.set(prabg,{ scale:1,rotationX:0, rotationY:0, rotationZ:0, z:0,/*top:0, left:0,*/ x:0, y:0,clearProps:"filter, transform", opacity:OA}),0);
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_bg,{transformOrigin:"50% 50% 0", transformPerspective:600, scale:1,rotationX:0, rotationY:0, rotationZ:0, z:0,autoAlpha:1,/*top:0, left:0,*/ x:0, y:0,clearProps:"filter, transform"}),0);
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_bg.parent(),{backgroundColor:"transparent"}),0);
	}



	// GET IN/OUT EASINGS
	var ei= gSlideTransA(obj.animation.easein,ctid),
		eo =gSlideTransA(obj.animation.easeout,ctid);

	ei = ei==="default" || ei==="d" ? _.TR[8] || "power2.inOut" : ei || _.TR[8] || "power2.inOut";
	eo = eo==="default" || eo==="d"  ? _.TR[9] || "power2.inOut" : eo || _.TR[9] || "power2.inOut";




	////////////////////////
	// CANVAS ANIMATIONS //
	///////////////////////
	if (nTR>=31) {
		// COLLECT CANVAS
		var _c = { canvas : _R[id].pr_next_bg[0].getElementsByClassName('bgcanvas')[0]};
		_c.ctx = _c.canvas.getContext("2d");
		_c.oBG_next = _R[id].pr_next_bg[0].getElementsByTagName('rs-sbg')[0];
		_c.img_next = _R.getLoadObj(id,_R.gA(_c.oBG_next,'src-rs-ref'));
		_c.col_next = _R.gA(_c.oBG_next,'bgcolor');
		if (ABGE) {
			_c.oBG_act = _R[id].pr_active_bg[0].getElementsByTagName('rs-sbg')[0];
			_c.img_act = _R.getLoadObj(id,_R.gA(_c.oBG_act,'src-rs-ref'));
			_c.col_act = _R.gA(_c.oBG_act,'bgcolor');

		}

		_c.ctx.clearRect(0,0,_c.canvas.width, _c.canvas.height);
		_c.canvas.width = _R[id].width;
		_c.canvas.height = _R[id].height;
		_c.canvas.style.background = "transparent";
		_c.canvas.style.display ="block";
		_c.oBG_next.style.display = "none";


		if (_c.col_next!==undefined && _c.col_next.indexOf('gradient')>=0) _c.col_next = "transparent";
		if (_c.col_act!==undefined && _c.col_act.indexOf('gradient')>=0) _c.col_act = "transparent";



		if (nTR===31) {
			var _n = _c.col_next!=="transparent" && _c.col_next!==undefined ? {x:0,y:0,width:_R[id].width, height:_R[id].height} : fit(_c.oBG_next.style.backgroundSize==="contain",_R[id].width, _R[id].height,_c.img_next.width,_c.img_next.height),
				_a = _c.oBG_act===undefined ? undefined : _c.col_act!=="transparent" && _c.col_act!==undefined ? {x:0,y:0,width:_R[id].width, height:_R[id].height} : fit(_c.oBG_act.style.backgroundSize==="contain",_R[id].width, _R[id].height,_c.img_act.width,_c.img_act.height),
				_w = _R[id].width/_R[id].slots,
				_wh = _w/2,
				_h = _R[id].height,
				anim = [];
			for (var i=0;i<(_R[id].slots+1);i++) anim.push({mt : _h, it : _h/4, ml : 0, o:2});


			_R[id].mtl.add(tpGS.gsap.staggerTo(anim,2*MS/1000,{mt:0, it:0, ml:_wh, o:0, ease:"power3.out",onUpdate:function() {
				var oX = 0;
				_c.ctx.clearRect(0,0,_c.canvas.width, _c.canvas.height);
				for (var i=0;i<(_R[id].slots+1);i++) {
					var yc = anim[i].mt, xc = anim[i].ml, it = anim[i].it,
						yco = anim[i].mt - _h, xco = anim[i].ml + _wh, ito = anim[i].it-_h/4;
					clippingPath({ctx:_c.ctx,poly:[[oX-_wh+xc,0+yc],[oX+_wh+1+xc,0+yc],[oX+1+xc,_h+yc],[oX-_w+xc,_h+yc]],bg:{overlay:'rgba(0,0,0,'+(anim[i].o)+')',img:_c.img_next.img,col:_c.col_next,offsetx:_n.x,offsety:_n.y+it,width:_n.width,height:_n.height}});
					if (_c.col_act!==undefined) {
						_c.oBG_act.style.display = "none";
						clippingPath({ctx:_c.ctx,poly:[[oX-_wh+xco,0+yco],[oX+_wh+1+xco,0+yco],[oX+1+xco,_h+yco],[oX-_w+xco,_h+yco]],bg:{overlay:'rgba(0,0,0,'+(1-anim[i].o)+')',img:_c.img_act.img,col:_c.col_act,offsetx:_a.x,offsety:_a.y+ito,width:_a.width,height:_a.height}});
					}
					oX = oX + _w;
				}
			}},0.2,function() {
				_c.ctx.clearRect(0,0,_c.canvas.width, _c.canvas.height);
				_c.canvas.style.display ="none";
				_c.oBG_next.style.display = "block";
			}),0);
		}
	}

	///////////////////
	// SLOT TRANSITION
	///////////////////
	if (nTR==0) {
		var maxz = Math.ceil(_R[id].height/_R[id].sloth), curz = 0;
		_R[id].pr_next_bg.find('.slotslide').each(function(j) {
			curz++;
			curz= curz===maxz ? 0 : curz;
			_R[id].rotate = _.trC===1 ? 45 : _R[id].rotate;
			_R[id].mtl.add(tpGS.gsap.from(this,(MS)/2000,{opacity:0,transformStyle:"flat", transformPerspective:600, scale:0,rotationZ:_R[id].rotate!==0 ? Math.random()*_R[id].rotate-(_R[id].rotate/2) : 0,force3D:"auto",ease:ei}),((j*10) + ((curz)*30))/3000);

		});
	} else

	//////////////////
	// SLOT TRANSITION
	//////////////////
	if (nTR==1) {
		// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
		_R[id].pr_next_bg.find('.slotslide').each(function(j) {
			_R[id].mtl.add(tpGS.gsap.from(this,(Math.random()*MS+300)/1000,{autoAlpha:0, force3D:"auto",rotation:_R[id].rotate,ease:ei}),(Math.random()*500+200)/1000);
		});
	} else


	///////////////////
	// SLOT TRANSITION
	///////////////////
	if (nTR==2 || nTR==3) {
		// ALL OLD SLOTS SHOULD BE SLIDED TO THE RIGHT
		_R[id].pr_active_bg.find('.slotslide').each(function() {
			_R[id].mtl.add(tpGS.gsap.to(this,MS/1000,{y:nTR===3 ? _R[id].sloth : 0,x:nTR===2 ? _R[id].slotw : 0,ease:ei, force3D:"auto",rotation:(0-_R[id].rotate)}),0);
		});
		// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
		_R[id].pr_next_bg.find('.slotslide').each(function() {
			_R[id].mtl.add(tpGS.gsap.from(this,MS/1000,{y:nTR==3 ? SDIR===1 ? 0-_R[id].sloth : _R[id].sloth : 0 , x:nTR==2 ? SDIR===1 ? 0-_R[id].slotw : _R[id].slotw : 0, ease:ei, force3D:"auto",rotation:_R[id].rotate}),0);
		});
	} else


	/////////////////////////////////////
	// SLOT TRANSITION  //
	////////////////////////////////////
	if (nTR==4 || nTR==5 || nTR==6) {

		var	subtl = tpGS.gsap.timeline(),
			cspeed = MS/1000 - MS/1000/_R[id].slots;
		_R[id].slots -= _R[id].slots%2==1 ? 1 : 0;
		_R[id].pr_active_bg.find('.slotslide').each(function(j) {
			var i = nTR!==6 ? j : j>_R[id].slots/2 ? _R[id].slots-j : j,
				del = ((nTR!==5 ? i : _R[id].slots-i) *(cspeed / _R[id].slots)) / (nTR===6 ? 1.3 : 1);
			subtl.add(tpGS.gsap.to(this,cspeed,{transformPerspective:600,force3D:"auto",y:SDIR!==1 ? _R[id].height : -_R[id].height,opacity:0.75,rotation:_R[id].rotate,ease:ei,
				delay: del}),0);
			_R[id].mtl.add(subtl,0);
		});

		_R[id].pr_next_bg.find('.slotslide').each(function(j) {
			var i = nTR!==6 ? j : j>_R[id].slots/2 ? _R[id].slots-j : j,
				del = ((nTR!==5 ? i : _R[id].slots-i) *(cspeed / _R[id].slots)) / (nTR===6 ? 1.3 : 1);
			subtl.add(tpGS.gsap.from(this,cspeed,{y:SDIR==1 ? _R[id].height : -_R[id].height,opacity:0.75,rotation:_R[id].rotate,force3D:"auto",ease:eo,
				delay: del}),0);
			_R[id].mtl.add(subtl,0);
		});


	} else

	////////////////////////////////////
	// THE SLOTSZOOM - TRANSITION II. //
	////////////////////////////////////
	if (nTR==7 || nTR==8) {
		MS = Math.min(_R[id].duration || MS, MS);
		// ALL OLD SLOTS SHOULD BE SLIDED TO THE RIGHT
		_R[id].pr_active_bg.find('.slotslide').each(function(j) {
			var i = j>_R[id].slots/2 ? _R[id].slots-j : j;
			_R[id].mtl.add(tpGS.gsap.to(this.getElementsByTagName("div"),MS/1000,{x:nTR===8 && _.trC===0 ? 0 : i*_R[id].slotw/3, y:nTR===8 && _.trC===0 ? i*_R[id].sloth/3 : 0, ease:ei,transformPerspective:600,force3D:"auto",filter:"blur(2px)", scale:1.2,opacity:0}),0);
		});
		_R[id].pr_next_bg.find('.slotslide').each(function(j) {
			var i = j>_R[id].slots/2 ? _R[id].slots-j : j;
			_R[id].mtl.add(tpGS.gsap.fromTo(this.getElementsByTagName("div"),MS/1000,{x:nTR===8 && _.trC===0 ? 0 : 0-i*_R[id].slotw/3, y:nTR===8 && _.trC===0 ? 0-i*_R[id].sloth/3 : 0, filter:"blur(2px)", opacity:0, transformPerspective:600, scale:1.2},{x:0,y:0,ease:eo,force3D:"auto",scale:1,filter:"blur(0px)", opacity:1,rotation:0}),0);
		});
	} else

	////////////////////////////////////////
	// THE SLOTSFADE - TRANSITION III.   //
	//////////////////////////////////////
	if (nTR==9 || nTR==10) {
		// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
		var ss = _R[id].pr_next_bg[0].getElementsByClassName("slotslide"),
			sk = MS - MS/1.8;
		for (var i=0;i<ss.length;i++) _R[id].mtl.add(tpGS.gsap.fromTo(ss[i],(MS - (i * (sk / _R[id].slots)))/1000,{zIndex:10,opacity:0},{opacity:1,ease:"none",delay:(i * (sk / _R[id].slots))/1000}),0);
	} else



	///////////////////////
	// CROSS ANIMATIONS //
	//////////////////////
	if (nTR==11) {
		_.trC = Math.min(12,_.trC);

		var bgcol = _.trC == 2 ? "#000000" : _.trC == 3 ? "#ffffff" : "transparent";
		MS = MS===0 ? 10 : MS;

		switch (_.trC) {
			case 0:  if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/1000,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),0); break;
			case 1: // CROSSFADE
				if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/1000,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),0);
				_R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/1000,{autoAlpha:1},{autoAlpha:0,force3D:"auto",ease:ei}),0);
			break;
			case 2: case 3: case 4:
				_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_bg.parent(),{backgroundColor:bgcol,force3D:"auto"}),0);
				_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_bg.parent(),{backgroundColor:"transparent",force3D:"auto"}),0);
				if (ABGE) _R[id].mtl.add(tpGS.gsap.to(_R[id].pr_active_bg,MS/2000,{autoAlpha:0,force3D:"auto",ease:ei}),0);
				if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/2000,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),MS/2000);
			break;
			case 5: // GRAYSCALE
			case 6: // GRAYSCALECROSS
			case 7: // BRIGHTNESS
			case 8: // BRIGHTNESSCROSS
			case 9: // BLUR LIGHT
			case 10: // BLUR LIGHT CROSS
			case 11: // BLUR STRONG
			case 12: // BLUR STRONG CROSS
				var _blur = jQuery.inArray(_.trC,[9,10])>=0 ? 5 : jQuery.inArray(_.trC,[11,12])>=0 ? 10 : 0,
					_gray = jQuery.inArray(_.trC,[5,6,7,8])>=0 ? 100 : 0,
					_bright = jQuery.inArray(_.trC,[7,8])>=0 ? 300 : 0,
					ff = "blur("+_blur+"px) grayscale("+_gray+"%) brightness("+_bright+"%)",
					ft = "blur(0px) grayscale(0%) brightness(100%)";
				if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/1000,{autoAlpha:0,filter:ff, "-webkit-filter":ff},{autoAlpha:1,filter:ft, "-webkit-filter":ft,force3D:"auto",ease:ei}),0);
				if (jQuery.inArray(_.trC,[6,8,10])>=0) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/1000,{autoAlpha:1,filter:ft, "-webkit-filter":ft},{autoAlpha:0,force3D:"auto",ease:ei,filter:ff, "-webkit-filter":ff}),0);
			break;
		}
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_bg.find('rs-sbg'),{autoAlpha:1}),0);
		if (ABGE) _R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_bg.find('rs-sbg'),{autoAlpha:1}),0);
    } else


	///////////////////////
	// CROSS ANIMATIONS //
	//////////////////////
	if (nTR==12 || nTR==13 || nTR==14 || nTR==15) {
		var spd = _.trC == 3 ? MS / 1300 : MS/1000,
			spd2 = MS/1000,
			oow = _.trC==5 || _.trC==6 ? 0 : _R[id].width,
			ooh = _.trC==5 || _.trC==6 ? 0 : _R[id].currentSlideHeight,
			twx = nTR==12 ? oow : nTR == 15 ? 0-oow : 0,
			twy = nTR==13 ? _.trC==5 || _.trC==6 ? 0 : _R[id].height : nTR == 14 ? _.trC==5 || _.trC==6 ? 0 : 0 - _R[id].height : 0,
			op = _.trC == 1 || _.trC == 2 || _.trC == 5 || _.trC == 6 ? 0 : 1,
			scal = _.trC==4 || _.trC==5 ? 0.6 : _.trC==6 ? 1.4 : 1,
			fromscale = _.trC==5 ? 1.4 : _.trC==6 ? 0.6 : 1;

		if (_.trC==7 || _.trC==4) { oow = 0;ooh = 0;}
		if (_.trC==8) {
			_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_slide,{zIndex:20}),0);
			_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_slide,{zIndex:15}),0);
			_R[id].mtl.add(tpGS.gsap.to(_R[id].pr_next_bg,0.01,{overflow:"hidden", x:0, y:0,  scale:1, autoAlpha:1,rotation:0,overwrite:true, immediateRender:true, force3D:"auto"}),0);
		} else {
			if (_R[id].pr_active_slide!==undefined && _R[id].pr_active_slide.length>0) _R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_slide,{zIndex:15}),0);
			if (_R[id].pr_next_slide!==undefined && _R[id].pr_next_slide.length>0) _R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_slide,{zIndex:20}),0);
			if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,spd,{x:twx, y:twy, overflow:"hidden", scale:fromscale, autoAlpha:op,rotation:_R[id].rotate},{autoAlpha:1,x:0,y:0,scale:1,rotation:0,ease:ei,force3D:"auto"}),0);
		}

		if (_.trC!=1)
			switch (nTR) {
				case 12:if (ABGE) _R[id].mtl.add(tpGS.gsap.to(_R[id].pr_active_bg,spd2,{x:(0-oow)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:_R[id].rotate,ease:eo}),0);break;
				case 15:if (ABGE) _R[id].mtl.add(tpGS.gsap.to(_R[id].pr_active_bg,spd2,{x:(oow)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:_R[id].rotate,ease:eo}),0);break;
				case 13:if (ABGE) _R[id].mtl.add(tpGS.gsap.to(_R[id].pr_active_bg,spd2,{y:(0-ooh)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:_R[id].rotate,ease:eo}),0);break;
				case 14:if (ABGE) _R[id].mtl.add(tpGS.gsap.to(_R[id].pr_active_bg,spd2,{y:(ooh)+'px',overflow:"hidden",force3D:"auto",scale:scal,autoAlpha:op,rotation:_R[id].rotate,ease:eo}),0);break;
			}
	} else

	////////////////////////////////////////
	// THE SLOTSLIDE - TRANSITION XX.  //
	///////////////////////////////////////
	if (nTR==16 ) {								// PAPERCUT
		var torig = SDIR===1 ? "80% 50% 0" : "20%  50% 0";
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_slide,{zIndex:20}),0);
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_slide,{zIndex:15}),0);

		_R[id].pr_active_bg.find('.slotslide').each(function(j) {
			_R[id].mtl.add(tpGS.gsap.fromTo(this,MS/1000,
			{x:0,rotationZ:0,opacity:1,y:0, z:0, scale:1},
			{opacity:1,x:SDIR===1 ? j==0 ? -_R[id].width/1.6 : -_R[id].width/1.8 : j===0 ? _R[id].width/1.6 : _R[id].width/1.8,rotationZ:SDIR===1 ? j===0 ? -35 : 25 : j===0 ? 25 : -35, z:0, y:j==0 ? "-120%" : "140%" ,scale:0.8 ,force3D:"auto",transformPerspective:600,transformOrigin:torig,delay:0,ease:ei}
			),0);
			_R[id].mtl.add(tpGS.gsap.fromTo(this,(MS/2000),{opacity:1},{opacity:0,delay:MS/2000}),0);
		});

		if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,(MS/1000) - (MS/7000),{x:Math.random()*100-50,opacity:1,scale:0.9, rotationZ:Math.random()*10-5},{x:0,opacity:1,scale:1,rotationZ:0, ease:ei, force3D:"auto",delay:(MS/7000)}),0);

	} else

	////////////////////////////////////////
	// THE SLOTSLIDE - TRANSITION XVII.  //
	///////////////////////////////////////
	if (nTR==17 || nTR==18) {
		// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
		_R[id].pr_next_bg.find('.slotslide').each(function(j) {
			_R[id].mtl.add(tpGS.gsap.fromTo(this,((MS/_R[id].slots))/1000,
				{opacity:0,y:0, x:0 ,rotationY: nTR===17 ? 0 : 90,scale:1,rotationX:nTR===17 ? (-90) : 0 ,force3D:"auto",transformPerspective:600,transformOrigin:nTR===17 ? "top center" : "center left"},
				{opacity:1,y:0,x:0,rotationX:0,rotationY:0,force3D:"auto",ease:eo,delay:j*(((MS/_R[id].slots))/2000)}),0);

		});
		_R[id].pr_active_bg.find('.slotslide').each(function(j) {
			_R[id].mtl.add(tpGS.gsap.fromTo(this,((MS/_R[id].slots))/1000,
				{opacity:1,rotationY: 0,scale:1,rotationX:0 ,force3D:"auto",transformPerspective:600,transformOrigin:nTR===17 ? "bottom center" : "center right"},
				{opacity:0,rotationX:nTR===17 ? (110) : 0,rotationY:nTR===17 ? 0 : (110), force3D:"auto",ease:ei,delay:j*(((MS/_R[id].slots))/2000)}),0);

		});
	} else

	////////////////////////////////////////
	// THE SLOTSLIDE - TRANSITION XIX.  //
	///////////////////////////////////////
	if (nTR==19 || nTR==22 || nTR==23 || nTR==24) {	// CUBE ANIMATIONS

		//SET DEFAULT IMG UNVISIBLE
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_slide,{zIndex:20}),0);
		_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_slide,{zIndex:10}),0);

		var torig = nTR===19 ? "center center -"+_R[id].height/2 : nTR===22 ?  "center center "+_R[id].height/2 : nTR===23 ? "center center -"+(_R[id].width/2) : "center center "+_R[id].width/2,
		CBGS = [];

		// ALL NEW SLOTS SHOULD BE SLIDED FROM THE LEFT TO THE RIGHT
		tpGS.gsap.set(_R[id].c,{transformStyle:"flat",backfaceVisibility:"hidden",transformPerspective:600});

		if (NBGE) {
				CBGS.push(_R[id].pr_next_bg);
				_R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/1000,
						{	rotationX: nTR ==19 || nTR === 22 ? SDIR==1 ? -90 : 90 : 0,
							rotationY: nTR ==23 || nTR === 24 ? SDIR==1 ? -90 : 90 : 0,
							//autoAlpha:nTR===22  || nTR===24 ? 1 : 0,
							x:0, scale:1,y:0,overflow:"hidden",autoAlpha:1, transformStyle:"flat", backfaceVisibility:"hidden",  force3D:"auto",transformPerspective:1200,transformOrigin:torig
						}, {	overflow:"hidden",x:0,autoAlpha:1,rotationX:0, rotationY:0,y:0, scale:1, delay:0,ease:ei,transformStyle:"flat", backfaceVisibility:"hidden",  force3D:"auto",transformPerspective:1200,transformOrigin:torig}),0);

			 	_R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/2000,{z:nTR==19 || nTR===23 ? -200 : 0},{z:nTR===19 || nTR===23? 0 : -200,ease:"power3.inOut",delay:(nTR===19 || nTR===23 ? MS/2000 : 0)}),0);
		}
		if (nTR===22 || nTR===24) {
			if (ABGE) CBGS.push(_R[id].pr_active_bg);
			_R[id].mtl.add(tpGS.gsap.fromTo(CBGS,MS/2000,{z:-200},{z:0,ease:"power2.in",delay: MS/2000 }),0);
		}

		if (ABGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/2000,{z:0},{z:-200,ease:"power3.inOut",delay:0, force3D:"auto" }),0);
		if (ABGE && (nTR===19 || nTR===23)) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/2000,{autoAlpha:1},{autoAlpha:0,ease:"none",delay: MS/2000, force3D:"auto" }),0);

		if(ABGE)
			_R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/1000,
				{	overflow:"hidden", rotationX:0, rotationY:0,rotationZ:0,y:0, x:0, scale:1,transformStyle:"flat", backfaceVisibility:"hidden",  force3D:"auto",transformPerspective:1200,transformOrigin:torig},
				{	rotationX:nTR===19 || nTR === 22 ? SDIR==1 ? 90 : -90 : 0,
					rotationY:nTR===23 || nTR === 24 ? SDIR==1 ? 90 : -90 : 0,
					overflow:"hidden",y:0, scale:1, delay:0,force3D:"auto",ease:ei,transformStyle:"flat", backfaceVisibility:"hidden",  transformPerspective:1200,transformOrigin:torig}),0);
	} else

	////////////////////////////////////////
	// THE SLOTSLIDE - TRANSITION XX.  //
	///////////////////////////////////////
	if (nTR==20 ) {								// FLYIN
		var torig = SDIR===1 ? "20% " : "80% ";
		torig+="60% -50%";
		if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/1000,
		{x:SDIR===1 ? -_R[id].width : _R[id].width,rotationX:20,z:-_R[id].width, autoAlpha:0,y:0,scale:1,force3D:"auto",transformPerspective:600,transformOrigin:torig,rotationY:SDIR===1 ? 50 : -50},
		{x:0,rotationX:0,autoAlpha:1,y:0,z:0, scale:1,rotationY:0, delay:0,ease:ei}),0);
		torig = SDIR!=1 ? "20% " : "80% ";
		torig+="60% -50%";
		_R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/1000,
		{autoAlpha:1,rotationX:0,y:0,z:0,scale:1,x:0, force3D:"auto",transformPerspective:600,transformOrigin:torig, rotationY:0},
		{autoAlpha:1,rotationX:20,y:0, z:-_R[id].width, x:SDIR!=1 ? -_R[id].width /1.2 : _R[id].width/1.2, force3D:"auto",rotationY:SDIR===1 ? -50 : 50, delay:0,ease:"power2.inOut"}),0);
	} else

	////////////////////////////////////////
	// THE SLOTSLIDE - TRANSITION XX.  //
	///////////////////////////////////////
	if (nTR==21 || nTR==25) {
		//ei = "power3.inOut";
		var rot = nTR===25 ? _R[id].rotate : SDIR===1 ? 90 : -90,
			rot2 = nTR===25 ? SDIR===1 ? -90 : 90 :  _R[id].rotate,
			torig = SDIR===1 ? nTR===25 ? "center top 0" : "left center 0" : nTR===25 ? "center bottom 0" : "right center 0";

		if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,MS/1000,{transformStyle:"flat",rotationX:rot2,y:0, x:0, autoAlpha:0,force3D:"auto",transformPerspective:1200,transformOrigin:torig,rotationY:rot},{autoAlpha:1,rotationX:0, rotationY:0,ease:ei}),0);

		torig = SDIR===1 ? nTR===25 ? "center bottom 0" : "right center 0" : nTR===25 ? "center top 0" : "left center 0";
		rot = nTR!==25 ? -rot : rot;
		rot2 = nTR!==25 ? rot2 : -rot2;

		_R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/1000,{rotationX:0, rotationY:0,transformStyle:"flat",transformPerspective:1200,force3D:"auto",},{immediateRender:true, rotationX:rot2,transformOrigin:torig,rotationY:rot,ease:eo}),0);

	} else


	 ///////////////////////
	// NO TRANSITION //
	//////////////////////
	if (nTR==26) {
		MS=0;
		if (NBGE) {
			_R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg,0.001,{autoAlpha:0},{autoAlpha:1,force3D:"auto",ease:ei}),0);
			_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_next_bg.find('rs-sbg'),{autoAlpha:1}),0);
		}
		if (ABGE) {
			_R[id].mtl.add(tpGS.gsap.to(_R[id].pr_active_bg,0.001,{autoAlpha:0,force3D:"auto",ease:ei}),0);
			_R[id].mtl.add(tpGS.gsap.set(_R[id].pr_active_bg.find('rs-sbg'),{autoAlpha:1}),0);
		}
	} else

	//////////////////////
	// SLIDING OVERLAYS //
	//////////////////////
	if (nTR==27||nTR==28||nTR==29||nTR==30) {
		var slot = _R[id].pr_next_bg.find('.slot'),
			nd = nTR==27 || nTR==28 ? 1 : 2,
			mhp = nTR==27 || nTR==29 ? "-100%" : "100%",
			php = nTR==27 || nTR==29 ? "100%" : "-100%",
			mep = nTR==27 || nTR==29 ? "-80%" : "80%",
			pep = nTR==27 || nTR==29 ? "80%" : "-80%",
			ptp = nTR==27 || nTR==29 ? "10%" : "-10%",
			fa = {overwrite:"all"},
			ta = {autoAlpha:0,zIndex:1,force3D:"auto",ease:ei},
			fb = {position:"inherit",autoAlpha:0,overwrite:"all",zIndex:1},
			tb = {autoAlpha:1,force3D:"auto",ease:eo},
			fc = {overwrite:"all",zIndex:2,opacity:1,autoAlpha:1},
			tc = {autoAlpha:1,force3D:"auto",overwrite:"all",ease:ei},
			fd = {overwrite:"all",zIndex:2,autoAlpha:1},
			td = {autoAlpha:1,force3D:"auto",ease:ei},
			at = nd==1 ? "y" : "x";

		fa[at] = "0%";
		ta[at] = mhp;
		fb[at] = ptp;
		tb[at] = 0;
		fc[at] = php;
		tc[at] = mhp;
		fd[at] = mep;
		td[at] = pep;

		slot.append('<span style="background-color:rgba(0,0,0,0.6);width:100%;height:100%;position:absolute;top:0px;left:0px;display:block;z-index:2"></span>');
		if (ABGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_active_bg,MS/1000,fa,ta),0);
		if (NBGE) _R[id].mtl.add(tpGS.gsap.fromTo(_R[id].pr_next_bg.find('rs-sbg'),MS/2000,fb,tb),MS/2000);
		_R[id].mtl.add(tpGS.gsap.fromTo(slot,MS/1000,fc,tc),0);
		_R[id].mtl.add(tpGS.gsap.fromTo(slot.find('.slotslide div'),MS/1000,fd,td),0);
	}

	if (bgVideoPausesDuringSlideAnim && vid.length>0) _R[id].mtl.add(tpGS.gsap.set(vid,{zIndex:30,display:"block",onComplete:function() {_R.playVideo(vid,id);}}));
	//return _R[id].mtl;
};

///////////////////////
// PREPARE THE SLIDE //
//////////////////////

var fit=function(contains,parentWidth, parentHeight, childWidth, childHeight) {
	var doRatio = childWidth / childHeight,
		cRatio = parentWidth / parentHeight,
		width = parentWidth,
		height = parentHeight;

	if (contains ? (doRatio > cRatio) : (doRatio < cRatio)) {
		height = width / doRatio;
	} else {
		width = height * doRatio;
	}

	return {
		width:width,
		height:height,
		x: (parentWidth - width) / 2,
		y: (parentHeight - height) / 2
	};
};


var clippingPath = function(_) {
        // save the unclipped context
        _.ctx.save();

        // define the path that will be clipped to
        _.ctx.beginPath();
        _.ctx.moveTo(_.poly[0][0],_.poly[0][1]);
        for (var i=1;i<_.poly.length;i++) _.ctx.lineTo(_.poly[i][0],_.poly[i][1]);
        _.ctx.closePath();

        _.ctx.lineWidth=2;
        _.ctx.stroke();

        // make the current path a clipping path
        _.ctx.clip();

        if (_.bg.img!==undefined) {
	        // draw the image which will be clipped except in the clipping path
	        _.ctx.drawImage(_.bg.img,_.bg.offsetx,_.bg.offsety,_.bg.width, _.bg.height);
	    }

	    if (_.bg.col!=="transparent" && _.bg.col!==undefined) {
	    	_.ctx.fillStyle = _.bg.col;
	    	_.ctx.fillRect(_.bg.offsetx,_.bg.offsety,_.bg.width, _.bg.height);
	    }

	    if (_.bg.overlay!==undefined) {
	    	_.ctx.fillStyle = _.bg.overlay;
	    	_.ctx.fillRect(_.bg.offsetx,_.bg.offsety,_.bg.width, _.bg.height);
	    }

        // restore the unclipped context (==undo the clipping path)
        _.ctx.restore();
    }


var slotSize = function(img,id) {
	_R[id].slotw=Math.ceil(_R[id].width/_R[id].slots);

	if (_R[id].sliderLayout=="fullscreen")
		_R[id].sloth=Math.ceil(_R.getWinH(id)/_R[id].slots);
	else
		_R[id].sloth=Math.ceil(_R[id].height/_R[id].slots);

	//if (_R[id].autoHeight && img!==undefined && img!=="") _R[id].sloth=Math.ceil(img.height()/_R[id].slots);
};

var prepareOneSlide = function(wrap,id,visible,vorh) {

	if (wrap.length===0) return;

	var img = wrap.find('rs-sbg'),
		mediafilter = img.data('mediafilter'),
		scalestart = wrap.data('zoomstart'),
		rotatestart = wrap.data('rotationstart');

	if (img.data('currotate')!=undefined) rotatestart = img.data('currotate');
	if (img.data('curscale')!=undefined && vorh=="box") scalestart = img.data('curscale')*100;
	else
	if (img.data('curscale')!=undefined) scalestart = img.data('curscale');

	slotSize(img,id);

	var src = img[0]!==undefined && img[0].dataset!==undefined && img[0].dataset.lazyload!==undefined ? img[0].dataset.lazyload : img.attr('src'),
		w = _R[id].width,
		h =_R[id].autoHeight ? _R[id].c.height() :  _R[id].height,
		fulloff = img.data("fxof"),
		fullyoff=0,
		off=0,
		bgcolor=wrap.data('bgcolor') || 'transparent', //img.css("backgroundColor"),
		bgfit = wrap.data('bgfit') || "cover",
		bgrepeat = wrap.data('bgrepeat') || "no-repeat",
		bgposition = wrap.data('bgposition') || "center center",
		bgstyle = (bgcolor!==undefined && bgcolor.indexOf('gradient')>=0) ? 'background:'+bgcolor : 'background-color:'+bgcolor+';'+'background-image:url('+src+');'+'background-repeat:'+bgrepeat+';'+'background-size:'+bgfit+';background-position:'+bgposition,
		innerwrap = "";


	fulloff= fulloff==undefined ? 0 : fulloff;

	var parent = wrap[0].parentNode;
	wrap.detach();
	wrap.find('.slot').each(function() {
		jQuery(this).remove();
	});

	if (vorh==="box") { // BOX ANIMATION PREPARING
		var x = 0,
			y = 0;
		for (var j=0;j<_R[id].slots;j++) {
			y=0;
			for (var i=0;i<_R[id].slots;i++) {
				innerwrap +=('<div class="slot" style="'+(scalestart!=undefined && rotatestart!=undefined ? 'transform:rotateZ('+rotatestart+'deg)' : '')+';position:absolute;overflow:hidden;top:'+(fullyoff+y)+'px;left:'+(fulloff+x)+'px;width:'+_R[id].slotw+'px;height:'+_R[id].sloth+'px;">'+
						  	'<div class="slotslide '+mediafilter+'" data-x="'+x+'" data-y="'+y+'" style="position:absolute;top:'+(0)+'px;left:'+(0)+'px;width:'+_R[id].slotw+'px;height:'+_R[id].sloth+'px;overflow:hidden;">'+
						  	'<div style="position:absolute;top:'+(0-y)+'px;left:'+(0-x)+'px;width:'+w+'px;height:'+h+'px;'+bgstyle+';">'+
						  	'</div></div></div>');
				y=y+_R[id].sloth;
			}
			x=x+_R[id].slotw;
		}
	} else
	if (vorh==="horizontal") {// SLOT ANIMATION PREPARING
		if (!visible) var off=0-_R[id].slotw;
		for (var i=0;i<_R[id].slots;i++) {
			innerwrap +=('<div class="slot" style="'+(scalestart!=undefined && rotatestart!=undefined ? 'transform:rotateZ('+rotatestart+'deg)' : '')+';position:absolute;overflow:hidden;top:'+(0+fullyoff)+'px;left:'+(fulloff+(i*_R[id].slotw))+'px;width:'+(_R[id].slotw+0.3)+'px;height:'+h+'px">'+
						'<div class="slotslide '+mediafilter+'" style="position:absolute;top:0px;left:'+off+'px;width:'+(_R[id].slotw+0.6)+'px;height:'+h+'px;overflow:hidden;">'+
						'<div style="position:absolute;top:0px;left:'+(0-(i*_R[id].slotw))+'px;width:'+w+'px;height:'+h+'px;'+bgstyle+';">'+
						'</div></div></div>');

		}
	}
	if (vorh==="vertical") {
		if (!visible) var off=0-_R[id].sloth;
		for (var i=0;i<_R[id].slots;i++) {
			innerwrap +=('<div class="slot" style="'+(scalestart!=undefined && rotatestart!=undefined ? 'transform:rotateZ('+rotatestart+'deg)' : '')+';position:absolute;overflow:hidden;top:'+(fullyoff+(i*_R[id].sloth))+'px;left:'+(fulloff)+'px;width:'+w+'px;height:'+(_R[id].sloth)+'px">'+
						 '<div class="slotslide '+mediafilter+'" style="position:absolute;top:'+(off)+'px;left:0px;width:'+w+'px;height:'+_R[id].sloth+'px;overflow:hidden;">'+
						'<div style="position:absolute;top:'+(0-(i*_R[id].sloth))+'px;left:0px;width:'+w+'px;height:'+h+'px;'+bgstyle+';">'+
						'</div></div></div>');

		}
	}
	wrap.append(innerwrap);
	parent.appendChild(wrap[0]);

};

})(jQuery);

//DEBUG:
	/*var debug = true;
	if (debug) {
		window.debugindex = window.debugindex===undefined ? 39 : window.debugindex;
		window.debugindex = SDIR!==1 ? window.debugindex+1 : window.debugindex-1;
		window.debugindex = window.debugindex===83 ? 0 : window.debugindex;
		TRindex = window.debugindex;
		nTR = tA[TRindex][1];
		trC = tA[TRindex][2];
	} else {
	}*/