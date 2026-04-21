/*standart countdown protytype_script*/
(function(){
	/* ---------------------------------------------------------------
	   Helper utilities
	--------------------------------------------------------------- */
	function wpda_ev_cookie(name,val,days){
		if(typeof val==="undefined"){
			var m=document.cookie.match(new RegExp('(?:^|; )'+name+'=([^;]*)'));
			return m?m[1]:'';
		}
		var d=new Date();d.setTime(d.getTime()+days*864e5);
		document.cookie=name+'='+val+';expires='+d.toUTCString()+';path=/';
	}
	function wpda_ev_calc_daily_seconds(timeStr){
		var parts=timeStr.split(':');
		var h=parseInt(parts[0])||23,m=parseInt(parts[1])||59;
		var now=new Date();
		var target=new Date(now.getFullYear(),now.getMonth(),now.getDate(),h,m,0);
		var diff=Math.floor((target-now)/1000);
		if(diff<=0) diff+=86400;
		return diff;
	}
	function wpda_ev_save(key,val){
		localStorage.setItem(key,val.toString());
		wpda_ev_cookie(key,val.toString(),365);
	}
	function wpda_cache_proof(options){
		if(!options.end_timestamp || options.is_evergreen) return;
		var now=Math.floor(Date.now()/1000);
		options.seconds_left=Math.max(0,options.end_timestamp-now);
		options.timer_start_time=options.start_timestamp-now;
		if(options.repeat_points && options.repeat_points.length>0 && options.server_time){
			var drift=now-options.server_time;
			for(var i=0;i<options.repeat_points.length;i++){
				options.repeat_points[i]=Math.max(0,options.repeat_points[i]-drift);
			}
		}
	}
	function wpda_evergreen_init(options){
		if(!options.is_evergreen) return;
		if(options.evergreen_expire_mode==="daily_time"){
			options.seconds_left=wpda_ev_calc_daily_seconds(options.evergreen_daily_expire_time);
			options.timer_start_time=0;
			return;
		}
		var key='wpda_ev_'+options.timer_id;
		var now=Math.floor(Date.now()/1000);
		var stored=localStorage.getItem(key)||wpda_ev_cookie(key);
		if(stored){
			stored=parseInt(stored);
			var elapsed=now-stored;
			var remaining=options.evergreen_duration-elapsed;
			if(remaining<=0){
				if(options.evergreen_restart==="immediate"){
					wpda_ev_save(key,now);
					options.seconds_left=options.evergreen_duration;
				}else if(options.evergreen_restart==="delay"){
					var expiredAt=stored+options.evergreen_duration;
					var waitEnd=expiredAt+options.evergreen_restart_delay;
					if(now>=waitEnd){
						wpda_ev_save(key,now);
						options.seconds_left=options.evergreen_duration;
					}else{
						options.seconds_left=0;
					}
				}else{
					options.seconds_left=0;
				}
			}else{
				options.seconds_left=remaining;
			}
		}else{
			wpda_ev_save(key,now);
			options.seconds_left=options.evergreen_duration;
		}
		options.timer_start_time=0;
	}

	/* ---------------------------------------------------------------
	   Wall-clock resync — fired on bfcache restore, tab refocus and
	   visibilitychange. Each view registers a callback that recomputes
	   its internal seconds_left from the wall clock and re-renders,
	   so intervals that paused or throttled in the background jump
	   back to the correct real time instead of drifting.
	--------------------------------------------------------------- */
	var wpda_cd_resync_handlers=[];
	function wpda_cd_register_resync(fn){
		wpda_cd_resync_handlers.push(fn);
	}
	function wpda_cd_trigger_resync(){
		for(var i=0;i<wpda_cd_resync_handlers.length;i++){
			try{ wpda_cd_resync_handlers[i](); }catch(e){}
		}
	}
	function wpda_cd_install_listeners(){
		if(wpda_cd_install_listeners._done) return;
		wpda_cd_install_listeners._done=true;
		document.addEventListener('visibilitychange',function(){
			if(document.visibilityState==='visible') wpda_cd_trigger_resync();
		});
		// pageshow with persisted=true = bfcache restore
		window.addEventListener('pageshow',function(e){
			if(e && e.persisted) wpda_cd_trigger_resync();
		});
		window.addEventListener('focus',wpda_cd_trigger_resync);
	}

	/* ---------------------------------------------------------------
	   Vanilla helpers
	--------------------------------------------------------------- */
	function _scrollTop(){
		return window.pageYOffset||document.documentElement.scrollTop||0;
	}
	function _winHeight(){
		return window.innerHeight||document.documentElement.clientHeight||0;
	}
	function _offset(el){
		var rect=el.getBoundingClientRect();
		var scrollTop=_scrollTop();
		return {top:rect.top+scrollTop};
	}
	function _getComputedStyle(el,prop){
		return window.getComputedStyle(el).getPropertyValue(prop);
	}
	function _fadeOut(el,ms){
		el.style.transition='opacity '+(ms/1000)+'s';
		el.style.opacity='0';
		setTimeout(function(){el.style.display='none';},ms);
	}
	function _fadeIn(el,ms){
		el.style.display='';
		el.style.opacity='0';
		el.style.transition='opacity '+(ms/1000)+'s';
		setTimeout(function(){el.style.opacity='1';},10);
	}
	function _forEachObj(obj,fn){
		var keys=Object.keys(obj);
		for(var i=0;i<keys.length;i++){
			fn(keys[i],obj[keys[i]]);
		}
	}

	/* ---------------------------------------------------------------
	   Hide parent popup/sticky bar on countdown expire
	--------------------------------------------------------------- */
	function wpda_hide_parent_on_expire(el){
		// Sticky bar
		var stickyBar=el.closest('.wpda_sticky_bar');
		if(stickyBar&&stickyBar.getAttribute('data-hide-on-expire')==='yes'){
			_fadeOut(stickyBar,400);
			return;
		}
		// Popup — countdown runs inside iframe, popup object is in parent window
		try{
			var p=window.parent;
			if(p&&p!==window&&typeof p.wpdevart_countdown_popup_object!=='undefined'){
				var obj=p.wpdevart_countdown_popup_object;
				if(obj.defaults&&obj.defaults.general_hide_after_expire==='yes'){
					obj.remove_overlay();
				}
			}
		}catch(e){}
	}

	/* ---------------------------------------------------------------
	   Animation helpers — global so they can be called from all views
	--------------------------------------------------------------- */
	function wpdevart_countdown_animated_element(element,effect,effect_time){
		if(typeof effect_time==="undefined") effect_time=0;
		window.addEventListener('scroll',animated_el);
		animated_el();
		setTimeout(function(){animated_el();},100);
		function animated_el(){
			if(!element.classList.contains('animated') && isInView()){
				setTimeout(function(){
					element.style.visibility='visible';
					element.classList.add('animated');
					element.classList.add(effect);
				},effect_time);
			}
		}
		function isInView(){
			var docViewTop=_scrollTop();
			var docViewBottom=docViewTop+_winHeight();
			var elemTop=_offset(element).top;
			var elemBottom=elemTop+parseInt(_getComputedStyle(element,'height'));
			return (
				((docViewTop<=elemTop+5)&&(elemTop-5<=docViewBottom)) ||
				((docViewTop<=elemBottom+5)&&(elemBottom-5<=docViewBottom)) ||
				(docViewTop===0&&docViewBottom===0) ||
				_winHeight()===0
			);
		}
	}
	window.wpdevart_countdown_animated_element=wpdevart_countdown_animated_element;

	function wpdevart_countdown_animated_element_children(element,effect,between_interval){
		if(typeof between_interval==="undefined") between_interval=0;
		window.addEventListener('scroll',animated_el);
		animated_el();
		function animated_el(){
			var local_time=0;
			if(!element.classList.contains('animated') && isInView()){
				var children=element.children;
				for(var i=0;i<children.length;i++){
					(function(child,delay){
						setTimeout(function(){
							child.style.visibility='visible';
							child.classList.add('animated');
							child.classList.add(effect);
						},delay);
					})(children[i],local_time);
					local_time+=between_interval;
				}
			}
		}
		function isInView(){
			var docViewTop=_scrollTop();
			var docViewBottom=docViewTop+_winHeight();
			var elemTop=_offset(element).top;
			var elemBottom=elemTop+parseInt(_getComputedStyle(element,'height'));
			return (
				((docViewTop<=elemTop+5)&&(elemTop-5<=docViewBottom)) ||
				((docViewTop<=elemBottom+5)&&(elemBottom-5<=docViewBottom)) ||
				(docViewTop===0&&docViewBottom===0) ||
				_winHeight()===0
			);
		}
	}

	/* Kept for BC — old PHP may still call this jQuery plugin form */
	function wpdevart_countdown_animation(element,effect,effect_time){
		wpdevart_countdown_animated_element(element,effect,effect_time);
	}
	window.wpdevart_countdown_animation=wpdevart_countdown_animation;

	/* ---------------------------------------------------------------
	   equals_url — shared helper
	--------------------------------------------------------------- */
	function equals_url(url1,url2){
		function strip(u){
			return u.replace(/^https?:\/\/(www\.)?/,'');
		}
		url1=strip(url1); url2=strip(url2);
		if(url1.slice(-1)!=='/') url1+='/';
		if(url2.slice(-1)!=='/') url2+='/';
		return url1===url2;
	}

	/* ---------------------------------------------------------------
	   STANDARD countdown  (global function, replaces $.fn.wpdevart_countdown_standart)
	--------------------------------------------------------------- */
	function wpdevart_countdown_standart(elementId,options){
		var element=document.getElementById(elementId);
		if(!element) return;
		wpda_cache_proof(options);
		wpda_evergreen_init(options);
		var seconds_left=options.seconds_left;
		var timer_countup_seconds=-options.timer_start_time;
		var array_of_dates=['week','day','hour','minut','second'];
		var interval_ids=[];
		/* calculating Date */
		var kaificents_by_seconds={
			week:604800,
			day:86400,
			hour:3600,
			minut:60,
			second:1,
		};
		var loc_kaificents=get_kaificents();
		var kaificents=loc_kaificents[0];
		var count_of_display_dates=loc_kaificents[1];
		var timer_exsist=false;
		delete loc_kaificents;
		/*end of Calculating Dates*/
		create_html();
		// if repeating exist then create the set time for it
		if(options.repeat_points.length>0){
			for(var i=0;i<options.repeat_points.length;i++){
				repeat_settimeout(i);
			}
		}
		if(options.coundown_type=="countup"||options.coundown_type=="evergreen_countup"){
			if(options.timer_start_time>0){
				before_timer();
				seconds_left=seconds_left-options.timer_start_time;
				timer_countup_seconds=0;
				setTimeout(function(){start_counting_up();},options.timer_start_time*1000+10);
			}else{
				setTimeout(function(){start_counting_up();},10);
			}
		}else{
			if(options.timer_start_time>0){
				before_timer();
				seconds_left=seconds_left-options.timer_start_time;
				setTimeout(function(){start_counting_down();},options.timer_start_time*1000+10);
			}else{
				setTimeout(function(){start_counting_down();},10);
			}
		}
		// create html
		function create_html(){
			if(options.top_html_text!=''){
				element.innerHTML+='<div class="wpdevart_top_html">'+options.top_html_text+'</div>';
			}
			for(var i=0;i<array_of_dates.length;i++){
				if(typeof options.display_days[array_of_dates[i]]==="undefined"){
					continue;
				}else{
					element.innerHTML+='<div class="wpdevart_countdown_element '+array_of_dates[i]+'_block_element"><span class="time_left_pro '+array_of_dates[i]+'_left"></span><span class="time_text '+array_of_dates[i]+'_text"></span></div>';
				}
			}
			if(options.bottom_html_text!=''){
				element.innerHTML+='<div class="wpdevart_bottom_html">'+options.bottom_html_text+'</div>';
			}
			set_html_text(options.display_days);
			/*Set animation effect*/
			if(options.gorup_animation=="group"){
				wpdevart_countdown_animated_element(element,options.effect,0);
			}else{
				wpdevart_countdown_animated_element_children(element,options.effect,400);
			}
			timer_exsist=true;
		}
		// remove html element
		function remove_html(){
			element.innerHTML="";
			timer_exsist=false;
		}
		// start counting down — ticks read wall clock each second so views stay in sync
		function start_counting_down(){
			if(!timer_exsist){
				remove_html();
				create_html();
			}
			var time_object=calculate_time(seconds_left);
			change_html_time(time_object);
			interval_ids.push(setInterval(function(){
				wpda_cache_proof(options);
				wpda_evergreen_init(options);
				seconds_left=options.seconds_left;
				if(seconds_left<=0){after_timer();return;}
				time_object=calculate_time(seconds_left);
				change_html_time(time_object);
			},1000));
			if(seconds_left<=0){
				after_timer();
			}
		}
		// start counting up — ticks read wall clock each second
		function start_counting_up(){
			if(timer_countup_seconds>=0&&seconds_left>0){
				if(!timer_exsist){
					remove_html();
					create_html();
				}
				var time_object=calculate_time(timer_countup_seconds);
				change_html_time(time_object);
				interval_ids.push(setInterval(function(){
					wpda_cache_proof(options);
					wpda_evergreen_init(options);
					seconds_left=options.seconds_left;
					timer_countup_seconds=-options.timer_start_time;
					if(seconds_left<=0){after_timer();return;}
					time_object=calculate_time(timer_countup_seconds);
					change_html_time(time_object);
				},1000));
			}else{
				after_timer();
			}
		}
		function repeat_settimeout(index){
			setTimeout(function(){
				if(index===0){
					seconds_left=options.repeat_seconds_start;
					timer_countup_seconds=options.repeat_seconds_mid-options.repeat_seconds_start;
				}else if(index===options.repeat_points.length-1){
					seconds_left=options.repeat_seconds_mid;
					timer_countup_seconds=0;
				}else{
					seconds_left=options.repeat_seconds_end;
					timer_countup_seconds=options.repeat_seconds_mid-options.repeat_seconds_end;
				}
				if(!timer_exsist){
					if(options.coundown_type=="countup"||options.coundown_type=="evergreen_countup"){
						start_counting_up();
					}else{
						start_counting_down();
					}
				}
			},(options.repeat_points[index])*1000);
		}
		// set text for html
		function set_html_text(text_of_html){
			_forEachObj(text_of_html,function(index){
				var els=element.querySelectorAll('.'+index+'_text');
				for(var i=0;i<els.length;i++) els[i].innerHTML=options.display_days_texts[index];
			});
		}
		// change time
		function change_html_time(time_object){
			_forEachObj(time_object,function(index,value){
				var els=element.querySelectorAll('.'+index+'_left');
				for(var i=0;i<els.length;i++) els[i].innerHTML=value;
			});
		}
		/* get day kaificents*/
		function get_kaificents(){
			var kaificent={
				week:1000,
				day:7,
				hour:24,
				minut:60,
				second:60,
			};
			var k=5;
			if(typeof options.display_days.week==="undefined"){
				kaificent["day"]=kaificent["week"]*7;
				delete kaificent.week; k--;
			}
			if(typeof options.display_days.day==="undefined"){
				kaificent["hour"]=kaificent["day"]*24;
				delete kaificent.day; k--;
			}
			if(typeof options.display_days.hour==="undefined"){
				kaificent["minut"]=kaificent["hour"]*60;
				delete kaificent.hour; k--;
			}
			if(typeof options.display_days.minut==="undefined"){
				kaificent["second"]=kaificent["minut"]*60;
				delete kaificent.minut; k--;
			}
			if(typeof options.display_days.second==="undefined"){
				delete kaificent.second; k--;
			}
			return [kaificent,k];
		}
		/* Calculating time */
		function calculate_time(seconds){
			var time_object={};
			var loc_seconds_left=seconds;
			var k=0;
			_forEachObj(kaificents,function(index,value){
				k++;
				if(k===count_of_display_dates&&loc_seconds_left!==0){
					time_object[index]=Math.min(Math.ceil(loc_seconds_left/kaificents_by_seconds[index]),value);
					loc_seconds_left=loc_seconds_left-time_object[index]*kaificents_by_seconds[index];
				}else{
					time_object[index]=Math.min(Math.floor(loc_seconds_left/kaificents_by_seconds[index]),value);
					loc_seconds_left=loc_seconds_left-time_object[index]*kaificents_by_seconds[index];
				}
			});
			return time_object;
		}
		/* after Countdown and functions */
		function after_timer(){
			switch(options.after_countdown_end_type){
				case "hide":
					hide_countdown();
					break;
				case "text":
					show_countdown_text();
					break;
				case "redirect":
					redirect_countdown();
					break;
				case "button":
					clear_intervals();
					remove_html();
					var btn='<a href="'+options.after_countdown_button_url+'" class="wpda_countdown_expired_btn" style="display:inline-block;padding:12px 28px;background:#1d2327;color:#fff;text-decoration:none;border-radius:6px;font-size:15px;font-weight:500;cursor:pointer;transition:opacity 0.2s;">'+options.after_countdown_button_text+'</a>';
					element.innerHTML='<div style="text-align:center;padding:15px 0;">'+btn+'</div>';
					break;
				case "hide_content":
					if(options.after_countdown_css_selector){
						var hideEl=document.querySelector(options.after_countdown_css_selector);
						if(hideEl) _fadeOut(hideEl,400);
					}
					hide_countdown();
					break;
				case "show_content":
					if(options.after_countdown_css_selector){
						var showEl=document.querySelector(options.after_countdown_css_selector);
						if(showEl) _fadeIn(showEl,400);
					}
					hide_countdown();
					break;
				default:
					hide_countdown();
					break;
			}
			wpda_hide_parent_on_expire(element);
		}
		function before_timer(){
			switch(options.before_countup_start_type){
				case "none":
					hide_countdown();
					break;
				case "text":
					show_countdown_before_text();
					break;
				case "redirect":
					if(options.before_countup_redirect&&options.before_countup_redirect!="") window.location.href=options.before_countup_redirect;
					break;
			}
		}
		function hide_countdown(){
			clear_intervals();
			remove_html();
		}
		function show_countdown_text(){
			clear_intervals();
			remove_html();
			element.innerHTML=options.after_countdown_text;
		}
		function redirect_countdown(){
			clear_intervals();
			if(equals_url(window.location.href,options.after_countdown_redirect)||options.after_countdown_redirect==""||options.after_countdown_redirect===window.location.href){
				hide_countdown();
			}else{
				window.location=options.after_countdown_redirect;
			}
		}
		function show_countdown_before_text(){
			timer_exsist=false;
			element.innerHTML=options.before_countup_text;
		}
		function clear_intervals(){
			var i=interval_ids.length;
			while(i>0){
				i--;
				clearInterval(interval_ids[i]);
				interval_ids.pop();
			}
		}
		if(parseInt(options.inline)){
			// rAF throttle — fires once per frame max during resize, much smoother than raw event
			var _raf_pending=false;
			var on_resize=function(){
				if(_raf_pending) return;
				_raf_pending=true;
				(window.requestAnimationFrame||function(cb){setTimeout(cb,16);})(function(){
					display_line();
					_raf_pending=false;
				});
			};
			if(document.readyState==='loading'){
				document.addEventListener('DOMContentLoaded',function(){
					initial_start_parametrs();
					display_line();
				});
			}else{
				initial_start_parametrs();
				display_line();
			}
			window.addEventListener('resize',on_resize);
			window.addEventListener('load',display_line);
		}
		function display_line(){
			// Use element.clientWidth (actual inner width) — parent.offsetWidth includes
			// parent padding and over-reports. Fallback if element itself is zero-width.
			var main_width=element.clientWidth;
			if(main_width<=0){
				var parentEl=element.parentElement;
				if(parentEl) main_width=parentEl.clientWidth;
			}
			if(main_width<=0) return;
			// Reserve 6px breathing room — prevents subpixel oscillation / wrap-before-scale race on resize
			var safe_width=Math.max(50,main_width-6);
			var sumary_inside_width=0;
			var elems=element.querySelectorAll(".wpdevart_countdown_element");
			if(!elems.length) return;
			for(var i=0;i<elems.length;i++){
				sumary_inside_width+=parseInt(elems[i].getAttribute('date-width'))+parseInt(elems[i].getAttribute('date-distance'));
			}
			// Last element's margin-right doesn't compete for space
			var lastGap=parseInt(elems[elems.length-1].getAttribute('date-distance'))||0;
			sumary_inside_width-=lastGap;
			if(sumary_inside_width<=0) return;
			var kaificent=sumary_inside_width/safe_width;
			// Always apply scale — when container grows back, kaificent<1 → scale=1 restores originals.
			// Otherwise, once scaled down, elements would be stuck at the smaller size forever.
			var scale=kaificent>=1?(1/kaificent):1;
			// Join "2 0 0 0" + "px" → "2px 0px 0px 0px" — scales each token and rebuilds proper CSS shorthand.
			var scaleCompound=function(str){
				var parts=(str||'').split(' ');
				for(var k=0;k<parts.length;k++) parts[k]=Math.floor((parseInt(parts[k])||0)*scale)+'px';
				return parts.join(' ');
			};
			for(var i=0;i<elems.length;i++){
				var el=elems[i];
				var w=Math.floor(parseInt(el.getAttribute('date-width'))*scale);
				var g=Math.floor(parseInt(el.getAttribute('date-distance'))*scale);
				el.style.width=w+'px';
				el.style.minWidth=w+'px';
				el.style.marginRight=g+'px';
				var time_left_pro=el.querySelector(".time_left_pro");
				var time_text=el.querySelector(".time_text");
				if(time_left_pro){
					time_left_pro.style.fontSize=(parseInt(time_left_pro.getAttribute("date-font"))*scale)+'px';
					time_left_pro.style.margin=scaleCompound(time_left_pro.getAttribute("date-margin"));
					time_left_pro.style.padding=scaleCompound(time_left_pro.getAttribute("date-padding"));
				}
				if(time_text){
					time_text.style.fontSize=(parseInt(time_text.getAttribute("date-font"))*scale)+'px';
					time_text.style.margin=scaleCompound(time_text.getAttribute("date-margin"));
					time_text.style.padding=scaleCompound(time_text.getAttribute("date-padding"));
				}
			}
		}
		function initial_start_parametrs(){
			var elems=element.querySelectorAll(".wpdevart_countdown_element");
			for(var i=0;i<elems.length;i++){
				var el=elems[i];
				el.setAttribute("date-width",el.offsetWidth);
				el.setAttribute("date-distance",window.getComputedStyle(el).marginRight);
				var time_left_pro=el.querySelector(".time_left_pro");
				var time_text=el.querySelector(".time_text");
				time_left_pro.setAttribute("date-font",window.getComputedStyle(time_left_pro).fontSize);
				time_text.setAttribute("date-font",window.getComputedStyle(time_text).fontSize);
				time_left_pro.setAttribute("date-margin",window.getComputedStyle(time_left_pro).margin);
				time_text.setAttribute("date-margin",window.getComputedStyle(time_text).margin);
				time_left_pro.setAttribute("date-padding",window.getComputedStyle(time_left_pro).padding);
				time_text.setAttribute("date-padding",window.getComputedStyle(time_text).padding);
			}
		}
		/* Wall-clock resync — recompute seconds_left from end_timestamp when
		   the tab becomes visible again (background throttling / bfcache). */
		wpda_cd_register_resync(function(){
			if(!timer_exsist) return;
			wpda_cache_proof(options);
			wpda_evergreen_init(options);
			seconds_left=options.seconds_left;
			timer_countup_seconds=-options.timer_start_time;
			if(seconds_left<=0){ after_timer(); return; }
			var isCountup=(options.coundown_type=="countup"||options.coundown_type=="evergreen_countup");
			change_html_time(calculate_time(isCountup?timer_countup_seconds:seconds_left));
		});
		wpda_cd_install_listeners();
	}
	window.wpdevart_countdown_standart=wpdevart_countdown_standart;


	/* ---------------------------------------------------------------
	   Ajax compatibility — re-init timers loaded dynamically
	   (Elementor popups, lazy load, AJAX)
	--------------------------------------------------------------- */
	if(typeof MutationObserver!=='undefined'){
		var wpda_cd_observer=new MutationObserver(function(mutations){
			mutations.forEach(function(mutation){
				if(!mutation.addedNodes||!mutation.addedNodes.length) return;
				mutation.addedNodes.forEach(function(node){
					if(node.nodeType!==1) return;
					var scripts=node.querySelectorAll?node.querySelectorAll('script'):[];
					// also check the node itself
					var allScripts=[];
					if(node.tagName==='SCRIPT') allScripts.push(node);
					for(var i=0;i<scripts.length;i++) allScripts.push(scripts[i]);
					allScripts.forEach(function(s){
						var txt=s.textContent||'';
						if(txt.indexOf('wpdevart_countdown_')!==-1){
							try{ (new Function(txt))(); }catch(e){}
						}
					});
				});
			});
		});
		if(document.readyState==='loading'){
			document.addEventListener('DOMContentLoaded',function(){
				wpda_cd_observer.observe(document.body,{childList:true,subtree:true});
			});
		}else{
			wpda_cd_observer.observe(document.body,{childList:true,subtree:true});
		}
	}
	// Manual re-init event (vanilla)
	document.addEventListener('wpda_countdown_reinit',function(){
		var scripts=document.querySelectorAll('script');
		scripts.forEach(function(s){
			var txt=s.textContent||'';
			if(txt.indexOf('wpdevart_countdown_')!==-1&&!s.dataset.wpdaInited){
				s.dataset.wpdaInited='1';
				try{ (new Function(txt))(); }catch(e){}
			}
		});
	});
})();
