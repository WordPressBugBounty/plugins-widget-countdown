var wpdevart_countdown_popup_object={
	iframe_src:"",
	defaults:{
		waiting_time:0,
		general_show_quantity:"one_time",
		general_show_popup_after:0,
		general_scrolling_content:"no",
		overlay_show_hide:1,
		overlay_fade_efect_time:600,
		remov_overlay_when_clicked:true,
		remov_overlay_esc_button:true,
		overlay_fade_efect:true,
		popup_position:5,
		popup_outside_margin:0,
		popup_border_width:0,
		popup_border_color:"#000000",
		popup_background_color:"#FFFFFF",
		popup_border_radius:"0",
		popup_fixed_position:true,
		popup_width:300,
		popup_height:300,
		popup_animation_time:500,
		popup_first_open_type:'open',
		popup_can_run:true,
		popup_animation_type:'fade',
		control_buttons_show_hide:'1',
		control_buttons_line_position:'0',
		control_buttons_line_height:'40',
		control_buttons_line_bg_color:'#000000',
		control_buttons_transparency:'60',
		control_buttons_transparency_hover:'100',
		control_buttons_close_position:'right',
		control_buttons_close_icon_style:'cross',
		control_buttons_close_icon_color:'#ffffff',
	},
	private:{
		popup_conteiner_max_width:320,popup_conteiner_max_height:320,
		window_width:320,window_height:320,
		margin:0,border:0,wordpress_admin_bar_height:0,
		conteiner_width:0,conteiner_height:0,
		popup_start_construct_time:0,control_buttons_height:0,
	},
	constant_defaults:{
		overlay_id:'wpdevart_countdown_overlay',
		popup_window_id:'wpdevart_countdown_main_window',
		popup_window_information_content_id:'wpdevart_countdown_information_content',
		popup_window_controll_buttons_id:'wpdevart_controll_buttons_line',
		popup_window_close_button_id:"close_img",
		iframe_id:'wpdevart_countdown_iframe',
	},
	_el:function(id){return document.getElementById(id);},
	_setStyle:function(el,props){if(!el)return;for(var k in props)el.style[k]=props[k];},

	create_popup:function(){
		var self=this;
		this.private.popup_start_construct_time=Date.now();
		var pw=document.createElement('div');
		pw.id=this.constant_defaults.popup_window_id;
		pw.style.display='none';
		document.body.insertBefore(pw,document.body.firstChild);
		this.set_popup_parametrs();
		setTimeout(function(){self.load_iframe_in_content();},parseInt(this.defaults.general_show_popup_after*1000));
	},
	load_iframe_in_content:function(){
		var self=this;
		var iframe=document.createElement('iframe');
		iframe.src=self.iframe_src;
		iframe.id=self.constant_defaults.iframe_id;
		iframe.width=200;iframe.height=200;
		iframe.setAttribute('scrolling',self.defaults.general_scrolling_content);
		iframe.style.cssText="width:500px;height:500px;";
		iframe.onload=function(){self.iframe_onload();};
		var pw=self._el(self.constant_defaults.popup_window_id);
		if(pw) pw.insertBefore(iframe,pw.firstChild);
	},
	iframe_onload:function(){
		if(this.defaults.control_buttons_show_hide) this.create_description_panel();
		this.show_popup();
	},
	show_popup:function(){
		if(this.defaults.overlay_show_hide) this.create_overlay();
		this.resize_popup();
		this.start_css_for_opening_popup(this._el(this.constant_defaults.popup_window_id));
		this.resize_popup();
	},
	set_popup_parametrs:function(){
		var pw=this._el(this.constant_defaults.popup_window_id);
		if(!pw)return;
		pw.style.position=this.defaults.popup_fixed_position?'fixed':'absolute';
		pw.style.borderRadius=this.defaults.popup_border_radius+'px';
		this.generete_border();
	},
	generete_border:function(){
		var pw=this._el(this.constant_defaults.popup_window_id);
		if(!pw)return;
		if(this.get_correct_border(this.defaults.popup_border_width))
			pw.style.border='solid '+this.defaults.popup_border_width+'px '+this.defaults.popup_border_color;
		else
			pw.style.border='none';
	},
	create_overlay:function(){
		var self=this;
		var ov=document.createElement('div');
		ov.id=this.constant_defaults.overlay_id;
		if(this.defaults.overlay_fade_efect){
			ov.style.opacity='0';
			ov.style.transition='opacity '+parseInt(this.defaults.overlay_fade_efect_time)+'ms';
			setTimeout(function(){ov.classList.add('wpdevart_opacity');},30);
		}else{
			ov.classList.add('wpdevart_opacity');
		}
		document.body.insertBefore(ov,document.body.firstChild);
		if(self.defaults.remov_overlay_when_clicked){
			ov.addEventListener('click',function(e){if(e.target===ov)self.remove_overlay();});
		}
		if(self.defaults.remov_overlay_esc_button){
			document.addEventListener('keyup',function(e){if(e.keyCode===27)self.remove_overlay();});
		}
	},
	create_description_panel:function(){
		var self=this;
		var btn=document.createElement('div');
		btn.id=this.constant_defaults.popup_window_controll_buttons_id;
		btn.style.height=this.defaults.control_buttons_line_height+'px';
		btn.style.backgroundColor=this.defaults.control_buttons_line_bg_color;
		var pw=this._el(this.constant_defaults.popup_window_id);
		if(!pw)return;
		var pos=parseInt(this.defaults.control_buttons_line_position);
		if(pos===0){btn.style.position='absolute';btn.style.zIndex='9999999';pw.insertBefore(btn,pw.firstChild);}
		else if(pos===1){btn.style.position='relative';pw.insertBefore(btn,pw.firstChild);}
		else if(pos===2){btn.style.position='absolute';btn.style.zIndex='9999999';btn.style.bottom='0px';pw.appendChild(btn);}
		else if(pos===3){btn.style.position='relative';pw.appendChild(btn);}
		this.create_controll_buttons();
	},
	create_controll_buttons:function(){
		var self=this;
		var svgs={
			cross:'<svg viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>',
			times:'<svg viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M17 7L7 17M7 7l10 10"/></svg>',
			circle_x:'<svg viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>'
		};
		var style=this.defaults.control_buttons_close_icon_style||'cross';
		var color=this.defaults.control_buttons_close_icon_color||'#ffffff';
		var btn=document.createElement('span');
		btn.id=this.constant_defaults.popup_window_close_button_id;
		btn.innerHTML=svgs[style]||svgs.cross;
		btn.style.cssText='display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;cursor:pointer;color:'+color+';opacity:0.8;transition:opacity 0.2s;float:'+this.defaults.control_buttons_close_position+';';
		var container=this._el(this.constant_defaults.popup_window_controll_buttons_id);
		if(container) container.appendChild(btn);
		btn.addEventListener('click',function(){self.remove_overlay();});
		btn.addEventListener('mouseenter',function(){this.style.opacity='1';});
		btn.addEventListener('mouseleave',function(){this.style.opacity='0.8';});
	},
	remove_popup:function(){
		var self=this;
		var pw=this._el(this.constant_defaults.popup_window_id);
		if(!pw)return;
		if(self.defaults.popup_animation_type!='disable'){
			self.end_css_for_opening_popup(pw);
			setTimeout(function(){if(pw.parentNode)pw.parentNode.removeChild(pw);},self.defaults.popup_animation_time);
		}else{
			if(pw.parentNode)pw.parentNode.removeChild(pw);
		}
	},
	remove_overlay:function(){
		var self=this;
		if(!this.defaults.overlay_show_hide){this.remove_popup();return;}
		var ov=this._el(this.constant_defaults.overlay_id);
		if(!ov||!ov.classList.contains('wpdevart_opacity'))return;
		ov.classList.remove('wpdevart_opacity');
		if(this.defaults.overlay_fade_efect){
			setTimeout(function(){if(ov.parentNode)ov.parentNode.removeChild(ov);},parseInt(self.defaults.overlay_fade_efect_time*2/3));
		}else{
			if(ov.parentNode)ov.parentNode.removeChild(ov);
		}
		this.remove_popup();
	},
	resize_popup:function(){
		this.generete_private_params();
		var cw=Math.min(this.private.popup_conteiner_max_width,this.defaults.popup_width);
		var ch=cw*(this.defaults.popup_height/this.defaults.popup_width);
		ch=Math.min(this.private.popup_conteiner_max_height,ch);
		cw=ch*(this.defaults.popup_width/this.defaults.popup_height);
		var pw=this._el(this.constant_defaults.popup_window_id);
		var ifr=this._el(this.constant_defaults.iframe_id);
		if(pw){pw.style.width=cw+'px';pw.style.height=ch+'px';}
		if(ifr){ifr.style.width=cw+'px';ifr.style.height=(ch-this.private.control_buttons_height)+'px';}
		this.private.conteiner_width=cw;
		this.private.conteiner_height=ch;
		this.generete_border();
		this.set_popup_position();
	},
	set_popup_position:function(){
		var pw=this._el(this.constant_defaults.popup_window_id);
		if(!pw)return;
		var s=0;
		if(!this.defaults.popup_fixed_position) s=this.get_top_scroled_pixel();
		var p=this.private,d=this.defaults;
		var positions={
			1:[p.margin, p.wordpress_admin_bar_height+p.margin+s],
			2:[(p.window_width-(p.margin+p.conteiner_width))/2, p.margin+p.wordpress_admin_bar_height+s],
			3:[p.window_width-(p.margin*2+p.conteiner_width), p.margin+p.wordpress_admin_bar_height+s],
			4:[p.margin, (p.window_height-p.wordpress_admin_bar_height-p.conteiner_height-p.border*2)/2+p.wordpress_admin_bar_height+s],
			5:[(p.window_width-(p.margin+p.conteiner_width+p.border*2))/2, (p.window_height-p.wordpress_admin_bar_height-p.conteiner_height-p.border*2)/2+p.wordpress_admin_bar_height+s],
			6:[p.window_width-(p.margin+p.conteiner_width+p.border*2), (p.window_height-p.wordpress_admin_bar_height-p.conteiner_height-p.border*2)/2+p.wordpress_admin_bar_height+s],
			7:[p.margin, p.window_height-p.margin-p.conteiner_height-p.border*2+s],
			8:[(p.window_width-(p.margin+p.conteiner_width))/2, p.window_height-p.margin-p.conteiner_height-p.border*2+s],
			9:[p.window_width-(p.margin+p.conteiner_width), p.window_height-p.margin-p.conteiner_height-p.border*2+s],
		};
		var pos=positions[d.popup_position]||positions[5];
		pw.style.left=pos[0]+'px';
		pw.style.top=pos[1]+'px';
	},
	start_css_for_opening_popup:function(el){
		if(!el)return;
		var self=this;
		el.style.webkitAnimationDuration=this.defaults.popup_animation_time+'ms';
		el.style.animationDuration=this.defaults.popup_animation_time+'ms';
		el.classList.add("wpdevart_countdown_"+this.defaults.popup_animation_type);
		el.style.display='block';
		setTimeout(function(){el.classList.remove("wpdevart_countdown_"+self.defaults.popup_animation_type);},this.defaults.popup_animation_time);
	},
	end_css_for_opening_popup:function(el){
		if(!el)return;
		el.style.webkitAnimationDuration=(this.defaults.popup_animation_time+200)+'ms';
		el.style.animationDuration=(this.defaults.popup_animation_time+200)+'ms';
		el.classList.add("wpdevart_countdown_"+this.defaults.popup_animation_type+'_remove');
	},
	get_correct_margin:function(d){
		if((this.private.window_width-d*2)<320||(this.private.window_height-d*2)<320||this.defaults.popup_position==5)d=0;
		return d;
	},
	get_correct_border:function(b){
		if((this.private.window_width-b*10)<320||(this.private.window_height-b*10)<320)b=0;
		return b;
	},
	get_top_scroled_pixel:function(){
		if(this.defaults.enabled_picture_full_screen)return 0;
		return window.pageYOffset||document.documentElement.scrollTop||0;
	},
	generete_private_params:function(){
		this.private.window_width=window.innerWidth;
		this.private.window_height=window.innerHeight;
		var ab=document.getElementById('wpadminbar');
		this.private.wordpress_admin_bar_height=ab?ab.offsetHeight:0;
		this.private.margin=this.get_correct_margin(this.defaults.popup_outside_margin);
		this.private.border=this.get_correct_border(this.defaults.popup_border_width);
		if(this.defaults.control_buttons_show_hide&&(this.defaults.control_buttons_line_position==1||this.defaults.control_buttons_line_position==3))
			this.private.control_buttons_height=parseInt(this.defaults.control_buttons_line_height);
		if([3,5,8].indexOf(this.defaults.popup_position)!=-1)
			this.private.popup_conteiner_max_width=this.private.window_width-this.private.border*2;
		else
			this.private.popup_conteiner_max_width=this.private.window_width-this.private.margin*2-this.private.border*2;
		if([4,5,6].indexOf(this.defaults.popup_position)!=-1)
			this.private.popup_conteiner_max_height=this.private.window_height-this.private.border*2-this.private.wordpress_admin_bar_height;
		else
			this.private.popup_conteiner_max_height=this.private.window_height-this.private.margin*2-this.private.border*2-this.private.wordpress_admin_bar_height;
	},
	conect_outside_variable:function(defaults,seted){
		var self=this;
		Object.keys(defaults).forEach(function(index){
			if(typeof seted[index]!=='undefined'){
				if(seted[index]==='true') seted[index]=true;
				if(seted[index]==='false') seted[index]=false;
				if(typeof seted[index]==='string'&&self.isnumeric(seted[index])) seted[index]=parseInt(seted[index]);
				defaults[index]=seted[index];
			}
		});
		return defaults;
	},
	isnumeric:function(x){return /(^\d+$)|(^\d+\.\d+$)/.test(x);},
	countdown_set_cookies:function(cname,cvalue,exdays){
		var d=new Date();d.setTime(d.getTime()+(exdays*864e5));
		document.cookie=cname+"="+cvalue+";expires="+d.toUTCString()+";path=/";
	},
	countdown_get_cookies:function(cname){
		var name=cname+"=";var ca=document.cookie.split(';');
		for(var i=0;i<ca.length;i++){var c=ca[i].trim();if(c.indexOf(name)===0)return c.substring(name.length);}
		return "";
	},
	start:function(){
		var self=this;
		self.defaults=this.conect_outside_variable(this.defaults,wpdevart_countdown_popup_params);
		if(self.defaults.general_show_quantity==="one_time"){
			if(!self.countdown_get_cookies('countdown_popup')){
				window.addEventListener('resize',function(){self.resize_popup();self.set_popup_position();});
				if(document.readyState==='loading')
					document.addEventListener('DOMContentLoaded',function(){self.generete_private_params();self.create_popup();});
				else{self.generete_private_params();self.create_popup();}
				self.countdown_set_cookies('countdown_popup','countdown_popup',2);
			}
		}else{
			window.addEventListener('resize',function(){self.resize_popup();self.set_popup_position();});
			if(document.readyState==='loading')
				document.addEventListener('DOMContentLoaded',function(){self.generete_private_params();self.create_popup();});
			else{self.generete_private_params();self.create_popup();}
		}
	}
};
