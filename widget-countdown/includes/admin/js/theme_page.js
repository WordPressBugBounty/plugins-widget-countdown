function submitbutton(value){
	jQuery("#adminForm").attr("action",jQuery("#adminForm").attr("action")+"&task="+value);
	jQuery("#adminForm").submit();
}
wpda_countdown_theme_class={
	start_tab_id:"countdown_theme_general",
	list_of_fields_po_mo:["text_for_weeks","text_for_day","text_for_hour","text_for_minute","text_for_second"],
	type_tab_map:{
		"standart":"Standart_countdown",
		"vertical":"vertical_countdown",
		"circle":"circle_countdown",
		"flip":"countdown_flip_settings"
	},
	start:function(){
		var self=this;
		jQuery(document).ready(function(){
			self.conect_tab_activate_functionality();
			self.activete_tab(self.start_tab_id);
			self.set_diplay_none_and_block();
			self.setup_type_tabs();
		})
	},
	setup_type_tabs:function(){
		var self=this;
		function updateTabs(){
			var selected=jQuery("#countdown_type").val()||"standart";
			jQuery.each(self.type_tab_map,function(type,tabKey){
				var tab=jQuery("#"+tabKey+"_tab");
				if(type===selected){
					tab.addClass('wpda_type_tab_visible');
				}else{
					tab.removeClass('wpda_type_tab_visible');
					if(tab.hasClass('active')){
						self.activete_tab(self.start_tab_id);
					}
				}
			});
		}
		jQuery("#countdown_type").on('change',updateTabs);
		updateTabs();
	},
	conect_tab_activate_functionality:function(){
		var self=this;
		jQuery(".wpda_theme_link_tabs li").click(function(){
			if(jQuery(this).is(':hidden')) return;
			self.activete_tab(jQuery(this).attr('id').replace("_tab",""));
		});
	},
	set_diplay_none_and_block:function(){
		var self=this;
		jQuery("#countdown_text_type").change(function(){
			if(jQuery(this).val()=="standart"){
				for(i=0;i<self.list_of_fields_po_mo.length;i++){
					jQuery("#"+self.list_of_fields_po_mo[i]).parent().parent().show();
				}
			}else{
				for(i=0;i<self.list_of_fields_po_mo.length;i++){
					jQuery("#"+self.list_of_fields_po_mo[i]).parent().parent().hide();
				}
			}
		})
		if(jQuery("#countdown_text_type").val()=="standart"){
			for(i=0;i<self.list_of_fields_po_mo.length;i++){
				jQuery("#"+self.list_of_fields_po_mo[i]).parent().parent().show();
			}
		}else{
			for(i=0;i<self.list_of_fields_po_mo.length;i++){
				jQuery("#"+self.list_of_fields_po_mo[i]).parent().parent().hide();
			}
		}
	},
	activete_tab:function(tab_id){
		jQuery(".wpda_theme_link_tabs li,.all_options_panel table tr").removeClass('active');
		jQuery("#"+tab_id+"_tab").addClass('active');
		jQuery((".all_options_panel table tr" + "."+tab_id)).addClass('active');
	}
}

wpda_countdown_theme_class.start();

/* ---------------------------------------------------------------
   Live theme preview
--------------------------------------------------------------- */
(function(){
var preview,btn;
var sampleTime={week:'02',day:'14',hour:'08',minut:'35',second:'42'};
var defaultTexts={week:'Weeks',day:'Days',hour:'Hours',minut:'Minutes',second:'Seconds'};
var textFieldMap={week:'text_for_weeks',day:'text_for_day',hour:'text_for_hour',minut:'text_for_minute',second:'text_for_second'};
var fields=['week','day','hour','minut','second'];

function gv(id){var el=document.getElementById(id);if(!el) el=document.querySelector('[name="'+id+'"]');return el?el.value:'';}
function gn(id){var v=parseFloat(gv(id));return isNaN(v)?0:v;}
function gc(id){var el=document.getElementById(id);return el?el.value||'':'';}
function gpv(base,side){
	var v=gn(base+'_'+side+'_id');
	if(v===0){
		var el=document.querySelector('[name="'+base+'['+side+']"]');
		if(el) v=parseFloat(el.value)||0;
	}
	return v;
}
function gp(base){
	return {top:gpv(base,'top'),right:gpv(base,'right'),bottom:gpv(base,'bottom'),left:gpv(base,'left')};
}
function pad4(p){return p.top+'px '+p.right+'px '+p.bottom+'px '+p.left+'px';}

function getCheckedFields(){
	var checked={};
	document.querySelectorAll('[name^="countdown_date_display"]').forEach(function(cb){
		if(cb.checked) checked[cb.value]=cb.value;
	});
	if(Object.keys(checked).length===0) checked={day:'day',hour:'hour',minut:'minut',second:'second'};
	return checked;
}

function getTexts(){
	var texts={};
	var mode=gv('countdown_text_type')||'po_mo';
	for(var i=0;i<fields.length;i++){
		var f=fields[i];
		if(mode==='standart'){
			texts[f]=gv(textFieldMap[f])||defaultTexts[f];
		}else{
			texts[f]=defaultTexts[f];
		}
	}
	return texts;
}

function getGlobalStyle(){
	var w=gv('countdown_global_width')||'100';
	var u=gv('countdown_global_width_metrick')||'%';
	var pos=gv('countdown_horizontal_position')||'center';
	return 'max-width:100%;width:'+w+u+';text-align:'+pos+';';
}

function renderStandard(){
	var display=getCheckedFields();var texts=getTexts();
	var w=gn('countdown_standart_elements_width')||80;
	var dist=gn('countdown_standart_elements_distance')||5;
	var tbg=gc('countdown_standart_time_bg_color')||'#fff';
	var tc=gc('countdown_standart_time_color')||'#000';
	var tfs=gn('countdown_standart_time_font_size')||21;
	var tff=gv('countdown_standart_time_font_famely')||'Arial,sans-serif';
	var tp=gp('countdown_standart_time_padding');
	var tm=gp('countdown_standart_time_margin');
	var tbw=gn('countdown_standart_time_border_width');
	var tbr=gn('countdown_standart_time_border_radius');
	var tbc=gc('countdown_standart_time_border_color')||'#000';
	var ttbg=gc('countdown_standart_time_text_bg_color')||'#000';
	var ttc=gc('countdown_standart_time_text_color')||'#000';
	var ttfs=gn('countdown_standart_time_text_font_size')||11;
	var ttff=gv('countdown_standart_time_text_font_famely')||'Arial,sans-serif';
	var ttp=gp('countdown_standart_time_text_padding');
	var ttm=gp('countdown_standart_time_text_margin');
	var ttbw=gn('countdown_standart_time_text_border_width');
	var ttbr=gn('countdown_standart_time_text_border_radius');
	var ttbc=gc('countdown_standart_time_text_border_color')||'#000';
	var html='<div style="'+getGlobalStyle()+'">';
	for(var i=0;i<fields.length;i++){
		var f=fields[i];
		if(!display[f]) continue;
		html+='<div style="display:inline-block;min-width:'+w+'px;margin-right:'+dist+'px;text-align:center;">'
			+'<span style="display:block;background:'+tbg+';color:'+tc+';font-size:'+tfs+'px;font-family:'+tff
			+';border:'+tbw+'px solid '+tbc+';border-radius:'+tbr+'px;border-style:solid'
			+';padding:'+pad4(tp)+';margin:'+pad4(tm)+';">'+sampleTime[f]+'</span>'
			+'<span style="display:block;background:'+ttbg+';color:'+ttc+';font-size:'+ttfs+'px;font-family:'+ttff
			+';border:'+ttbw+'px solid '+ttbc+';border-radius:'+ttbr+'px;border-style:solid'
			+';padding:'+pad4(ttp)+';margin:'+pad4(ttm)+';">'+texts[f]+'</span>'
			+'</div>';
	}
	html+='</div>';
	return html;
}

function renderCircle(){
	var display=getCheckedFields();var texts=getTexts();
	var size=gn('countdown_circle_elements_width_height')||100;
	var gap=gn('countdown_circle_elements_distance')||10;
	var bgc=gc('countdown_circle_border_color_outside')||'#eee';
	var fgc=gc('countdown_circle_border_color_inside')||'#2271b1';
	var thick=(gn('countdown_circle_width_parcents')||10)/100;
	var linecap=gv('countdown_circle_type_of_rounding')||'round';
	var dir=gv('countdown_circle_border_direction')||'left';
	var circleBg=gc('countdown_circle_background_color')||'transparent';
	var circleBgOp=(gn('countdown_circle_background_color_opacity')||0)/100;
	var numFs=gn('countdown_circle_time_font_size')||18;
	var numC=gc('countdown_circle_time_color')||'#000';
	var numFf=gv('countdown_circle_time_font_famely')||'Arial,sans-serif';
	var lblFs=gn('countdown_circle_time_text_font_size')||11;
	var lblC=gc('countdown_circle_time_text_color')||'#666';
	var strokeW=Math.max(2,Math.round(size*thick));
	var r=(size-strokeW)/2;
	var c=2*Math.PI*r;
	var pct=dir==='right'?0.35:0.65;
	var bgRgba=hexToRgba(circleBg,circleBgOp);
	var html='<div style="'+getGlobalStyle()+'">';
	for(var i=0;i<fields.length;i++){
		var f=fields[i];
		if(!display[f]) continue;
		html+='<div style="display:inline-block;width:'+size+'px;height:'+size+'px;position:relative;margin-right:'+gap+'px;vertical-align:top;">'
			+'<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);border-radius:50%;width:calc(100% - 2px);height:calc(100% - 2px);background:'+bgRgba+';"></div>'
			+'<svg viewBox="0 0 '+size+' '+size+'" style="width:100%;height:100%;transform:rotate(-90deg);display:block;">'
			+'<circle cx="'+(size/2)+'" cy="'+(size/2)+'" r="'+r+'" fill="none" stroke="'+bgc+'" stroke-width="'+strokeW+'"/>'
			+'<circle cx="'+(size/2)+'" cy="'+(size/2)+'" r="'+r+'" fill="none" stroke="'+fgc+'" stroke-width="'+strokeW+'" stroke-linecap="'+linecap+'" stroke-dasharray="'+c+'" stroke-dashoffset="'+(c-(pct*c))+'"/>'
			+'</svg>'
			+'<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;line-height:1.2;">'
			+'<span style="font-size:'+numFs+'px;color:'+numC+';font-weight:700;font-family:'+numFf+';">'+sampleTime[f]+'</span>'
			+'<span style="font-size:'+lblFs+'px;color:'+lblC+';">'+texts[f]+'</span>'
			+'</div></div>';
	}
	html+='</div>';
	return html;
}

function renderFlip(){
	var display=getCheckedFields();var texts=getTexts();
	var bg=gc('countdown_flip_card_bg')||'#1d2327';
	var color=gc('countdown_flip_card_color')||'#fff';
	var label=gc('countdown_flip_label_color')||'#50575e';
	var w=gn('countdown_flip_card_width')||70;
	var h=gn('countdown_flip_card_height')||80;
	var fs=gn('countdown_flip_font_size')||36;
	var gap=gn('countdown_flip_gap')||12;
	var br=gn('countdown_flip_border_radius')||8;
	var ff=gv('countdown_flip_font_family')||'-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,monospace';
	var html='<div style="'+getGlobalStyle()+'"><div style="display:inline-flex;gap:'+gap+'px;align-items:flex-start;">';
	for(var i=0;i<fields.length;i++){
		var f=fields[i];
		if(!display[f]) continue;
		html+='<div style="display:flex;flex-direction:column;align-items:center;">'
			+'<div style="position:relative;width:'+w+'px;height:'+h+'px;border-radius:'+br+'px;overflow:hidden;font-size:'+fs+'px;font-weight:700;color:'+color+';font-family:'+ff+';">'
			+'<div style="position:absolute;top:0;left:0;width:100%;height:50%;overflow:hidden;background:'+bg+';border-radius:'+br+'px '+br+'px 0 0;"><div style="text-align:center;line-height:'+h+'px;">'+sampleTime[f]+'</div></div>'
			+'<div style="position:absolute;bottom:0;left:0;width:100%;height:50%;overflow:hidden;background:'+darken(bg,8)+';border-radius:0 0 '+br+'px '+br+'px;"><div style="text-align:center;line-height:'+h+'px;position:absolute;bottom:0;width:100%;">'+sampleTime[f]+'</div></div>'
			+'<div style="position:absolute;left:0;top:50%;width:100%;height:1px;background:rgba(0,0,0,0.4);z-index:2;"></div>'
			+'</div>'
			+'<div style="margin-top:6px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:'+label+';">'+texts[f]+'</div>'
			+'</div>';
	}
	html+='</div></div>';
	return html;
}

function renderVertical(){
	var display=getCheckedFields();var texts=getTexts();
	var dist=gn('countdown_vertical_elements_distance')||5;
	var bgc=gc('countdown_vertical_background_color')||'#333';
	var tc=gc('countdown_vertical_time_color')||'#fff';
	var tfs=gn('countdown_vertical_time_font_size')||20;
	var tff=gv('countdown_vertical_time_font_famely')||'Arial,sans-serif';
	var tbw=gn('countdown_vertical_time_border_width')||2;
	var tbc=gc('countdown_vertical_time_border_color')||'#000';
	var ttbg=gc('countdown_vertical_time_text_bg_color')||'#000';
	var ttc=gc('countdown_vertical_time_text_color')||'#fff';
	var ttfs=gn('countdown_vertical_time_text_font_size')||11;
	var ttff=gv('countdown_vertical_time_text_font_famely')||'Arial,sans-serif';
	var ttp=gp('countdown_vertical_time_text_padding');
	var ttm=gp('countdown_vertical_time_text_margin');
	var ttbw=gn('countdown_vertical_time_text_border_width')||0;
	var ttbr=gn('countdown_vertical_time_text_border_radius')||0;
	var ttbc=gc('countdown_vertical_time_text_border_color')||'#000';
	var html='<div style="'+getGlobalStyle()+'">';
	for(var i=0;i<fields.length;i++){
		var f=fields[i];
		if(!display[f]) continue;
		var digits=sampleTime[f].split('');
		var digitsHtml='';
		for(var j=0;j<digits.length;j++){
			digitsHtml+='<span style="display:inline-block;box-sizing:content-box;background:'+bgc+';color:'+tc+';font-size:'+tfs+'px;font-family:'+tff+';border:'+tbw+'px solid '+tbc+';padding:0.15em 0.25em;text-align:center;min-width:0.6em;line-height:1.4;'+(j>0?'border-left:none;':'')+'">'+digits[j]+'</span>';
		}
		html+='<div style="display:inline-block;margin-right:'+dist+'px;text-align:center;">'
			+'<div>'+digitsHtml+'</div>'
			+'<span style="display:block;background:'+ttbg+';color:'+ttc+';font-size:'+ttfs+'px;font-family:'+ttff
			+';border:'+ttbw+'px solid '+ttbc+';border-radius:'+ttbr+'px;border-style:solid'
			+';padding:'+pad4(ttp)+';margin:'+pad4(ttm)+';">'+texts[f]+'</span>'
			+'</div>';
	}
	html+='</div>';
	return html;
}

function darken(hex,pct){
	hex=hex.replace('#','');
	if(hex.length===3) hex=hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
	var r=Math.max(0,parseInt(hex.substr(0,2),16)-Math.round(255*pct/100));
	var g=Math.max(0,parseInt(hex.substr(2,2),16)-Math.round(255*pct/100));
	var b=Math.max(0,parseInt(hex.substr(4,2),16)-Math.round(255*pct/100));
	return '#'+('0'+r.toString(16)).slice(-2)+('0'+g.toString(16)).slice(-2)+('0'+b.toString(16)).slice(-2);
}

function hexToRgba(hex,opacity){
	hex=hex.replace('#','');
	if(hex.length===3) hex=hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
	var r=parseInt(hex.substr(0,2),16)||0;
	var g=parseInt(hex.substr(2,2),16)||0;
	var b=parseInt(hex.substr(4,2),16)||0;
	return 'rgba('+r+','+g+','+b+','+opacity+')';
}

function refreshPreview(){
	if(!preview) return;
	var type=gv('countdown_type')||'standart';
	var html='';
	switch(type){
		case 'standart': html=renderStandard(); break;
		case 'vertical': html=renderVertical(); break;
		case 'circle':   html=renderCircle(); break;
		case 'flip':     html=renderFlip(); break;
	}
	preview.innerHTML=html;
}

jQuery(document).ready(function(){
	preview=document.getElementById('wpda_theme_preview');
	btn=document.getElementById('wpda_refresh_preview');
	if(!preview) return;
	if(btn) btn.addEventListener('click',refreshPreview);
	jQuery('#adminForm').on('change input','select,input',function(){
		clearTimeout(window._wpda_preview_timer);
		window._wpda_preview_timer=setTimeout(refreshPreview,300);
	});
	jQuery(document).on('irischange','.color',function(){
		clearTimeout(window._wpda_preview_timer);
		window._wpda_preview_timer=setTimeout(refreshPreview,150);
	});
	setTimeout(refreshPreview,500);
});
})();
