/********************************************
 * REVOLUTION 6.2 EXTENSION - VIDEO FUNCTIONS
 * @version: 6.2.11 (28.05.2020)
 * @requires rs6.main.js
 * @author ThemePunch
*********************************************/
(function($) {
	"use strict";
var
	_R = jQuery.fn.revolution,
	_ISM = _R.is_mobile(),
	_ANDROID = _R.is_android();

///////////////////////////////////////////
// 	EXTENDED FUNCTIONS AVAILABLE GLOBAL  //
///////////////////////////////////////////
jQuery.extend(true,_R, {

	preLoadAudio : function(li,id) {
		_R[id].videos = _R[id].videos===undefined ? {} : _R[id].videos;
		li.find('.rs-layer-audio').each(function() {
			
			var _nc = jQuery(this),
				_ = _R[id].videos[_nc[0].id] = _R[id].videos[_nc[0].id]===undefined ? readVideoDatas(_nc.data(),"audio",_R.gA(li[0],"key")) : _R[id].videos[_nc[0].id],
				obj = {};

			if (_nc.find('audio').length===0) {
				obj.src = _.mp4 !=undefined ? _.mp4  : '',
				obj.pre = _.pload || '';
				this.id = this.id===undefined || this.id==="" ? _nc.attr('audio-layer-'+Math.round(Math.random()*199999)) : this.id;
				obj.id = this.id;
				obj.status = "prepared";
				obj.start = jQuery.now();
				obj.waittime = _.ploadwait!==undefined ? _.ploadwait*1000 : 5000;
				if (obj.pre=="auto" || obj.pre=="canplaythrough" || obj.pre=="canplay" || obj.pre=="progress") {
					if (_R[id].audioqueue===undefined) _R[id].audioqueue = [];
					_R[id].audioqueue.push(obj);
					_R.manageVideoLayer(_nc,id,_R.gA(li[0],"key"));
				}
			}
		});
	},

	preLoadAudioDone : function(_nc,id,event) {
		var _ = _R[id].videos[_nc[0].id];
		if (_R[id].audioqueue && _R[id].audioqueue.length>0)
			jQuery.each(_R[id].audioqueue,function(i,obj) {
				if (_.mp4 === obj.src && (obj.pre === event || obj.pre==="auto")) obj.status = "loaded";
			});
	},

	resetVideo : function(_nc,id,mode,nextli) {		

		var _ = _R[id].videos[_nc[0].id];		
		switch (_.type) {
			case "youtube":	


				if (_.rwd && _.player!=undefined && _.player.seekTo!==undefined ) {
					_.player.seekTo(_.ssec==-1 ? 0 : _.ssec);
					_.player.pauseVideo();
				}
				if (_nc.find('rs-poster').length==0 && !_.bgvideo && mode!=="preset") 					
					tpGS.gsap.to(_nc.find('iframe'),0.3,{opacity:1,display:"block",ease:"power3.inOut"});					
				
			break;

			case "vimeo":
				if (_.vimeoplayer!==undefined && !nextli && _.rwd && ((_.ssec!==0  && _.ssec!==-1) || (_.bgvideo || _nc.find('rs-poster').length>0))) {
					_.vimeoplayer.setCurrentTime(_.ssec==-1 ? 0 : _.ssec);
					_.vimeoplayer.pause();
				}																		
				if (_nc.find('rs-poster').length==0 && !_.bgvideo && mode!=="preset")
					tpGS.gsap.to(_nc.find('iframe'),0.3,{opacity:1,display:"block",ease:"power3.inOut"});
			break;

			case "html5":
				if (_ISM && _.notonmobile) return false;
				tpGS.gsap.to(_.jvideo,0.3,{opacity:1,display:"block",ease:"power3.inOut"});								
				if (_.rwd && !_nc.hasClass("videoisplaying") && !isNaN(_.video.duration)) {
					_.justReseted = true;
					_.video.currentTime=_.ssec == -1 ? 0 : _.ssec;								
				}	
				if (_.volume=="mute" || _R.lastToggleState(_nc.videomutetoggledby) || _R[id].globalmute===true) _.video.muted = true;
			break;
		}
	},

	Mute : function(_nc,id,m) {			
		var muted = false,
			_ = _R[id].videos[_nc[0].id];		
		switch (_.type) {
			case "youtube":
				if (_.player) {
					if (m===true) _.player.mute();
					if (m===false) ytVolume(_,parseInt(_.volcache,0));
					muted = _.player.isMuted();
				}
			break;
			case "vimeo":
				//if (jQuery.fn.revolution.get_browser()==="Chrome") return;
				if (!_.volcachecheck) {
					_.volcache = _.volcache>1 ? _.volcache/100 : _.volcache;
					_.volcachecheck = true;
				}		
				_.volume = m===true ? "mute" : m===false ? _.volcache : _.volume;				
				if (m!==undefined && _.vimeoplayer!=undefined) vimeoVolume(_,(m===true ? 0 : _.volcache));
				muted= _.volume=="mute" || _.volume===0;

			break;
			case "html5":
				if (!_.volcachecheck) {
					_.volcache = _.volcache>1 ? _.volcache/100 : _.volcache;
					_.volcachecheck = true;
				}
				_.video.volume = _.volcache;
				if (m!==undefined && _.video) _.video.muted = m;
				muted = _.video!==undefined ? _.video.muted : muted;
			break;
		}
		if (m===undefined) return muted;
	},

	stopVideo : function(_nc,id) {
		if (_R[id]===undefined || _R[id]===undefined) return;
		var _ = _R[id].videos[_nc[0].id];
		if (_===undefined) return;		
		if (!_R[id].leaveViewPortBasedStop) _R[id].lastplayedvideos = [];
		_R[id].leaveViewPortBasedStop = false;
		switch (_.type) {
			case "youtube":
				if (_.player===undefined || _.player.getPlayerState()===2 || _.player.getPlayerState()===5) return;
				_.player.pauseVideo();
				_.youtubepausecalled = true;
				setTimeout(function() { _.youtubepausecalled=false;},80);
			break;
			case "vimeo":
				if (_.vimeoplayer===undefined) return;
				_.vimeoplayer.pause();
				_.vimeopausecalled = true;
				setTimeout(function() { _.vimeopausecalled=false;},80);
			break;
			case "html5":
				if (_.video) _.video.pause();
			break;
		}
	},

	

	playVideo : function(_nc,id) {			

		var _ = _R[id].videos[_nc[0].id];		
		clearTimeout(_.videoplaywait);
		switch (_.type) {
			case "youtube":						
				if (_nc.find('iframe').length==0) {
					_nc.append(_.videomarkup);
					addVideoListener(_nc,id,true);					
				} else
				if (_.player.playVideo!=undefined) {

						var ct = _.player.getCurrentTime();
						if (_.nseTriggered) {
							ct=-1;
							_.nseTriggered = false;
						}
						if (_.ssec!=-1 && _.ssec>ct) _.player.seekTo(_.ssec);						
						if (_.youtubepausecalled!==true) playYouTube(_);

				} else
				_.videoplaywait = setTimeout(function() { if (_.youtubepausecalled!==true) _R.playVideo(_nc,id); },50);
			break;
			case "vimeo":				
				if (_nc.find('iframe').length==0) {
					delete _.vimeoplayer;
					_nc.append(_.videomarkup);
					addVideoListener(_nc,id,true);
				} else
				if (_nc.hasClass("rs-apiready")) {
					_.vimeoplayer = _.vimeoplayer==undefined ? new Vimeo.Player(_nc.find('iframe').attr("id")) : _.vimeoplayer;
					if (!_.vimeoplayer.getPaused())
						_.videoplaywait = setTimeout(function() { if (_.vimeopausecalled!==true) _R.playVideo(_nc,id);},50);
					else
						setTimeout(function() {
							var ct = _.currenttime===undefined ? 0 : _.currenttime;
							if (_.nseTriggered) {
								ct=-1;
								_.nseTriggered = false;
							}
							if (_.ssec!=-1 && _.ssec>ct) _.vimeoplayer.setCurrentTime(_.ssec);
							if (_.volume=="mute" || _.volume===0 || _R.lastToggleState(_nc.data('videomutetoggledby')) || _R[id].globalmute===true) {_.volumetoken=true;_.vimeoplayer.setVolume(0);}
							playVimeo(_.vimeoplayer);
						},510);
				} else
				_.videoplaywait = setTimeout(function() { if (_.vimeopausecalled!==true) _R.playVideo(_nc,id);},100);
			break;
			case "html5":	

				if (!_.metaloaded)
					addEvent(_.video,'loadedmetadata',function(_nc) {
						_R.resetVideo(_nc,id);

						_.video.play();
						var	ct = _.video.currentTime;
						if (_.nseTriggered) {
								ct=-1;
								_.nseTriggered = false;
							}
						if (_.ssec!=-1 && _.ssec>ct) _.video.currentTime = _.ssec;
					}(_nc));
				else {		

					playHTML5(_.video);
					var ct = _.video.currentTime;
					if (_.nseTriggered) {
							ct=-1;
							_.nseTriggered = false;
						}
					if (_.ssec!=-1 && _.ssec>ct) _.video.currentTime = _.ssec;
				}
			break;
		}
	},

	isVideoPlaying : function(_nc,id) {
		var ret = false;
		if (_R[id].playingvideos != undefined) {
			jQuery.each(_R[id].playingvideos,function(i,nc) {
				if (_nc.attr('id') == nc.attr('id')) ret = true;
			});
		}
		return ret;
	},

	removeMediaFromList : function(_nc,id) {
		remVidfromList(_nc,id);
	},

	prepareCoveredVideo : function(id) {
		clearTimeout(_R[id].resizePrepareCoverVideolistener);
		var w = _R[id].sliderType==="carousel" ? _R[id].carousel.justify ? _R[id].carousel.slide_widths===undefined ? undefined : _R[id].carousel.slide_widths[_R[id].carousel.focused] : _R[id].carousel.slide_width : _R[id].conw,
			h = _R[id].sliderType==="carousel" ? _R[id].carousel.slide_height : _R[id].conh;
		if (w===0 || h===0 || w===undefined || h===undefined) {			
			_R.contWidthManager(id,'containerResized_1');
			_R.updateDimensions(id);				
			_R.setSize(id);					
			_R[id].resizePrepareCoverVideolistener = setTimeout(function() { _R.prepareCoveredVideo(id);},100);			
			return;
		}
		
		// Go Through all Known Videos, and Pick VIMEO Videos wenn they set as BG Videos and/or Force Cover Videos
		for (var i in _R[id].videos) {												
			var _ = _R[id].videos[i];
			if (!_.bgvideo && !_.fcover) continue;

			
			if (_.type==="html5" && _.jvideo!==undefined) tpGS.gsap.set(_.jvideo,{width:w});						
			if ((_R[id].activeRSSlide!==undefined && _.slideid!==_R.gA(_R[id].slides[_R[id].activeRSSlide],"key")) && (_R[id].pr_next_slide!==undefined && _.slideid!==_R.gA(_R[id].pr_next_slide[0],"key"))) continue;													
			_.vd =  _.ratio.split(':').length>1 ? _.ratio.split(':')[0] / _.ratio.split(':')[1]  : 1;						
			var od = w / h,
				nvh = (od/_.vd)*100,
				nvw = (_.vd/od)*100;						
			if (_R.get_browser()==="Edge" || _R.get_browser()==="IE") {			
				_.ifr = _.ifr===undefined ? _.video : _.ifr;
				if (od>_.vd) 
					tpGS.gsap.set(_.ifr,{minWidth:"100%", height:nvh+"%", x:"-50%", y:"-50%", top:"50%",left:"50%",position:"absolute"});												
				else 
					tpGS.gsap.set(_.ifr,{minHeight:"100%", width:nvw+"%", x:"-50%", y:"-50%", top:"50%",left:"50%",position:"absolute"});					
			} else {			
				if (od>_.vd) 
					tpGS.gsap.set(_.ifr,{height:nvh+"%", width:"100%", top:-(nvh-100)/2+"%",left:"0px",position:"absolute"});		
				else 
					tpGS.gsap.set(_.ifr,{width:nvw+"%", height:"100%", left:-(nvw-100)/2+"%",top:"0px",position:"absolute"});
			}					
		}
	},

	checkVideoApis : function(_nc,id) {
		var httpprefix = location.protocol === 'https:' ? "https" : "http";
		if (!_R[id].youtubeapineeded) {
			if ((_nc.data('ytid')!=undefined  || _nc.find('iframe').length>0 && _nc.find('iframe').attr('src').toLowerCase().indexOf('youtube')>0)) _R[id].youtubeapineeded = true;
			if (_R[id].youtubeapineeded && !window.rs_addedyt) {
				_R[id].youtubestarttime = jQuery.now();
				window.rs_addedyt=true;
				var s = document.createElement("script"),
					before = document.getElementsByTagName("script")[0],
					loadit = true;
				s.src = "https://www.youtube.com/iframe_api";

				jQuery('head').find('*').each(function(){
					if (jQuery(this).attr('src') == "https://www.youtube.com/iframe_api")
					   loadit = false;
				});
				if (loadit) before.parentNode.insertBefore(s, before);
			}
		}
		if (!_R[id].vimeoapineeded) {
			if ((_nc.data('vimeoid')!=undefined || _nc.find('iframe').length>0 && _nc.find('iframe').attr('src').toLowerCase().indexOf('vimeo')>0)) _R[id].vimeoapineeded = true;
		  	if (_R[id].vimeoapineeded && !window.rs_addedvim) {
				_R[id].vimeostarttime = jQuery.now();
				window.rs_addedvim=true;
				var vimeoPlayerUrl = 'https://player.vimeo.com/api/player.js',
					loadit = true;
                if (loadit) {
                    var _isMinified = true;
                    jQuery.each(document.getElementsByTagName('script'), function(key, item) {
                        if (item.src.length != 0 && item.src.indexOf('.min.js') == -1 && item.src.indexOf(document.location.host) != -1 ) {
                            _isMinified = false;
                        }
                    });
                    require([_isMinified ? 'vimeoPlayer' : vimeoPlayerUrl], function(vimeoPlayer) {
                        window['Vimeo'] = {Player: vimeoPlayer};
                    });
                }
			}
		}
	},

	manageVideoLayer : function(_nc,id,slideid) {		


		if (_R.gA(_nc[0],"videoLayerManaged")===true || _R.gA(_nc[0],"videoLayerManaged")==="true") return false;
		_R[id].videos = _R[id].videos===undefined ? {} : _R[id].videos;		
		var _ = _R[id].videos[_nc[0].id] = _R[id].videos[_nc[0].id]===undefined ? readVideoDatas(_nc.data(),undefined,slideid) : _R[id].videos[_nc[0].id];			
		_.audio = _.audio===undefined ? false : _.audio;
		if (!_ISM || !_.opom) {
			_.id = _nc[0].id;
			_.pload = _.pload === "auto" || _.pload === "canplay" || _.pload === "canplaythrough" || _.pload === "progress" ? "auto" : _.pload;
			_.type = (_.mp4!=undefined || _.webm!=undefined) ? "html5" : (_.ytid!=undefined && String(_.ytid).length>1) ? "youtube" : (_.vimeoid!=undefined && String(_.vimeoid).length>1) ? "vimeo" : "none";
			_.newtype = (_.type=="html5" && _nc.find(_.audio ? "audio" : "video").length==0) ? "html5" : (_.type=="youtube" && _nc.find('iframe').length==0) ? "youtube" : (_.type=="vimeo" && _nc.find('iframe').length==0) ? "vimeo" : "none";

			// PREPARE TIMER BEHAVIOUR BASED ON AUTO PLAYED VIDEOS IN SLIDES
			if (!_.audio && _.aplay == "1sttime" && _.pausetimer && _.bgvideo) _R.sA(_nc.closest('rs-slide')[0],"rspausetimeronce",1);			
			if (!_.audio && _.bgvideo && _.pausetimer && (_.aplay==true || _.aplay=="true" || _.aplay == "no1sttime"))  _R.sA(_nc.closest('rs-slide')[0],"rspausetimeralways",1);

			if (_.noInt) _nc.addClass("rs-nointeraction");
			// ADD HTML5 VIDEO IF NEEDED
			switch (_.newtype) {
				case "html5":
					if (window.isSafari11==true) _R[id].slideHasIframe = true;							
					if (_.audio) _nc.addClass("rs-audio");
					_.tag = _.audio ? "audio" : "video";
					var _funcs = _.tag==="video" && (_R.is_mobile() || _R.isSafari11()) ? _.aplay || _.aplay==="true" ? 'muted playsinline autoplay' : _.inline ? ' playsinline' : '' : '',
						apptxt = '<'+_.tag+' '+_funcs+' '+(_.controls && _.controls!=="none" ? ' controls ':'') +' style="'+(_R.get_browser()!=="Edge" ? 'object-fit:cover;background-size:cover;opacity:0;width:100%; height:100%' : '') +'" class="" '+(_.loop ? 'loop' : '')+' preload="'+_.pload+'">';

					if (_.tag === 'video' && _.webm!=undefined && _R.get_browser().toLowerCase()=="firefox") apptxt = apptxt + '<source src="'+_.webm+'" type="video/webm" />';
					if (_.mp4!=undefined) apptxt = apptxt + '<source src="'+_.mp4+'" type="'+ (_.tag==="video" ? 'video/mp4' : 'audio/mpeg')+'" />';
					if (_.ogv!=undefined) apptxt = apptxt + '<source src="'+_.mp4+'" type="'+_.tag+'/ogg" />';
					apptxt += '</'+_.tag+'>';
					_.videomarkup = apptxt;
					if (!(_ISM && _.notonmobile) && !_R.isIE(8)) _nc.append(apptxt);

					// ADD HTML5 VIDEO CONTAINER
					if (!_nc.find(_.tag).parent().hasClass("html5vid"))	_nc.find(_.tag).wrap('<div class="html5vid '+(_.afs===false ? "hidefullscreen" : "")+'" style="position:relative;top:0px;left:0px;width:100%;height:100%; overflow:hidden;"></div>');					
					_.jvideo = _nc.find(_.tag);
					_.video = _.jvideo[0];
					_.html5vid = _.jvideo.parent();

					if (!_.metaloaded)
						addEvent(_.video,'loadedmetadata',function(_nc) {
							htmlvideoevents(_nc,id);
							_R.resetVideo(_nc,id);
						}(_nc));
				break;

				case "youtube":			
					_R[id].slideHasIframe = true;	
					if (!_.controls || _.controls==="none") {					
				 		_.vatr = _.vatr.replace("controls=1","controls=0");
				 		if (_.vatr.toLowerCase().indexOf('controls')==-1) _.vatr = _.vatr+"&controls=0";
				 	}
				 	if (_.inline || _nc[0].tagName==="RS-BGVIDEO") _.vatr = _.vatr + "&playsinline=1";

				 	if (_.ssec!=-1) _.vatr+="&start="+_.ssec;
				 	if (_.esec!=-1) _.vatr+="&end="+_.esec;				 	
				 	var orig = _.vatr.split('origin=https://');
				 	_.vatrnew = orig.length>1 ? orig[0]+'origin=https://' + ((self.location.href.match(/www/gi) && !orig[1].match(/www/gi)) ? "www."+orig[1] : orig[1]) : _.vatr;				 		 		
				 	_.videomarkup = '<iframe allow="autoplay; '+(_.afs===true ? "fullscreen" : "")+'" type="text/html" src="https://www.youtube-nocookie.com/embed/'+_.ytid+'?'+_.vatrnew+'" '+(_.afs===true ? "allowfullscreen" : "")+' width="100%" height="100%" class="intrinsic-ignore" style="opacity:0;visibility:visible;width:100%;height:100%"></iframe>';
				break;

				case "vimeo":	

					_R[id].slideHasIframe = true;	
					if (!_.controls || _.controls==="none") {					
				 		_.vatr = _.vatr.replace("background=1","background=0");
				 		if (_.vatr.toLowerCase().indexOf('background')==-1) _.vatr = _.vatr+"&background=0";
				 	} else {
				 		_.vatr = _.vatr.replace("background=0","background=1");
				 		if (_.vatr.toLowerCase().indexOf('background')==-1) _.vatr = _.vatr+"&background=1";
				 	}
					_.vatr = 'autoplay='+(_.aplay===true ? 1 : 0)+'&'+_.vatr;
					if (_ISM) _.vatr = 'muted=1&'+_.vatr;
					if (_.loop) _.vatr = 'loop=1&'+_.vatr;								
					_.videomarkup = '<iframe  allow="autoplay; '+(_.afs===true ? "fullscreen" : "")+'" src="https://player.vimeo.com/video/'+_.vimeoid+'?'+_.vatr+'" '+(_.afs===true ? "webkitallowfullscreen mozallowfullscreen allowfullscreen" : "")+' width="100%" height="100%" class="intrinsic-ignore" style="opacity:0;visibility:visible;100%;height:100%"></iframe>';
				break;
			}				
			if (_.poster!=undefined && _.poster.length>2 && !(_ISM && _.npom)) {
				if (_nc.find('rs-poster').length==0) _nc.append('<rs-poster class="noSwipe" style="background-image:url('+_.poster+');"></rs-poster>');
				if (_nc.find('iframe').length==0)
				_nc.find('rs-poster').click(function() {
					_R.playVideo(_nc,id);
					if (_ISM) {
						if (_.notonmobile) return false;
						tpGS.gsap.to(_nc.find('rs-poster'),0.3,{opacity:0,visibility:"hidden",force3D:"auto",ease:"power3.inOut"});
						tpGS.gsap.to(_nc.find('iframe'),0.3,{opacity:1,display:"block",ease:"power3.inOut"});
					}
				})
			} else {
				if  (_ISM && _.notonmobile) return false;
				if (_nc.find('iframe').length==0 && (_.type=="youtube" || _.type=="vimeo")) {
					delete _.vimeoplayer;
					_nc.append(_.videomarkup);
					addVideoListener(_nc,id,false);
				}
			}

			// ADD DOTTED OVERLAY IF NEEDED
			if (_.doverlay !=="none" && _.doverlay!==undefined)
				if (_.bgvideo) { if  (_nc.closest('rs-sbg-wrap').find('rs-dotted').length!=1) _nc.closest('rs-sbg-wrap').append('<rs-dotted class="'+_.doverlay+'"></rs-dotted>');
				} else if (_nc.find('rs-dotted').length!=1) _nc.append('<rs-dotted class="'+_.doverlay+'"></rs-dotted>');
			_R.sA(_nc[0],"videoLayerManaged",true);			
			if (_.bgvideo) tpGS.gsap.set(_nc.find('video, iframe'),{opacity:0});
		} else {			
			if (_nc.find('rs-poster').length==0) _nc.append('<rs-poster class="noSwipe" style="background-image:url('+_.poster+');"></rs-poster>');
		}
	}
});

function getStartSec(st) {
	return st == undefined ? -1 :jQuery.isNumeric(st) ? st : st.split(":").length>1 ? parseInt(st.split(":")[0],0)*60 + parseInt(st.split(":")[1],0) : st;
};

var addEvent = function(element, eventName, callback) {
	if (element.addEventListener)
		element.addEventListener(eventName, callback, {capture:false,passive:true});
	else
		element.attachEvent(eventName, callback, {capture:false,passive:true});
};

var pushVideoData = function(p,t,d) {
	var a = {};
	a.video = p;
	a.type = t;
	a.settings = d;
	return a;
}

var callPrepareCoveredVideo = function(id,_nc) {
	var _ = _R[id].videos[_nc[0].id];
	// CARE ABOUT ASPECT RATIO
	if (_.bgvideo || _.fcover) {
		if (_.fcover) _nc.removeClass("rs-fsv").addClass("coverscreenvideo");
		if (_.ratio===undefined || _.ratio.split(":").length<=1) _.ratio = "16:9";
		_R.prepareCoveredVideo(id);	
	}
}

// SET VOLUME OF THE VIMEO
var vimeoVolume = function(_,p) {		
	var v = _.vimeoplayer;	
	v.getPaused().then(function(paused) {
	    _.volumetoken = true;
	    var isplaying = !paused,	    	
	    	promise = v.setVolume(p);
	    
		if (promise!==undefined) {
			promise.then(function(e) {
				v.getPaused().then(function(paused) {
				    if (isplaying === paused) {
				    	_.volume = "mute";
				    	_.volumetoken = true;
				    	v.setVolume(0);				    	
				    	v.play();
				    }
				}).catch(function(e) {
					console.log("Get Paused Function Failed for Vimeo Volume Changes Inside the Promise");
				});
			}).catch(function(e) {
				if (isplaying) {
					_.volume = "mute";
					_.volumetoken = true;
					v.setVolume(0);					
					v.play();
				}
			});
		}
	}).catch(function(){
		console.log("Get Paused Function Failed for Vimeo Volume Changes");
	});
}

// SET YOUTUBE VOLUME
var ytVolume = function(_,p) {
	var wasplaying = _.player.getPlayerState();

	if (p==="mute")
		_.player.mute();
	else {
		_.player.unMute();
		_.player.setVolume(p);
	}

	setTimeout(function() {
		if (wasplaying===1 && _.player.getPlayerState()!==1) {
			_.player.mute();
			_.player.playVideo();
		}
	},39);

}

// ERROR HANDLING FOR VIDEOS BY CALLING

var playHTML5 = function(v) {
	var promise = v.play();
	if (promise!==undefined) promise.then( function(e) {}).catch(function(e) {
		v.pause();
	})
}

var playVimeo = function(v) {			
	var promise = v.play();
	if (promise!==undefined) promise.then( function(e) {}).catch(function(e) {
		v.volumetoken=true;	
		v.setVolume(0);
		v.play();
	});
}

var playYouTube = function(_) {		
	_.player.playVideo();	
	setTimeout(function() {
		if (_.player.getPlayerState()!==1 && _.player.getPlayerState()!==3) {
			_.volume = "mute";
			_.player.mute();
			_.player.playVideo();
		}
	},39);
}

var vimeoPlayerPlayEvent = function(_,_nc,id) {		
	_.vimeostarted = true;
	_.nextslidecalled = false;
	var poster = _nc.find('rs-poster');
	_.ifr = _nc.find('iframe');	
	
	if (poster!==undefined && poster.length>0) {
		tpGS.gsap.to(poster,0.3,{opacity:0,visibility:"hidden", force3D:"auto",ease:"power3.inOut"});		
		if (_.ifr!==undefined && _.ifr.length>0) tpGS.gsap.to(_.ifr,0.3,{opacity:1,display:"block",ease:"power3.inOut"});
	} else		
		if (_.ifr!==undefined && _.ifr.length>0) tpGS.gsap.to(_.ifr,0.001,{opacity:1,display:"block",ease:"power3.out"});
	
	_R[id].c.trigger('revolution.slide.onvideoplay',pushVideoData(_.vimeoplayer,"vimeo",_));
	_R[id].stopByVideo=_.pausetimer;
	addVidtoList(_nc,id);
	if (_.volume=="mute" || _.volume===0 || _R.lastToggleState(_nc.data('videomutetoggledby')) || _R[id].globalmute===true) {_.volumetoken=true;_.vimeoplayer.setVolume(0);} else vimeoVolume(_,parseInt(_.volcache,0)/100 || 0.75);	
	_R.toggleState(_.videotoggledby);
}

var addVideoListener = function(_nc,id,startnow) {
	var _=	_R[id].videos[_nc[0].id],
		frameID = "iframe"+Math.round(Math.random()*100000+1);
	_.ifr = _nc.find('iframe');

	callPrepareCoveredVideo(id,_nc);

	_.ifr.attr('id',frameID);
	_.startvideonow = startnow;

	if (_.videolistenerexist) {
		if (startnow)
			switch (_.type) {
				case "youtube":
					playYouTube(_);
					if (_.ssec!=-1) _.player.seekTo(_.ssec)
				break;
				case "vimeo":
					playVimeo(_.vimeoplayer);
					if (_.ssec!=-1) _.vimeoplayer.seekTo(_.ssec);
				break;
			}
	}else {
		switch (_.type) {
			// YOUTUBE LISTENER
			case "youtube":			
				if (typeof YT==='undefined' || YT.Player===undefined) {
					_R.checkVideoApis(_nc,id);
					setTimeout(function() { addVideoListener(_nc,id,startnow);},50);										
					return;
				}								
				_.player = new YT.Player(frameID, {
					events: {
						'onStateChange': function(event) {
								if (event.data == YT.PlayerState.PLAYING) {											
									tpGS.gsap.to(_nc.find('rs-poster'),0.3,{opacity:0,visibility:"hidden",force3D:"auto",ease:"power3.inOut"});
									tpGS.gsap.to(_.ifr,0.3,{opacity:1,display:"block",ease:"power3.inOut"});	

									if (_.volume=="mute" || _.volume===0 || _R.lastToggleState(_nc.data('videomutetoggledby')) || _R[id].globalmute===true)
										  _.player.mute();
									else ytVolume(_,parseInt(_.volcache,0) || 75);

									_R[id].stopByVideo=true;
									addVidtoList(_nc,id);
									if (_.pausetimer) _R[id].c.trigger('stoptimer'); else _R[id].stopByVideo=false;

									_R[id].c.trigger('revolution.slide.onvideoplay',pushVideoData(_.player,"youtube",_));
									_R.toggleState(_.videotoggledby);
								} else {
									if (event.data==0 && _.loop) {
										if (_.ssec!=-1) _.player.seekTo(_.ssec);											
										playYouTube(_);
										_R.toggleState(_.videotoggledby);
									}
									if (!_R.checkfullscreenEnabled(id) && (event.data==0 || event.data==2) && ((_.scop && _nc.find('rs-poster').length>0) || (_.bgvideo && _nc.find('.rs-fullvideo-cover').length>0))) {
										
										if (_.bgvideo)
											tpGS.gsap.to(_nc.find('.rs-fullvideo-cover'),0.1,{opacity:1,force3D:"auto",ease:"power3.inOut"});
										else 
											tpGS.gsap.to(_nc.find('rs-poster'),0.1,{opacity:1,visibility:"visible",force3D:"auto",ease:"power3.inOut"});										
										tpGS.gsap.to(_.ifr,0.1,{opacity:0,ease:"power3.inOut"});
									}
									if ((event.data!=-1 && event.data!=3)) {																												
										_R[id].stopByVideo=false;
										_R[id].tonpause = false;
										remVidfromList(_nc,id);
										_R[id].c.trigger('starttimer');
										_R[id].c.trigger('revolution.slide.onvideostop',pushVideoData(_.player,"youtube",_));
										if (_R[id].videoIsPlaying==undefined || _R[id].videoIsPlaying.attr("id") == _nc.attr("id")) _R.unToggleState(_.videotoggledby);
									}

									if (event.data==0 && _.nse) {
										exitFullscreen();
										_.nseTriggered = true;
										_R[id].c.revnext();
										remVidfromList(_nc,id);
									} else {
										remVidfromList(_nc,id);
										_R[id].stopByVideo=false;

										if (event.data===3 || (_.lasteventdata==-1 || _.lasteventdata==3 || _.lasteventdata===undefined) && (event.data==-1 || event.data==3)) {
											//Can be ignored
										} else {
											_R[id].c.trigger('starttimer');
										}
										_R[id].c.trigger('revolution.slide.onvideostop',pushVideoData(_.player,"youtube",_));
										if (_R[id].videoIsPlaying==undefined || _R[id].videoIsPlaying.attr("id") == _nc.attr("id"))	_R.unToggleState(_.videotoggledby);
									}
								}
								_.lasteventdata = event.data;
							},
						'onReady': function(event) {
							var playerMuted,
								isVideoMobile = _R.is_mobile(),
								isVideoLayer = _nc.hasClass('rs-layer-video');
							if ((isVideoMobile || _R.isSafari11() && !(isVideoMobile && isVideoLayer)) && (_nc[0].tagName==="RS-BGVIDEO" || (isVideoLayer && (_.aplay===true || _.aplay==="true")))) {

								playerMuted = true;
								_.player.setVolume(0);
								_.volume = "mute";
								_.player.mute();
								clearTimeout(_nc.data('mobilevideotimr'));
								if (_.player.getPlayerState()===2 || _.player.getPlayerState()===-1) _nc.data('mobilevideotimr', setTimeout(function() {playYouTube(_);}, 500));								
							}

							if(!playerMuted && _.volume=="mute") {
								_.player.setVolume(0);
								_.player.mute();
							}

							_nc.addClass("rs-apiready");
							if (_.speed!=undefined || _.speed!==1) event.target.setPlaybackRate(parseFloat(_.speed));

							// PLAY VIDEO IF THUMBNAIL HAS BEEN CLICKED
							_nc.find('rs-poster').unbind("click");
							_nc.find('rs-poster').click(function() { if (!_ISM) playYouTube(_);})

							if (_.startvideonow) {
								playYouTube(_);
								if (_.ssec!=-1) _.player.seekTo(_.ssec);
							}
							_.videolistenerexist = true;
						}
					}
				});
			break;

			// VIMEO LISTENER
			case "vimeo":
				if (typeof Vimeo==='undefined' || Vimeo.Player===undefined) {
					_R.checkVideoApis(_nc,id);
					setTimeout(function() { addVideoListener(_nc,id,startnow);},50);										
					return;
				}
								
				var isrc = _.ifr.attr('src'),
					queryParameters = {}, queryString = isrc,
					re = /([^&=]+)=([^&]*)/g, m;
				// Creates a map with the query string parameters
				while (m = re.exec(queryString)) queryParameters[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);

				if (queryParameters['player_id']!=undefined)
					isrc = isrc.replace(queryParameters['player_id'],frameID);
				else
					isrc=isrc+"&player_id="+frameID;

				isrc = isrc.replace(/&api=0|&api=1/g, '');

				var isVideoMobile = _R.is_mobile(),
					deviceCheck = isVideoMobile || _R.isSafari11(),
					isVideoBg = _nc[0].tagName==="RS-BGVIDEO";

				if(deviceCheck && isVideoBg) isrc += '&background=1';
				_.ifr.attr('src',isrc);

				_.vimeoplayer = _.vimeoplayer===undefined || _.vimeoplayer===false ? new Vimeo.Player(frameID) : _.vimeoplayer;

				if(deviceCheck) {
					var toMute;
					if(isVideoBg)
						toMute = true;
					else if(_.aplay || _.aplay==="true") {
						//if(isVideoMobile) _.aplay = false;  // Removed Line to allow Auto Play also on further loops.
						toMute = true;
					}

					if(toMute) {
						_.volumetoken=true;
						_.vimeoplayer.setVolume(0);
						_.volume = "mute";
					}
				}

				_.vimeoplayer.on('play', function(data) {						
					if (!_.vimeostarted) vimeoPlayerPlayEvent(_,_nc,id);
				});


				// Read out the Real Aspect Ratio from Vimeo Video
				_.vimeoplayer.on('loaded',function(data) {		
				
					var newas = {};
					_.vimeoplayer.getVideoWidth().then( function(width) {
						newas.width = width;
						if (newas.width!==undefined && newas.height!==undefined) {
							_.ratio = newas.width+":"+newas.height;
							_.vimeoplayerloaded = true;
							callPrepareCoveredVideo(id,_nc);
						}
					});
					_.vimeoplayer.getVideoHeight().then( function(height) {
						newas.height = height;
						if (newas.width!==undefined && newas.height!==undefined) {
							_.ratio = newas.width+":"+newas.height;
							_.vimeoplayerloaded = true;
							callPrepareCoveredVideo(id,_nc);
						}
					});
					if (_.startvideonow) {
						if (_.volume==="mute") {_.volumetoken=true;_.vimeoplayer.setVolume(0);}
						playVimeo(_.vimeoplayer);
						if (_.ssec!=-1) _.vimeoplayer.setCurrentTime(_.ssec);
					}
				});

				_nc.addClass("rs-apiready");

				_.vimeoplayer.on('volumechange',function(data) {					
					if (_.volumetoken) _.volume = data.volume;					
					_.volumetoken = false;					
				});

				_.vimeoplayer.on('timeupdate',function(data) {	

					if (!_.vimeostarted && data.percent!==0 && (_R[id].activeRSSlide===undefined || _.slideid===_R.gA(_R[id].slides[_R[id].activeRSSlide],"key"))) vimeoPlayerPlayEvent(_,_nc,id);																									
					if (_.pausetimer && _R[id].sliderstatus=="playing") {
						_R[id].stopByVideo = true;
						_R[id].c.trigger('stoptimer');
					}
					_.currenttime = data.seconds;
					if (_.esec!=0 && _.esec!==-1 && _.esec<data.seconds && _.nextslidecalled!==true) {
						if (_.loop) {
							playVimeo(_.vimeoplayer);
							_.vimeoplayer.setCurrentTime(_.ssec!==-1 ? _.ssec : 0);
						} else {
							if (_.nse) {
								_.nseTriggered = true;
								_.nextslidecalled = true;
								_R[id].c.revnext();
							}

							_.vimeoplayer.pause();
						}
					}
				});

				_.vimeoplayer.on('ended', function(data) {	
				
						_.vimeostarted = false;
						remVidfromList(_nc,id);
						_R[id].stopByVideo=false;
						_R[id].c.trigger('starttimer');
						_R[id].c.trigger('revolution.slide.onvideostop',pushVideoData(_.vimeoplayer,"vimeo",_));
						if (_.nse) {
							_.nseTriggered = true;
							_R[id].c.revnext();
						}
						if (_R[id].videoIsPlaying==undefined || _R[id].videoIsPlaying.attr("id") == _nc.attr("id")) _R.unToggleState(_.videotoggledby);

				});

				_.vimeoplayer.on('pause', function(data) {	
				
						_.vimeostarted = false;										
						if (((_.scop && _nc.find('rs-poster').length>0) || (_.bgvideo && _nc.find('.rs-fullvideo-cover').length>0))) {							
							if (_.bgvideo)
								tpGS.gsap.to(_nc.find('.rs-fullvideo-cover'),0.1,{opacity:1,force3D:"auto",ease:"power3.inOut"});
							else
								tpGS.gsap.to(_nc.find('rs-poster'),0.1,{opacity:1,visibility:"visible", force3D:"auto",ease:"power3.inOut"});							
							tpGS.gsap.to(_nc.find('iframe'),0.1,{opacity:0,ease:"power3.inOut"});
						}
						_R[id].stopByVideo = false;
						_R[id].tonpause = false;

						remVidfromList(_nc,id);
						_R[id].c.trigger('starttimer');
						_R[id].c.trigger('revolution.slide.onvideostop',pushVideoData(_.vimeoplayer,"vimeo",_));
						if (_R[id].videoIsPlaying==undefined || _R[id].videoIsPlaying.attr("id") == _nc.attr("id")) _R.unToggleState(_.videotoggledby);

				});

				_nc.find('rs-poster').unbind("click");
				_nc.find('rs-poster').click(function() {
					 if (!_ISM) {
						playVimeo(_.vimeoplayer);
						return false;
					 }
				});

				_.videolistenerexist = true;
			break;
		}
	}
}


var exitFullscreen = function() {	
  if(document.exitFullscreen && document.fullscreen) {
    document.exitFullscreen();
  } else if(document.mozCancelFullScreen && document.mozFullScreen) {
    document.mozCancelFullScreen();
  } else if(document.webkitExitFullscreen && document.webkitIsFullScreen) {
    document.webkitExitFullscreen();
  }
}


_R.checkfullscreenEnabled = function(id) {  
    if (window['fullScreen'] !== undefined) return window.fullScreen;
    if (document.fullscreen !==undefined) return document.fullscreen;
    if (document.mozFullScreen!==undefined) return document.mozFullScreen;
    if (document.webkitIsFullScreen!==undefined) return document.webkitIsFullScreen;        
    var h = (jQuery.browser.webkit && /Apple Computer/.test(navigator.vendor)) ? 42 : 5;
    return screen.width == _R.winW && Math.abs(screen.height - _R.getWinH(id)) < h;	 
  }

/////////////////////////////////////////	HTML5 VIDEOS 	///////////////////////////////////////////

var htmlvideoevents = function(_nc,id,startnow) {
	var _ = _R[id].videos[_nc[0].id];

	if (_ISM && _.notonmobile) return false;
	_.metaloaded = true;


	//PLAY, STOP VIDEO ON CLICK OF PLAY, POSTER ELEMENTS
	if (!_.controls || _.audio) {
		if (_nc.find('.tp-video-play-button').length==0 && !_ISM) _nc.append('<div class="tp-video-play-button"><i class="revicon-right-dir"></i><span class="tp-revstop">&nbsp;</span></div>');
		_nc.find('video, rs-poster, .tp-video-play-button').click(function() {
			if (_nc.hasClass("videoisplaying"))
				_.video.pause();
			else
				_.video.play();
		});
	}

	// PRESET FULLCOVER VIDEOS ON DEMAND
	if (_.fcover || _nc.hasClass('rs-fsv') || _.bgvideo)  {
		if (_.fcover || _.bgvideo) {
			_.html5vid.addClass("fullcoveredvideo");
			if (_.ratio===undefined || _.ratio.split(':').length==1) _.ratio = "16:9";
			_R.prepareCoveredVideo(id); //-> Only for Vimeo Videos !?
		}
		else _.html5vid.addClass("rs-fsv");
	}

	addEvent(_.video,"canplaythrough", function() { _R.preLoadAudioDone(_nc,id,"canplaythrough");});

	addEvent(_.video,"canplay", function() {_R.preLoadAudioDone(_nc,id,"canplay");});

	addEvent(_.video,"progress", function() {_R.preLoadAudioDone(_nc,id,"progress");});
	

	// Update the seek bar as the video plays

	addEvent(_.video,"timeupdate", function(a) {		
		// FIX for Safari Start Animation (6.2.3 - 30.04.2020)
		// Added VideoIsVisible which first added when the Progress real starts. Other way Video Cover would aniate out on Video Play which is on Safari not equal to real Playing Progress, and we see blank layer shortly!		 
		if (_nc!==undefined && _nc[0]!==undefined && _.VideoIsVisible!==true && _.justReseted!==true) {			
			if (_nc[0].getElementsByTagName('rs-poster').length>0) {

				tpGS.gsap.to(_nc[0].getElementsByTagName('rs-poster'),0.3,{opacity:0,visibility:"hidden", force3D:"auto",ease:"power3.inOut"});
				if (_nc.find(_.tag).length>0) tpGS.gsap.to(_nc.find(_.tag),0.3,{opacity:1,display:"block",ease:"power3.inOut"});	
			} else 
			if (_nc.find(_.tag).length>0) tpGS.gsap.to(_nc.find(_.tag),0.001,{opacity:1,display:"block",ease:"power3.out"});	
			_.VideoIsVisible = true;
			// CANCEL SHOWING POSTER
			clearTimeout(_.showCoverSoon);
		}		

		// VIDEO IS JUST RESETED, LETS TRY TO SHOW POSTER UNTIL THINGS STARTS
		if (_.justReseted) {			
			if (_R.checkfullscreenEnabled(id)!=true && _nc.find('rs-poster').length>0) {
				_.showCoverSoon = setTimeout(function() {	
					if (!_.seeking) {
						tpGS.gsap.to(_nc.find('rs-poster'),0.001,{opacity:1,visibility:"visible",force3D:"auto",ease:"power3.inOut"});
						tpGS.gsap.to(_nc.find(_.tag),0.0001,{opacity:0,ease:"power3.inOut"});
					}
					_.VideoIsVisible = false;
				},500);								
			} 					
		}

		_.justReseted = false;
		// FIX for Safari Loop (6.2.3 - 30.04.2020)
		// which will give a Delay if we dont overtake the restart little earlier than the real End ! 	

		if (_.esec===-1 && _.loop && window.isSafari11==true) _.esec = _.video.duration-0.075;
		if (_.esec!=0 && _.esec!=-1 && _.esec<_.video.currentTime && !_.nextslidecalled) {						
			if (_.loop) {
				_.video.play();				
				_.video.currentTime = _.ssec===-1 ? 0.5 : _.ssec;				
			} else {
				if (_.nse) {
					_.nseTriggered = true;
					_.nextslidecalled = true;
					_R[id].jcnah = true;
					_R[id].c.revnext();
					setTimeout(function() {
						_R[id].jcnah = false;
					},1000);
				}
				_.video.pause();
			}
		}
	});

	// VIDEO EVENT LISTENER FOR "PLAY"
	addEvent(_.video,"play",function() {			
		_.nextslidecalled = false;
		_.volume = _.volume!=undefined && _.volume!="mute" ? parseFloat(_.volcache) : _.volume;		
		_.volcache = _.volcache!=undefined && _.volcache!="mute" ? parseFloat(_.volcache) : _.volcache;				
		if (!_R.is_mobile() && !_R.isSafari11()) {
			if (_R[id].globalmute===true) _.video.muted = true; else _.video.muted = _.volume=="mute" ? true : false;
			_.volcache = jQuery.isNumeric(_.volcache) && _.volcache>1 ? _.volcache/100 : _.volcache;
			if (_.volume=="mute") _.video.muted=true;
			else if (_.volcache!=undefined) _.video.volume = _.volcache;
		}

		_nc.addClass("videoisplaying");

		addVidtoList(_nc,id);
		clearTimeout(_.showCoverSoon)

		if (_.pausetimer!==true || _.tag=="audio") {
			_R[id].stopByVideo = false;
			_R[id].c.trigger('revolution.slide.onvideostop',pushVideoData(_.video,"html5",_));
		} else {
			_R[id].stopByVideo = true;
			_R[id].c.trigger('revolution.slide.onvideoplay',pushVideoData(_.video,"html5",_));
		}

		if (_.pausetimer && _R[id].sliderstatus=="playing") {
			_R[id].stopByVideo = true;
			_R[id].c.trigger('stoptimer');
		}
		
		_R.toggleState(_.videotoggledby);
	});

	addEvent(_.video,"seeked",function() {_.seeking = false;});

	addEvent(_.video,"seeking",function() {_.seeking = true;});

	// VIDEO EVENT LISTENER FOR "PAUSE"
	addEvent(_.video,"pause",function(e) {			
		var fsmode = _R.checkfullscreenEnabled(id);				
		if (!fsmode && _nc.find('rs-poster').length>0 && _.scop) {
			_.showCoverSoon = setTimeout(function() {	
				if (!_.seeking) {
					tpGS.gsap.to(_nc.find('rs-poster'),0.001,{opacity:1,visibility:"visible",force3D:"auto",ease:"power3.inOut"});
					tpGS.gsap.to(_nc.find(_.tag),0.0001,{opacity:0,ease:"power3.inOut"});
				}
			},500);
			_.VideoIsVisible = false;
			// FIX for Safari Start Animation (6.2.3 - 30.04.2020) 
			// We can set the Visibility to false since poster is over video now
		}
		_nc.removeClass("videoisplaying");
		_R[id].stopByVideo = false;
		remVidfromList(_nc,id);
		if (_.tag!="audio")  _R[id].c.trigger('starttimer');
		_R[id].c.trigger('revolution.slide.onvideostop',pushVideoData(_.video,"html5",_));

		if (_R[id].videoIsPlaying==undefined || _R[id].videoIsPlaying.attr("id") == _nc.attr("id")) _R.unToggleState(_.videotoggledby);
	});

	// VIDEO EVENT LISTENER FOR "END"
	addEvent(_.video,"ended",function() {			
		exitFullscreen();
		remVidfromList(_nc,id);
		_R[id].stopByVideo = false;
		remVidfromList(_nc,id);
		if (_.tag!="audio") _R[id].c.trigger('starttimer');
		_R[id].c.trigger('revolution.slide.onvideostop',pushVideoData(_.video,"html5",_nc.data()));

		if (_.nse && _.video.currentTime>0) {
			if (!_R[id].jcnah==true) {
				_.nseTriggered = true;
				_R[id].c.revnext();
				_R[id].jcnah = true;
			}
			setTimeout(function() {
				_R[id].jcnah = false;
			},1500)
		}
		_nc.removeClass("videoisplaying");
		if (_R[id].inviewport===true || _R[id].inviewport===undefined) _R[id].lastplayedvideos = [];		
	});
}

var ctfs = function(_) {
	return _==="t" || _===true || _==="true" ? true : _==="f" || _===false || _==="false" ? false : _;
}

// TRANSFER SHORTENED DATA VALUES
var readVideoDatas = function(_,type,slideid) {		
	_.audio = type==="audio";			
	var o = _.video===undefined ? [] : _.video.split(";"),
		r = {
			volume: _.audio ? 1 : "mute",	//volume
			pload:"auto", 	//preload
			ratio:"16:9",	//aspectratio
			loop:true,	//loop
			aplay:'true',	//autplay
			fcover:_.bgvideo===1 ? true : false,	//forcecover
			afs:true,		//allowFullscreen
			controls:false,	//videocontrol
			nse:true,		//nextslideatend
			npom:false,		//noposteronmobile
			opom:false,		//Only Poster on Mobile
			inline:true, 	//inline
			notonmobile:false, //disablevideoonmobile
			start:-1,		//videostartat
			end:-1,			//videoendat
			doverlay:"none", //dottedoverlay
			scop:false,		//showcoveronpause
			rwd:true,		//forcerewind
			speed:1, 		//speed / speed
			ploadwait:5,  	// Preload Wait
			stopAV:_.bgvideo===1 ? false : true, 	// Stop All Videos
			noInt:false, 	// Stop All Videos
			volcache : 75 // Basic Volume
		}
	for (var u in o) {
		if (!o.hasOwnProperty(u)) continue;
		var s = o[u].split(":");
		switch(s[0]) {
			case "v": r.volume = s[1];break;
			case "vd": r.volcache = s[1];break;
			case "p": r.pload = s[1];break;
			case "ar": r.ratio = s[1] + (s[2]!==undefined ? ":"+s[2] : "");break;
			case "ap": r.aplay = ctfs(s[1]);break;
			case "fc": r.fcover = ctfs(s[1]); break;
			case "afs": r.afs = ctfs(s[1]);break;
			case "vc": r.controls = s[1];break;
			case "nse": r.nse = ctfs(s[1]);break;
			case "npom": r.npom = ctfs(s[1]);break;
			case "opom": r.opom = ctfs(s[1]);break;
			case "t": r.vtype = s[1];break;
			case "inl": r.inline = ctfs(s[1]);break;
			case "nomo": r.notonmobile = ctfs(s[1]);break;
			case "sta": r.start = s[1]+ (s[2]!==undefined ? ":"+s[2] : "");break;
			case "end": r.end = s[1] + (s[2]!==undefined ? ":"+s[2] : "");break;
			case "do": r.doverlay = s[1];break;
			case "scop": r.scop = ctfs(s[1]);break;
			case "rwd": r.rwd = ctfs(s[1]);break;
			case "sp": r.speed = s[1];break;
			case "vw": r.ploadwait = parseInt(s[1],0) || 5;break;
			case "sav": r.stopAV = ctfs(s[1]);break;
			case "noint": r.noInt = ctfs(s[1]);break;
			case "l": r.loopcache = s[1]; r.loop = s[1]==="loop" || s[1]==="loopandnoslidestop" ? true : s[1]==="none" ? false : ctfs(s[1]);break;
			case "ptimer": r.pausetimer = ctfs(s[1]);break;
		}
	}

	if (_.bgvideo!==undefined) r.bgvideo = _.bgvideo;
	if (_.bgvideo!==undefined && (r.fcover === false || r.fcover==="false"))  r.doverlay = "none";
	if (r.noInt) r.controls = false;
	if (_.mp4!==undefined) r.mp4 = _.mp4;
	if (_.videomp4!==undefined) r.mp4 = _.videomp4;
	if (_.ytid!==undefined) r.ytid = _.ytid;
	if (_.ogv!==undefined) r.ogv = _.ogv;
	if (_.webm!==undefined) r.webm = _.webm;
	if (_.vimeoid!==undefined) r.vimeoid = _.vimeoid;
	if (_.vatr!==undefined) r.vatr = _.vatr;
	if (_.videoattributes!==undefined) r.vatr = _.videoattributes;
	if (_.poster!==undefined) r.poster = _.poster;

	r.slideid = slideid;

	r.aplay = r.aplay==="true" ? true : r.aplay;
	//r.aplay = _.audio==true ? false : r.aplay;
	if (r.bgvideo===1) r.volume="mute";

	r.ssec = getStartSec(r.start);
	r.esec = getStartSec(r.end);

	//INTRODUCING loop and pausetimer
	r.pausetimer = r.pausetimer===undefined ? r.loopcache!=="loopandnoslidestop" : r.pausetimer;
	r.inColumn = _._incolumn;
	r.audio = _.audio;

	if ((r.loop===true || r.loop==="true") && (r.nse===true || r.nse==="true")) r.loop = false;
	return r;
}

var addVidtoList = function(_nc,id) {
	_R[id].playingvideos = _R[id].playingvideos===undefined ? new Array() : _R[id].playingvideos;
	// STOP OTHER VIDEOS
	if (_R[id].videos[_nc[0].id].stopAV) {
		if (_R[id].playingvideos !== undefined && _R[id].playingvideos.length>0) {
			_R[id].lastplayedvideos = jQuery.extend(true,[],_R[id].playingvideos);
			for (var i in _R[id].playingvideos) if (_R[id].playingvideos.hasOwnProperty(i)) _R.stopVideo(_R[id].playingvideos[i],id);
		}
	}
	_R[id].playingvideos.push(_nc);
	_R[id].videoIsPlaying = _nc;
}

var remVidfromList = function(_nc,id) {
	if (_R[id]===undefined || _R[id]===undefined) return;
	if (_R[id].playingvideos != undefined && jQuery.inArray(_nc,_R[id].playingvideos)>=0)
		_R[id].playingvideos.splice(jQuery.inArray(_nc,_R[id].playingvideos),1);
}


})(jQuery);