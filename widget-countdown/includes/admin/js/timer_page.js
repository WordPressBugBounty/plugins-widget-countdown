function submitbutton(value){
	jQuery("#adminForm").attr("action",jQuery("#adminForm").attr("action")+"&task="+value);
	jQuery("#adminForm").submit();
}
wpda_timer_class={
	start_tab_id:"timer_set_time",
	current_tab_id:"timer_set_time",
	after_countdown_end_type:{
		hide:[""],
		text:["after_countdown_text"],
		redirect:["after_countdown_redirect"],
		button:["after_countdown_button_text","after_countdown_button_url"],
		hide_content:["after_countdown_css_selector"],
		show_content:["after_countdown_css_selector"],
	},
	before_countup_start_type:{
		none:[""],
		text:["before_countup_text"],
		redirect:["before_countup_redirect"],
	},
	timer_coundown_type:{
		countdown:["timer_end_date","timer_start_time","timer_coundown_repeat"],
		countup:["timer_end_date","timer_start_time","timer_coundown_repeat"],
		evergreen_countup:["evergreen_expire_mode"],
		evergreen_countdown:["evergreen_expire_mode"],
	},
	evergreen_expire_mode:{
		duration:["timer_seesion_time","evergreen_restart"],
		daily_time:["evergreen_daily_expire_time"],
	},
	evergreen_restart:{
		none:[""],
		immediate:[""],
		delay:["evergreen_restart_delay"],
	},
	timer_coundown_repeat:{
		none:[""],
		when_end:["after_countdown_repeat_time","repeat_end"],
		hourly:["repeat_hourly_interval","after_countdown_repeat_time","repeat_end"],
		daily:["repeat_daily_quantity","repeat_countdown_start_time","repeat_countdown_end_time","repeat_end"],
		weekly:["repeat_weekly_days","repeat_countdown_start_time","repeat_countdown_end_time","repeat_end"],
		monthly:["repeat_monthly_day","repeat_countdown_start_time","repeat_countdown_end_time","repeat_end"],
	},
	repeat_end:{
		never:[""],
		after:["repeat_ending_after"],
		on_date:["repeat_ending_after_date"],
	},
	all_selects:['timer_coundown_type','evergreen_expire_mode','evergreen_restart','timer_coundown_repeat','repeat_end','after_countdown_end_type','before_countup_start_type'],
	start:function(){
		var self=this;
		jQuery(document).ready(function(){
			self.conect_tab_activate_functionality();
			self.initialize_timpickers();
			self.show_hide_elems_on_select_change();
			self.init_all_tabs();
		})
	},
	init_all_tabs:function(){
		var self=this;
		// activate each tab, run all selects, then go back to start tab
		jQuery(".wpda_timer_link_tabs li").each(function(){
			var tab_id=jQuery(this).attr('id').replace("_tab","");
			jQuery(".all_options_panel table tr."+tab_id).addClass('active');
		});
		// now all rows visible — run every select to hide what needs hiding
		// only process if the select's row is still visible (not hidden by a parent select)
		for(var i=0;i<self.all_selects.length;i++){
			var sid=self.all_selects[i];
			var el=document.getElementById(sid);
			if(!el||typeof self[sid]==="undefined") continue;
			var row=jQuery(el).closest('tr');
			if(!row.hasClass('active')) continue;
			self.show_hide_elements_by_select_val(self[sid],sid);
		}
		// now hide all rows and activate only start tab
		jQuery(".all_options_panel table tr").removeClass('active');
		self.activete_tab(self.start_tab_id);
	},
	conect_tab_activate_functionality:function(){
		var self=this;
		jQuery(".wpda_timer_link_tabs li").click(function(){
			self.current_tab_id=jQuery(this).attr('id').replace("_tab","");
			self.activete_tab(self.current_tab_id);
		});
	},
	activete_tab:function(tab_id){
		var self = this;
		jQuery(".wpda_timer_link_tabs li,.all_options_panel table tr").removeClass('active');
		jQuery("#"+tab_id+"_tab").addClass('active');
		jQuery((".all_options_panel table tr" + "."+tab_id)).addClass('active');
		// run all selects that have configs — handles nested deps
		// only process if the select's row is still visible (has 'active')
		for(var i=0;i<self.all_selects.length;i++){
			var sid=self.all_selects[i];
			var el=document.getElementById(sid);
			if(!el) continue;
			var row=jQuery(el).closest('tr');
			if(!row.hasClass('active')) continue;
			if(typeof self[sid]!=="undefined"){
				self.show_hide_elements_by_select_val(self[sid],sid);
			}
		}
	},
	initialize_timpickers:function(){
		if (typeof flatpickr === 'undefined') return;

		// Flatpickr's hour/minute spinners only commit on blur. If the user types
		// or scrolls then clicks outside the calendar, the close-on-outside handler
		// fires before blur, and the pending value is lost. Fix: listen to 'input'
		// and 'change' on the spinner elements and push the value into selectedDates
		// in real time.
		function commitPendingTime(instance) {
			if (!instance.selectedDates.length) return;
			if (!instance.hourElement || !instance.minuteElement) return;
			var h = parseInt(instance.hourElement.value, 10);
			var m = parseInt(instance.minuteElement.value, 10);
			if (isNaN(h) || isNaN(m)) return;
			if (h < 0 || h > 23 || m < 0 || m > 59) return;
			var current = instance.selectedDates[0];
			if (current.getHours() === h && current.getMinutes() === m) return;
			var d = new Date(current);
			d.setHours(h);
			d.setMinutes(m);
			instance.setDate(d, true);
		}

		function wireTimeSync(instance) {
			var onInput = function(){ commitPendingTime(instance); };
			if (instance.hourElement) {
				instance.hourElement.addEventListener('input', onInput);
				instance.hourElement.addEventListener('change', onInput);
			}
			if (instance.minuteElement) {
				instance.minuteElement.addEventListener('input', onInput);
				instance.minuteElement.addEventListener('change', onInput);
			}
		}

		var common = {
			enableTime: true,
			time_24hr: true,
			dateFormat: 'd/m/Y H:i',
			allowInput: true,
			wrap: false,
			onReady: function(selectedDates, dateStr, instance) {
				wireTimeSync(instance);
			},
			onClose: function(selectedDates, dateStr, instance) {
				commitPendingTime(instance);
			}
		};

		// Linked pickers: start cannot be after end, end cannot be before start
		var startEl = document.querySelector('input[name="timer_start_time"]');
		var endEl   = document.querySelector('input[name="timer_end_date"]');
		var startPicker = null, endPicker = null;

		if (startEl) {
			startPicker = flatpickr(startEl, Object.assign({}, common, {
				onChange: function(selectedDates) {
					if (!endPicker) return;
					endPicker.set('minDate', selectedDates.length ? selectedDates[0] : null);
				}
			}));
		}
		if (endEl) {
			endPicker = flatpickr(endEl, Object.assign({}, common, {
				onChange: function(selectedDates) {
					if (!startPicker) return;
					startPicker.set('maxDate', selectedDates.length ? selectedDates[0] : null);
				}
			}));
		}

		// Apply initial constraints from existing values
		if (startPicker && endPicker) {
			if (startPicker.selectedDates.length) endPicker.set('minDate', startPicker.selectedDates[0]);
			if (endPicker.selectedDates.length)   startPicker.set('maxDate', endPicker.selectedDates[0]);
		}

		// Other date fields without cross-linking (e.g. repeat_ending_after_date)
		jQuery('.wpda_datepicker_timer').not('[name="timer_start_time"]').not('[name="timer_end_date"]').each(function(){
			flatpickr(this, common);
		});
	},
	show_hide_elems_on_select_change:function(){
		var self=this;
		var select_arrays=['after_countdown_end_type',"before_countup_start_type","timer_coundown_type","timer_coundown_repeat","repeat_end","evergreen_restart","evergreen_expire_mode"];
		var count_select_arrays=select_arrays.length;		
		for(var i = 0; i < count_select_arrays;i++){
			jQuery("#"+select_arrays[i]).change(function(){
				var current_elemtn=jQuery(this).attr('id');
				self.show_hide_elements_by_select_val(self[current_elemtn],current_elemtn)			
			});
		}
	},
	
	show_hide_elements_by_select_val:function(select_info,select_val){		
		var self=this;		
		var all_options=self.make_unic_array_for_select_hide(select_info);
		self.hide_elements_by_array(all_options);
		var active_options=self.make_array_for_select_active(select_val);		
		self.active_elemes_by_array(active_options);
	},
	hide_elements_by_array:function(elems_array){
		if(!Array.isArray(elems_array)){
			return false;
		}		
		var count=elems_array.length;
		for(var i = 0; i < count; i++){			
			jQuery("[name='"+elems_array[i]+"'],[name^='"+elems_array[i]+"[']").eq(0).closest('.tr_option ').removeClass('active');
		}
	},
	active_elemes_by_array:function(elems_array){
		if(!Array.isArray(elems_array)){
			return false;
		}		
		var count=elems_array.length;
		for(var i = 0; i < count; i++){
			jQuery("[name='"+elems_array[i]+"'],[name^='"+elems_array[i]+"[']").eq(0).closest('.tr_option ').addClass('active');
		}
	},
	make_unic_array_for_select_hide:function(obj){
		var self = this;
		var unic_array = new Array();
		jQuery.each(obj,function(key,value){
			var count_loc_array=value.length;
			for(var i=0;i < count_loc_array; i++){
				if(!unic_array.includes(value[i])){
					unic_array.push(value[i]);
					if(typeof(self[value[i]])!="undefined"){
						unic_array=unic_array.concat(self.make_unic_array_for_select_hide(self[value[i]]));						
						if(unic_array.length>10000){
							alert("something go wrong you in programm now in unlimit circle contact to wpdevart");
							return [];
						}
					}
				}
			}
		})		
		return unic_array;
	},
	make_array_for_select_active:function(obj_id){
		var self = this;
		var obj=self[obj_id];
		var obj_val=jQuery("#"+obj_id).val()
		var all_active_element=[];
		var count=obj[obj_val].length;		
		for(var i=0;i<count; i++){			
			all_active_element.push(obj[obj_val][i]);
			if(typeof(self[obj[obj_val][i]])!="undefined"){				
				all_active_element=all_active_element.concat(self.make_array_for_select_active(obj[obj_val][i]));				
				if(all_active_element.length>10000){
					alert("something go wrong you in programm now in unlimit circle contact to wpdevart");
					return [];
				}
			}
		}		
		return all_active_element;		
	}
	
}

wpda_timer_class.start();