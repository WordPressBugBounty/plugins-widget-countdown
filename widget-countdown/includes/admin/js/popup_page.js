/*for reomve cookies this uses for show one time or more times*/
function countdown_set_cookies(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/";
}
/*ADMIN CUSTOMIZE SETTINGS OPEN OR HIDE*/
function get_array_of_opened_elements(){
	var kk=0;
	var array_of_activ_elements=new Array();
	jQuery('#countdown_page .main_parametrs_group_div').each(function(index, element) {		
        if(!jQuery(this).hasClass('closed_params')){			
			array_of_activ_elements[kk]=jQuery('#countdown_page .main_parametrs_group_div').index(this);
			kk++;
		}
    });
	return array_of_activ_elements;
}

jQuery(document).ready(function(e) {
	/* SECTION OPEN HIDE AND SEAVE*/
    if (typeof(localStorage) != 'undefined' ) {
			active_coming_sections = localStorage.getItem("countdown_array_of_activ_section");
			active_coming_sections=JSON.parse(active_coming_sections)
			if(active_coming_sections!=null)
			for(ii=0; ii<active_coming_sections.length;ii++){
				jQuery(jQuery('#countdown_page .main_parametrs_group_div').eq(active_coming_sections[ii])).removeClass('closed_params');
			}
	}	
	jQuery('.main_parametrs_group_div > .head_panel_div').click(function(){
		
		if(jQuery(this).parent().hasClass('closed_params')){
			jQuery(this).parent().find('.inside_information_div').slideDown( "normal" )
			jQuery(this).parent().removeClass('closed_params');
			localStorage.setItem("countdown_array_of_activ_section", JSON.stringify(get_array_of_opened_elements()));
		}
		else{
			jQuery(this).parent().find('.inside_information_div').slideUp( "normal",function(){jQuery(this).parent().addClass('closed_params'); localStorage.setItem("countdown_array_of_activ_section", JSON.stringify(get_array_of_opened_elements()));} )
		}
		
	})
	/*SET CoLOR PICKERS*/
	jQuery('.color_option').wpColorPicker()
	
	/*radio Enable Disable*/
	countdown_clickable=1;
	jQuery(".cb-enable").click(function(){
		if(!countdown_clickable || jQuery(this).hasClass('selected'))
		return;
		countdown_clickable=0;
		jQuery('#countdown_enable .saving_in_progress').css('display','inline-block');
		jQuery.ajax({
					type:'POST',
					url: countdown_ajaxurl+'?action=countdown_popup_page_save',
					data: {curent_page:'general_save_parametr',countdown_options_nonce:jQuery('#countdown_options_nonce').val(),countdown_page_mode:'on'},
				}).done(function(date) {
					jQuery('#countdown_enable .saving_in_progress').css('display','none');
					if(date==countdown_parametrs_sucsses_saved){							
						jQuery('#countdown_enable .sucsses_save').css('display','inline-block');
						setTimeout(function(){countdown_clickable=1;jQuery('#countdown_enable .sucsses_save').hide('fast');jQuery('#save_button').removeClass('padding_loading');jQuery("#save_button").prop('disabled', false);},500);
						
					}
					else{
						jQuery('#countdown_enable .error_in_saving').css('display','inline-block');
						jQuery('#countdown_enable .error_massage').html(date);							
						
					}
		});
		var parent = jQuery(this).parents('.switch');
		jQuery('.cb-disable',parent).removeClass('selected');
		jQuery(this).addClass('selected');		
	});
	jQuery(".cb-disable").click(function(){
		if(!countdown_clickable || jQuery(this).hasClass('selected'))
		return;
		countdown_clickable=0;
		jQuery('#countdown_enable .saving_in_progress').css('display','inline-block');
		jQuery.ajax({
					type:'POST',
					url: countdown_ajaxurl+'?action=countdown_popup_page_save',
					data: {curent_page:'general_save_parametr',countdown_options_nonce:jQuery('#countdown_options_nonce').val(),countdown_page_mode:'off'},
				}).done(function(date) {
					jQuery('#countdown_enable .saving_in_progress').css('display','none');
					if(date==countdown_parametrs_sucsses_saved){							
						jQuery('#countdown_enable .sucsses_save').css('display','inline-block');
						setTimeout(function(){countdown_clickable=1;jQuery('#countdown_enable .sucsses_save').hide('fast');jQuery('#save_button').removeClass('padding_loading');jQuery("#save_button").prop('disabled', false);},500);
						
					}
					else{
						jQuery('#countdown_enable .error_in_saving').css('display','inline-block');
						jQuery('#countdown_enable .error_massage').html(date);
						
					}
		});
		var parent = jQuery(this).parents('.switch');
		jQuery('.cb-enable',parent).removeClass('selected');
		jQuery(this).addClass('selected');
					
	});
	
	/*Upload button single click*/
	jQuery('.upload-button').click(function (e) {
		var self=this;
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            
            var image_url = uploaded_image.toJSON().url;
            // Let's assign the url value to the input field
            jQuery(self).parent().find('.upload').val(image_url);
			jQuery(self).parent().find('.upload_many_images').val(image_url);
        });
    });
	
	/* MANY UPLOADS FOR BACKGROUND*/
	jQuery('.add_upload_image_button').click(function(){
		 
		jQuery('.slider_images_div').eq(jQuery('.slider_images_div').length-1).after(jQuery('<div class="slider_images_div"><input type="text" class="upload_many_images" value=""/><input class="upload-button  button" type="button" value="Upload"/><img src="'+countdown_plugin_url+'includes/admin/images/remove_element.png" title="remove" class="remove_upload_image"/></div>'))
			initial_last_element_functions(this);
		})
		jQuery('.remove_upload_image').click(function(){
			if(jQuery('.remove_upload_image').length>1)
				jQuery(this).parent().remove()	
	})
 	function initial_last_element_functions(element_of_add){
		jQuery('.remove_upload_image').eq(jQuery('.remove_upload_image').length-1).click(function(){
			if(jQuery('.remove_upload_image').length>1)
				jQuery(this).parent().remove()	
		})
		jQuery(element_of_add).parent().find('.upload-button').eq(jQuery(element_of_add).parent().find('.upload-button').length-1).click(function (e) {
			var self=this;
			e.preventDefault();
			var image = wp.media({ 
				title: 'Upload Image',
				// mutiple: true if you want to upload multiple files at once
				multiple: false
			}).open()
			.on('select', function(e){
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Output to the console uploaded_image
				var image_url = uploaded_image.toJSON().url;
				// Let's assign the url value to the input field
				jQuery(self).parent().find('.upload').val(image_url);
				jQuery(self).parent().find('.upload_many_images').val(image_url);
			});
		});
	}
	
	/*sortable content*/
	jQuery( ".wpdevart_sortable" ).sortable({placeholder: "ui-state-highlight"});
	jQuery( ".wpdevart_sortable" ).disableSelection();
	jQuery( ".wpdevart_sortable li" ).click(function(){
		if(jQuery(this).hasClass('control_deactive')){
			jQuery(this).removeClass('control_deactive')
			jQuery(this).addClass('control_active')
		}else{
			jQuery(this).removeClass('control_active')
			jQuery(this).addClass('control_deactive')
		}
	});
	function set_information_ordering_to_input(){		
		jQuery('.wpdevart_sortable').each(function(){
			var set_input_value={};
			jQuery(this).find('li').each(function() {
				var loc_array=[];
				if(jQuery(this).hasClass('control_active'))
					loc_array[0]=1;
				else
					loc_array[0]=0;
				loc_array[1]=jQuery( this ).attr('date-value');
				set_input_value[jQuery( this ).attr('date-value')]=loc_array;

			});			
			jQuery(this).parent().find("input").val(JSON.stringify(set_input_value))
		})	
	}
	/*stop when more clicks*/
	var stoper=0;
	var section_count=jQuery(".save_section_parametrs").length;
	jQuery(".save_all_section_parametrs").click(function(){
		jQuery('.save_all_section_parametrs').addClass('padding_loading');
		jQuery('.save_all_section_parametrs').prop('disabled', true);		
		jQuery('.save_all_section_parametrs .saving_in_progress').css('display','inline-block');
		setTimeout(function(){check_all_saved_if_no_go_to_next_section(0)},500);
	})
	function check_all_saved_if_no_go_to_next_section(index){
		if((section_count)!=index && stoper==0){
			jQuery(".save_section_parametrs").eq(index).trigger('click');
			index=index+1;
		}
		if((section_count)==index && stoper==0){
			jQuery('.save_all_section_parametrs .saving_in_progress').css('display','none');
			jQuery('.save_all_section_parametrs .sucsses_save').css('display','inline-block');
			setTimeout(function(){jQuery('.save_all_section_parametrs .sucsses_save').hide('fast');jQuery('.save_all_section_parametrs').removeClass('padding_loading');jQuery('.save_all_section_parametrs').prop('disabled', false);},1800);
			
		}
		else{		
			setTimeout(function(){check_all_saved_if_no_go_to_next_section(index)},500);
		}
	}
	
	/*############ Other section Save click ################*/
	jQuery(".save_section_parametrs").click(function(){
		set_information_ordering_to_input()
		if(typeof tinymce!=='undefined') tinymce.triggerSave();

		var countdown_curent_section=jQuery(this).attr('id');
		jQuery.each( countdown_all_parametrs[countdown_curent_section], function( key, value ) {
			var el=jQuery('#'+key);
			if(jQuery('input[name^="'+key+'["]').length>1){
				var input_array={};
				if(jQuery('input[name^="'+key+'"]').eq(0).attr("type")=="checkbox"){
					if(jQuery('input[name^="'+key+'"]:checked').length>0){
						jQuery('input[name^="'+key+'"]:checked').each(function(){
							input_array[jQuery(this).val()]=jQuery(this).val();
						})
					}else{
						input_array=""
					}

				}else{
					jQuery('input[name^="'+key+'"]').each(function(){
						cur_key=jQuery(this).attr("name").replace(key,"").replace("[","").replace("]","");
						input_array[cur_key]=jQuery(this).val();
					})
				}
				countdown_all_parametrs[countdown_curent_section][key]=input_array;
			}else if(el.length && el.prop('multiple')){
				countdown_all_parametrs[countdown_curent_section][key]=el.val()||[];
			}else if(el.length){
				countdown_all_parametrs[countdown_curent_section][key]=el.val();
			}
		});
		var countdown_date_for_post=countdown_all_parametrs;
		countdown_all_parametrs[countdown_curent_section]['curent_page']=countdown_curent_section;
		countdown_all_parametrs[countdown_curent_section]['countdown_options_nonce']=jQuery('#countdown_options_nonce').val();
		
		
		jQuery('#'+countdown_curent_section).addClass('padding_loading');
		jQuery('#'+countdown_curent_section).prop('disabled', true);		
		jQuery('#'+countdown_curent_section+' .saving_in_progress').css('display','inline-block');
		stoper++;
		jQuery.ajax({
					type:'POST',
					url: countdown_ajaxurl+'?action=countdown_popup_page_save',
					data: countdown_all_parametrs[countdown_curent_section],
				}).done(function(date) {
					jQuery('#'+countdown_curent_section+' .saving_in_progress').css('display','none');
					if(date==countdown_parametrs_sucsses_saved){							
						jQuery('#'+countdown_curent_section+' .sucsses_save').css('display','inline-block');
						setTimeout(function(){countdown_clickable=1;jQuery('#'+countdown_curent_section+' .sucsses_save').hide('fast');jQuery('#'+countdown_curent_section+'.save_section_parametrs').removeClass('padding_loading');jQuery('#'+countdown_curent_section).prop('disabled', false);},1800);
						stoper--;
					}
					else{
						jQuery('#'+countdown_curent_section+' .error_in_saving').css('display','inline-block');
						jQuery('#'+countdown_curent_section).parent().find('.error_massage').eq(0).html(date);
						
					}
		});
	});

});