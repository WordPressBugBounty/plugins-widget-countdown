function get_array_of_opened_elements(){
	var kk=0;
	var array_of_activ_elements=new Array();
	jQuery('#sticky_bar_page .main_parametrs_group_div').each(function(index, element) {
        if(!jQuery(this).hasClass('closed_params')){
			array_of_activ_elements[kk]=jQuery('#sticky_bar_page .main_parametrs_group_div').index(this);
			kk++;
		}
    });
	return array_of_activ_elements;
}

jQuery(document).ready(function(e) {
    if (typeof(localStorage) != 'undefined' ) {
			active_coming_sections = localStorage.getItem("sticky_bar_array_of_activ_section");
			active_coming_sections=JSON.parse(active_coming_sections)
			if(active_coming_sections!=null)
			for(ii=0; ii<active_coming_sections.length;ii++){
				jQuery(jQuery('#sticky_bar_page .main_parametrs_group_div').eq(active_coming_sections[ii])).removeClass('closed_params');
			}
	}
	jQuery('.main_parametrs_group_div > .head_panel_div').click(function(){
		if(jQuery(this).parent().hasClass('closed_params')){
			jQuery(this).parent().find('.inside_information_div').slideDown("normal")
			jQuery(this).parent().removeClass('closed_params');
			localStorage.setItem("sticky_bar_array_of_activ_section", JSON.stringify(get_array_of_opened_elements()));
		}else{
			jQuery(this).parent().find('.inside_information_div').slideUp("normal",function(){jQuery(this).parent().addClass('closed_params'); localStorage.setItem("sticky_bar_array_of_activ_section", JSON.stringify(get_array_of_opened_elements()));})
		}
	})
	jQuery('.color_option').wpColorPicker()

	var sticky_bar_clickable=1;
	jQuery(".cb-enable").click(function(){
		if(!sticky_bar_clickable || jQuery(this).hasClass('selected')) return;
		sticky_bar_clickable=0;
		jQuery('#sticky_bar_enable .saving_in_progress').css('display','inline-block');
		jQuery.ajax({
			type:'POST',
			url: sticky_bar_ajaxurl+'?action=sticky_bar_page_save',
			data: {curent_page:'general_save_parametr',sticky_bar_options_nonce:jQuery('#sticky_bar_options_nonce').val(),sticky_bar_page_mode:'on'},
		}).done(function(date) {
			jQuery('#sticky_bar_enable .saving_in_progress').css('display','none');
			if(date==sticky_bar_parametrs_sucsses_saved){
				jQuery('#sticky_bar_enable .sucsses_save').css('display','inline-block');
				setTimeout(function(){sticky_bar_clickable=1;jQuery('#sticky_bar_enable .sucsses_save').hide('fast');},500);
			}else{
				jQuery('#sticky_bar_enable .error_in_saving').css('display','inline-block');
				jQuery('#sticky_bar_enable .error_massage').html(date);
			}
		});
		var parent = jQuery(this).parents('.switch');
		jQuery('.cb-disable',parent).removeClass('selected');
		jQuery(this).addClass('selected');
	});
	jQuery(".cb-disable").click(function(){
		if(!sticky_bar_clickable || jQuery(this).hasClass('selected')) return;
		sticky_bar_clickable=0;
		jQuery('#sticky_bar_enable .saving_in_progress').css('display','inline-block');
		jQuery.ajax({
			type:'POST',
			url: sticky_bar_ajaxurl+'?action=sticky_bar_page_save',
			data: {curent_page:'general_save_parametr',sticky_bar_options_nonce:jQuery('#sticky_bar_options_nonce').val(),sticky_bar_page_mode:'off'},
		}).done(function(date) {
			jQuery('#sticky_bar_enable .saving_in_progress').css('display','none');
			if(date==sticky_bar_parametrs_sucsses_saved){
				jQuery('#sticky_bar_enable .sucsses_save').css('display','inline-block');
				setTimeout(function(){sticky_bar_clickable=1;jQuery('#sticky_bar_enable .sucsses_save').hide('fast');},500);
			}else{
				jQuery('#sticky_bar_enable .error_in_saving').css('display','inline-block');
				jQuery('#sticky_bar_enable .error_massage').html(date);
			}
		});
		var parent = jQuery(this).parents('.switch');
		jQuery('.cb-enable',parent).removeClass('selected');
		jQuery(this).addClass('selected');
	});

	var stoper=0;
	var section_count=jQuery(".save_section_parametrs").length;
	jQuery(".save_all_section_parametrs").click(function(){
		jQuery('.save_all_section_parametrs').addClass('padding_loading');
		jQuery('.save_all_section_parametrs').prop('disabled', true);
		jQuery('.save_all_section_parametrs .saving_in_progress').css('display','inline-block');
		setTimeout(function(){check_all_saved(0)},500);
	})
	function check_all_saved(index){
		if(section_count!=index && stoper==0){
			jQuery(".save_section_parametrs").eq(index).trigger('click');
			index++;
		}
		if(section_count==index && stoper==0){
			jQuery('.save_all_section_parametrs .saving_in_progress').css('display','none');
			jQuery('.save_all_section_parametrs .sucsses_save').css('display','inline-block');
			setTimeout(function(){jQuery('.save_all_section_parametrs .sucsses_save').hide('fast');jQuery('.save_all_section_parametrs').removeClass('padding_loading');jQuery('.save_all_section_parametrs').prop('disabled', false);},1800);
		}else{
			setTimeout(function(){check_all_saved(index)},500);
		}
	}

	jQuery(".save_section_parametrs").click(function(){
		var cur_section=jQuery(this).attr('id');
		if(typeof tinymce!=='undefined') tinymce.triggerSave();
		jQuery.each(sticky_bar_all_parametrs[cur_section], function(key, value){
			var el=jQuery('#'+key);
			if(el.length && el.prop('multiple')){
				sticky_bar_all_parametrs[cur_section][key]=el.val()||[];
			}else if(el.length){
				sticky_bar_all_parametrs[cur_section][key]=el.val();
			}
		});
		var post_data=sticky_bar_all_parametrs[cur_section];
		post_data['curent_page']=cur_section;
		post_data['sticky_bar_options_nonce']=jQuery('#sticky_bar_options_nonce').val();

		jQuery('#'+cur_section).addClass('padding_loading');
		jQuery('#'+cur_section).prop('disabled', true);
		jQuery('#'+cur_section+' .saving_in_progress').css('display','inline-block');
		stoper++;
		jQuery.ajax({
			type:'POST',
			url: sticky_bar_ajaxurl+'?action=sticky_bar_page_save',
			data: post_data,
		}).done(function(date) {
			jQuery('#'+cur_section+' .saving_in_progress').css('display','none');
			if(date==sticky_bar_parametrs_sucsses_saved){
				jQuery('#'+cur_section+' .sucsses_save').css('display','inline-block');
				setTimeout(function(){jQuery('#'+cur_section+' .sucsses_save').hide('fast');jQuery('#'+cur_section).removeClass('padding_loading');jQuery('#'+cur_section).prop('disabled', false);},1800);
				stoper--;
			}else{
				jQuery('#'+cur_section+' .error_in_saving').css('display','inline-block');
				jQuery('#'+cur_section).parent().find('.error_massage').eq(0).html(date);
			}
		});
	});
});
