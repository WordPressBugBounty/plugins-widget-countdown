<?php
if ( ! defined( "ABSPATH" ) ) exit;
class wpda_countdown_popup_page{
	
	private $options;
	private $abstract_options;
	private static $text_parametrs=array('parametrs_sucsses_saved'=>'Successfully saved.','error_in_saving'=>'can\'t save "%s" plugin parameter<br>','missing_title'=>'Type Message Title','missing_fromname'=>'Type From Name','missing_frommail'=>'Type From mail','mising_massage'=>'Type Message','sucsses_mailed'=>'Your message was sent successfully.',	'error_maied'=>'error sending email','authorize_problem' => 'Authorization Problem'	);
	
	function __construct(){	
		$this->update_old_option_to_new();
		$this->options=self::return_params_array();	
		$this->generete_abstract_params_list();
		$this->generete_popup_settings();
	}
	
	public static function return_params_array(){
		$timers_array = wpda_countdown()->timer_repository()->all_names();
		$themes_array = wpda_countdown()->theme_repository()->all_names();
		$params = array(
			"general_options"=>array(
				"heading_name"=>"General",
				"location"=>"left",
				"image"=>wpda_countdown_plugin_url."left",
				"params"=>array(
					"general_show_popup_action"=>array(
						"title"=>"Show/Hide countdown from below list",
						"description"=>"Choose the action to show or hide countdown from below list.",
						"values"=>array("show"=>"Show from Below selecte list","hide"=>"Hide from Below selecte list"),
						"default_value"=>"hide",						
						"function_name"=>"simple_select"
					),	
					"general_show_popup_on_multiple"=>array(
						"title"=>"Select the list",
						"description"=>"Click on the field and then choose something from list.",
						"default_value"=>array(),						
						"function_name"=>"multiplay_select"
					),
					"general_show_quantity"=>array(
						"title"=>"Countdown popup display periodicity",
						"description"=>"Select the countdown popup display periodicity.",
						"values"=>array(
							"one_time"=>"Оne Тime",
							"on_refresh"=>"Еvery Тime",	
						),
						"default_value"=>"on_refresh",
						"function_name"=>"simple_select",
					),
					"general_show_popup_after"=>array(
						"title"=>"Time to show the countdown popup",
						"description"=>"Type the time when countdown popup will appear.",				
						"function_name"=>"simple_input",
						"small_text"=>"(seconds)",
						"default_value"=>"1",
						"type"=>"number"
					),
					"general_element_ordering"=>array(
						"title"=>"Countdown popup elements ordering(hiding)",
						"description"=>"Choose the countdown popup elements ordering, also you can hide Popup countdown elements by click on exact element.",				
						"function_name"=>"oredering",
						"values"=>array(
							"title"=>"Title",
							"message"=>"Message",
							"countdown"=>"Countdown",
						),
						"default_value"=>'{"title":[1,"title"],"message":[1,"message"],"countdown":[1,"countdown"]}',
						"type"=>"number"
					),
					"general_scrolling_content"=>array(
						"title"=>"Visible or hidden scroll inside popup",
						"description"=>"Select visible or hidden scroll inside popup",
						"values"=>array(
							"yes"=>"Visible",
							"no"=>"Hidden",
						),
						"default_value"=>"no",
						"function_name"=>"simple_select"
					),
					"general_hide_after_expire"=>array(
						"title"=>"Hide popup after countdown expires",
						"description"=>"Automatically close the popup when the countdown reaches zero. Useful for time-limited events like Black Friday deals.",
						"values"=>array(
							"no"=>"No — keep popup visible",
							"yes"=>"Yes — close popup when expired",
						),
						"default_value"=>"no",
						"function_name"=>"simple_select"
					),
				)
			),
			"countdown_options"=>array(
				"heading_name"=>"Countdown",
				"location"=>"left",
				"image"=>wpda_countdown_plugin_url."left",
				"params"=>array(
					"countdown_timer"=>array(
						"title"=>"Countdown timer",
						"description"=>"Select the countdown timer.",
						"values"=>$timers_array,
						"default_value"=>"1",
						"function_name"=>"simple_select",
					),
					"countdown_theme"=>array(
						"title"=>"Countdown theme",
						"description"=>"Select the countdown theme.",
						"values"=>$themes_array,
						"default_value"=>"1",
						"function_name"=>"simple_select",
					),
				),
			),
			"overlay_options"=>array(
				"heading_name"=>"Overlay",
				"location"=>"right",
				"image"=>wpda_countdown_plugin_url."left",
				"params"=>array(
					"overlay_show_hide"=>array(
						"title"=>"Show overlay",
						"description"=>"Show/Hide Overlay.",
						"values"=>array(
							"1"=>"Show",
							"0"=>"Hide",
						),
						"default_value"=>"true",
						"function_name"=>"simple_select",
					),
					"overlay_trancparency"=>array(
						"title"=>"Overlay transparency",
						"description"=>"Set the overlay transparency.",						
						"default_value"=>"30",
						"small_text"=>"(%)",
						"function_name"=>"range_input"
					),	
					"overlay_top_bg_color"=>array(
						"title"=>"Overlay top background color",
						"description"=>"Choose overlay top background color",
						"default_value"=>"#000000",
						"function_name"=>"color_input",
					),
					"overlay_top_bottom_color"=>array(
						"title"=>"Overlay bottom background color",
						"description"=>"Choose overlay bottom background color.",
						"default_value"=>"#000000",
						"function_name"=>"color_input",
					),
					"overlay_fade_efect_time"=>array(
						"title"=>"Overlay fade effect time",
						"description"=>"Set overlay fade effect time.",				
						"function_name"=>"simple_input",
						"small_text"=>"(Miliseconds)",
						"default_value"=>"500",						
					)
			
				)
			),	
			"popup_options"=>array(
				"heading_name"=>"Popup",
				"location"=>"right",
				"image"=>wpda_countdown_plugin_url."left",
				"params"=>array(
					"popup_width"=>array(
						"title"=>"Width",
						"description"=>"Type width for popup",						
						"default_value"=>"550",
						"small_text"=>"(Px)",
						"type"=>"number",
						"function_name"=>"simple_input"
					),	
					"popup_height"=>array(
						"title"=>"Height",
						"description"=>"Type height for popup",						
						"default_value"=>"300",
						"small_text"=>"(Px)",
						"type"=>"number",
						"function_name"=>"simple_input"
					),	
					"popup_position"=>array(
						"title"=>"Popup position",
						"description"=>"Select popup position",
						"values"=>array(
							"1"=>"Top Left",
							"2"=>"Top center",
							"3"=>"Top right",
							"4"=>"Middle Left",
							"5"=>"Middle center",
							"6"=>"Middle right",
							"7"=>"Bottom Left",
							"8"=>"Bottom center",
							"9"=>"Bottom right",	
						),
						"default_value"=>"5",
						"function_name"=>"simple_select",
					),
					"popup_fixed_position"=>array(
						"title"=>"Popup fixed position",
						"description"=>"Enable/Disable fixed position for Popup",
						"values"=>array(
							"true"=>"Enable",
							"false"=>"Dsiable",
						),
						"default_value"=>"true",
						"function_name"=>"simple_select",
					),
					"popup_outside_margin"=>array(
						"title"=>"Popup distance from window",
						"description"=>"Type Popup distance from window when position is 1,2,3,4,6,7,8,9(except Middle center position)",				
						"function_name"=>"simple_input",
						"small_text"=>"(Px)",
						"default_value"=>"20",						
					),
					"popup_background_color"=>array(
						"title"=>"Popup background color",
						"description"=>"Set the Popup background color",						
						"function_name"=>"color_input",
						"default_value"=>"#ffffff",
					),
					"popup_border_color"=>array(
						"title"=>"Popup border color",
						"description"=>"Set the Popup border color",						
						"function_name"=>"color_input",
						"default_value"=>"#000000",
					),			
					"popup_border_width"=>array(
						"title"=>"Popup border width",
						"description"=>"Type the Popup border width",				
						"function_name"=>"simple_input",
						"small_text"=>"(Px)",
						"default_value"=>"2",						
					),
					"popup_border_radius"=>array(
						"title"=>"Popup border radius",
						"description"=>"Set the Popup border radius",				
						"function_name"=>"simple_input",
						"small_text"=>"(Px)",
						"default_value"=>"8",						
					),
					"popup_animation_type"=>array(
						"title"=>"Popup opening animation type",
						"description"=>"Choose popup opening animation type",
						"values"=>array(
							"disable"=>"Disabel",
							"fade"=>"Fade",
							"zoom_out"=>"Zoom  out",
							"zoom_in"=>"Zoom  in",
							"slide_in_right"=>"Slide in from right",
							"slide_in_left"=>"Slide in from left",
							"slide_from_top"=>"Slide in from top",
							"slide_from_bottom"=>"Slide in from Bottom",
							"newspaper"=>"Newspaper",
							"flip_hor_left"=>"Flip Horizontal Left",
							"flip_hor_right"=>"Flip Horizontal Right",
							"flip_ver_bottom"=>"Flip Vertical Top",
							"wpdevart_lb_flip_ver_bottom"=>"Flip Vertical Bottom",
						),
						"default_value"=>"disable",
						"function_name"=>"simple_select",
					),
					"popup_animation_time"=>array(
						"title"=>"Popup animation duration",
						"description"=>"Choose duration of Popup animation",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"small_text"=>"(Miliseconds)",
						"default_value"=>"500",						
					)			
				),
			
			),
			"control_buttons"=>array(
				"heading_name"=>"Close Button",
				"location"=>"right",
				"image"=>wpda_countdown_plugin_url."left",
				"params"=>array(
					"control_buttons_show_hide"=>array(
						"title"=>"Show Close button section",
						"description"=>"Select to show/hide close button section",
						"values"=>array(
							"1"=>"Show",
							"0"=>"Hide",
						),
						"default_value"=>"true",
						"function_name"=>"simple_select",
					),
					"control_buttons_line_position"=>array(
						"title"=>"Close button section position",
						"description"=>"Select close button section position",
						"values"=>array(
							"0"=>"Top inside popup",
							"1"=>"Top outside popup",
							"2"=>"Bottom inside popup",
							"3"=>"Bottom outside popup",
						),
						"default_value"=>"0",
						"function_name"=>"simple_select",
					),					
					"control_buttons_line_height"=>array(
						"title"=>"Close button section line height",
						"description"=>"Type close button section line height",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"small_text"=>"(px)",
						"default_value"=>"30",						
					),
					"control_buttons_line_bg_color"=>array(
						"title"=>"Close button section background color",
						"description"=>"Select close button section background color",						
						"function_name"=>"color_input",
						"default_value"=>"#000000",
					),
					"control_buttons_transparency"=>array(
						"title"=>"Close button section transparency",
						"description"=>"Select close button section transparency",						
						"default_value"=>"60",
						"small_text"=>"(%)",
						"function_name"=>"range_input"
					),	
					"control_buttons_transparency_hover"=>array(
						"title"=>"Close button section hover transparency",
						"description"=>"Select close button section hover transparency",						
						"default_value"=>"100",
						"small_text"=>"(%)",
						"function_name"=>"range_input"
					),	
					"control_buttons_close_position"=>array(
						"title"=>"Close button position",
						"description"=>"Select close button position",
						"values"=>array(
							"right"=>"Right",
							"left"=>"Left",
						),
						"default_value"=>"right",
						"function_name"=>"simple_select",
					),
					"control_buttons_close_icon_style"=>array(
						"title"=>"Close icon style",
						"description"=>"Select the close button icon style",
						"function_name"=>"simple_select_pro_font_size",
						"values"=>array(
							"cross"=>array("✕  Thin cross",""),
							"times"=>array("✖  Bold cross",""),
							"circle_x"=>array("⊗  Circle cross",""),
						),
						"default_value"=>"cross",
					),
					"control_buttons_close_icon_color"=>array(
						"title"=>"Close icon color",
						"description"=>"Select the close icon color",
						"function_name"=>"color_input",
						"default_value"=>"#ffffff",
					),	
				),
			),
			"title_options"=>array(
				"heading_name"=>"Popup Title",
				"location"=>"left",
				"image"=>wpda_countdown_plugin_url."left",
				"params"=>array(
					"popup_page_title_text"=>array(
						"title"=>"Title",
						"description"=>"Type here the popup title.",				
						"function_name"=>"simple_input",
						"default_value"=>"Countdown",						
					),
					"popup_page_title_color"=>array(
						"title"=>"Title color",
						"description"=>"Select the popup title color.",						
						"function_name"=>"color_input",
						"default_value"=>"#000000",
					),
					"popup_page_title_font_size"=>array(
						"title"=>"Title font size",
						"description"=>"Type here the popup title font size.",				
						"function_name"=>"simple_input",
						"small_text"=>"(px)",
						"default_value"=>"24",						
					),
					"popup_page_title_font_family"=>array(
						"title"=>"Title font family",
						"description"=>"Select the popup title font family.",				
						"function_name"=>"font_select",
						"values"=>Wpda_Countdown_Admin_Fields::font_choices(),
						"default_value"=>"Arial,Helvetica Neue,Helvetica,sans-serif",
					),
					"popup_page_title_position"=>array(
						"title"=>"Title position",
						"description"=>"Select the popup title position.",
						"values"=>array(
							"left"=>"Left",
							"center"=>"Center",	
							"right"=>"Right",	
						),
						"default_value"=>"center",
						"function_name"=>"simple_select",
					),
					"popup_page_title_padding"=>array(
						"title"=>"Title padding",
						"description"=>"Type here the popup title padding.",				
						"function_name"=>"padding_margin_input",
						"values"=>array("top"=>"30","right"=>"0","bottom"=>"0","left"=>"0"),
						"default_value"=>array("top"=>"30","right"=>"0","bottom"=>"0","left"=>"0"),
					),
					"popup_page_title_animation"=>array(
						"title"=>"Title animation type",
						"description"=>"Select the popup title animation type.",				
						"function_name"=>"simple_select",
						"values"=>array(
							"none"=>"None",
							"random"=>"Random",
							"Attention_Seekers"=>array(
								"bounce"=>"Bounce",
								"flash"=>"Flash",
								"pulse"=>"Pulse",
								"rubberBand"=>"RubberBand",
								"shake"=>"Shake",
								"swing"=>"Swing",
								"tada"=>"tada",
								"wobble"=>"wobble",
							),		
							"Bouncing_Entrances"=>array(
								"bounceIn"=>"BounceIn",
								"bounceInDown"=>"BounceInDown",
								"bounceInLeft"=>"BounceInLeft",
								"bounceInRight"=>"BounceInRight",
								"bounceInUp"=>"BounceInUp",
							),						
							"Fading_Entrances"=>array(
								"fadeIn"=>"FadeIn",
								"fadeInDown"=>"FadeInDown",
								"fadeInDownBig"=>"FadeInDownBig",
								"fadeInLeft"=>"FadeInLeft",
								"fadeInLeftBig"=>"FadeInLeftBig",
								"fadeInRight"=>"FadeInRight",
								"fadeInRightBig"=>"FadeInRightBig",
								"fadeInUp"=>"FadeInUp",
								"fadeInUpBig"=>"FadeInUpBig",
							),
							"Flippers"=>array(
								"flip"=>"Flip",
								"flipInX"=>"FlipInX",
								"flipInY"=>"FlipInY",
							),
							"Lightspeed"=>array(
								"lightSpeedIn"=>"LightSpeedIn",
							),
							"Rotating_Entrances"=>array(
								"rotateIn"=>"RotateIn",
								"rotateInDownLeft"=>"RotateInDownLeft",
								"rotateInDownRight"=>"RotateInDownRight",
								"rotateInUpLeft"=>"RotateInUpLeft",
								"rotateInUpRight"=>"RotateInUpRight",
							),
							"Specials"=>array(
								"rollIn"=>"RollIn",
							),
							"Zoom_Entrances"=>array(
								"zoomIn"=>"ZoomIn",
								"zoomInDown"=>"ZoomInDown",
								"zoomInLeft"=>"ZoomInLeft",
								"zoomInRight"=>"ZoomInRight",
								"zoomInUp"=>"ZoomInUp",
							),
						),
						"default_value"=>"none",
					),
					"popup_page_title_animation_time"=>array(
						"title"=>"Animation waiting time",
						"description"=>"Type here the animation waiting time.",				
						"function_name"=>"simple_input",
						"small_text"=>"(milliseconds)",
						"default_value"=>"0",						
					),			
				)
			),
			"message_options"=>array(
				"heading_name"=>"Popup message",
				"location"=>"left",
				"image"=>wpda_countdown_plugin_url."left",
				"params"=>array(
					"popup_page_message"=>array(
						"title"=>"Message",
						"description"=>"type message",				
						"function_name"=>"tinmce",
						"default_value"=>"Countdown message",
					),
					"popup_page_message_animation"=>array(
						"title"=>"Message animation type",
						"description"=>"Select the popup message animation type.",				
						"function_name"=>"simple_select",
						"values"=>array(
							"none"=>"None",
							"random"=>"Random",
							"Attention_Seekers"=>array(
								"bounce"=>"Bounce",
								"flash"=>"Flash",
								"pulse"=>"Pulse",
								"rubberBand"=>"RubberBand",
								"shake"=>"Shake",
								"swing"=>"Swing",
								"tada"=>"tada",
								"wobble"=>"wobble",
							),		
							"Bouncing_Entrances"=>array(
								"bounceIn"=>"BounceIn",
								"bounceInDown"=>"BounceInDown",
								"bounceInLeft"=>"BounceInLeft",
								"bounceInRight"=>"BounceInRight",
								"bounceInUp"=>"BounceInUp",
							),						
							"Fading_Entrances"=>array(
								"fadeIn"=>"FadeIn",
								"fadeInDown"=>"FadeInDown",
								"fadeInDownBig"=>"FadeInDownBig",
								"fadeInLeft"=>"FadeInLeft",
								"fadeInLeftBig"=>"FadeInLeftBig",
								"fadeInRight"=>"FadeInRight",
								"fadeInRightBig"=>"FadeInRightBig",
								"fadeInUp"=>"FadeInUp",
								"fadeInUpBig"=>"FadeInUpBig",
							),
							"Flippers"=>array(
								"flip"=>"Flip",
								"flipInX"=>"FlipInX",
								"flipInY"=>"FlipInY",
							),
							"Lightspeed"=>array(
								"lightSpeedIn"=>"LightSpeedIn",
							),
							"Rotating_Entrances"=>array(
								"rotateIn"=>"RotateIn",
								"rotateInDownLeft"=>"RotateInDownLeft",
								"rotateInDownRight"=>"RotateInDownRight",
								"rotateInUpLeft"=>"RotateInUpLeft",
								"rotateInUpRight"=>"RotateInUpRight",
							),
							"Specials"=>array(
								"rollIn"=>"RollIn",
							),
							"Zoom_Entrances"=>array(
								"zoomIn"=>"ZoomIn",
								"zoomInDown"=>"ZoomInDown",
								"zoomInLeft"=>"ZoomInLeft",
								"zoomInRight"=>"ZoomInRight",
								"zoomInUp"=>"ZoomInUp",
							),
						),
						"default_value"=>"none",
					),
					"popup_page_message_animation_time"=>array(
						"title"=>"Animation waiting time",
						"description"=>"Type here the animation waiting time.",				
						"function_name"=>"simple_input",
						"small_text"=>"(milliseconds)",
						"default_value"=>"0",
					),
				)
			),
		);

		// Popup is a Pro feature — in the free build every field is marked (pro)
		// so the page renders as a full preview with an alert on any interaction.
		if ( ! WPDA_COUNTDOWN_IS_PRO ) {
			foreach ( $params as $gk => $group ) {
				if ( empty( $group['params'] ) ) continue;
				foreach ( $group['params'] as $pk => $field ) {
					$params[ $gk ]['params'][ $pk ]['pro'] = true;
				}
			}
		}

		return $params;
	}
	public static function save_in_db() {
		$params_array = self::return_params_array();
		$databases_parametrs = json_decode( get_option( 'wpda_countdown_params' ), true );
		if ( ! is_array( $databases_parametrs ) ) $databases_parametrs = array();

		if ( ! isset( $_POST['curent_page'], $_POST['countdown_options_nonce'] )
			|| ! wp_verify_nonce( $_POST['countdown_options_nonce'], 'countdown_options_nonce' ) ) {
			die( self::$text_parametrs['authorize_problem'] );
		}

		if ( $_POST['curent_page'] === 'general_save_parametr' && isset( $_POST['countdown_page_mode'] ) ) {
			update_option( 'wpdevart_countdown_popup_enable', sanitize_text_field( $_POST['countdown_page_mode'] ) );
		} elseif ( isset( $params_array[ $_POST['curent_page'] ]['params'] ) ) {
			foreach ( $params_array[ $_POST['curent_page'] ]['params'] as $key => $value ) {
				if ( isset( $_POST[ $key ] ) ) {
					$databases_parametrs[ $key ] = Wpda_Countdown_Admin_Fields::sanitize_field_value( $value, $_POST[ $key ] );
				} elseif ( $value['function_name'] === 'multiplay_select' ) {
					$databases_parametrs[ $key ] = array();
				}
			}
			update_option( 'wpda_countdown_params', wp_json_encode( $databases_parametrs ) );
		}

		die( self::$text_parametrs['parametrs_sucsses_saved'] );
	}
	public static function get_default_values_array(){
		$array_of_returned=array();
		$options=self::return_params_array();
		foreach($options as $param_heading_key=>$param_heading_value){
			foreach($param_heading_value['params'] as $key=>$value){
				$array_of_returned[$key]=$value['default_value'];
			}
		}	
		return $array_of_returned;
	}
	private function generete_popup_settings(){
		$databases_parametrs=json_decode(get_option("wpda_countdown_params"), true);
		if(!is_array($databases_parametrs)) $databases_parametrs=array();
		foreach($this->options as $param_heading_key=>$param_heading_value){
			foreach($param_heading_value['params'] as $key=>$value){
				if(isset($databases_parametrs[$key])){
					$this->options[$param_heading_key]['params'][$key]["value"]=$databases_parametrs[$key];
				}else{
					$this->options[$param_heading_key]['params'][$key]["value"]=$this->options[$param_heading_key]['params'][$key]["default_value"];
				}
			}
		}
	}
	private function generete_abstract_params_list(){
		$abstract_options=array();
		foreach($this->options as $param_heading_key=>$param_heading_value){
			foreach($param_heading_value['params'] as $key=>$value){	
				if(is_array($this->options[$param_heading_key]['params'][$key]["default_value"])){
					$abstract_options[$param_heading_key][$key]=wp_json_encode($this->options[$param_heading_key]['params'][$key]["default_value"]);
				}
				else{
					$abstract_options[$param_heading_key][$key]=$this->options[$param_heading_key]['params'][$key]["default_value"];
				}
			}
		}
		$this->abstract_options=$abstract_options;
	}
	public function controller_page(){
		$enable_disable=get_option("wpdevart_countdown_popup_enable","off");
		$locked = WPDA_COUNTDOWN_IS_PRO ? '' : ' wpda_pro_locked_page';
		?>
		<script>
			var countdown_ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
			var countdown_plugin_url="<?php echo esc_url( wpda_countdown_plugin_url ); ?>";
			var countdown_parametrs_sucsses_saved="<?php echo esc_js( self::$text_parametrs['parametrs_sucsses_saved'] ); ?>";
			var countdown_all_parametrs = <?php echo wp_json_encode( $this->abstract_options ); ?>;
		</script>
		<?php Wpda_Countdown_Admin_Fields::render_pro_banner(); ?>
		<div class="wpda_popup_page<?php echo esc_attr( $locked ); ?>">
		<div class="countdown_title"><h1><span><svg class="wpda_section_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="16" height="13" rx="2"/><rect x="7" y="7" width="16" height="13" rx="2" fill="white"/><rect x="7" y="7" width="16" height="13" rx="2"/><line x1="10" y1="13" x2="20" y2="13"/><line x1="10" y1="16" x2="16" y2="16"/></svg>Countdown Popup</span></h1></div>    
		<div id="countdown_enable" class="field switch">
			<label for="radio1" class="cb-enable <?php if($enable_disable=='on') echo 'selected'; ?>"><span>Enable</span></label>
			<label for="radio2" class="cb-disable <?php if($enable_disable=='off') echo 'selected'; ?>"><span>Disable</span></label>
			<span class="progress_enable_disable_buttons"><span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span><span class="error_massage"></span></span>
			<div style="clear:both"></div>
			<br>
		  </div>  
		<div class="wp-table right_margin">
			<table class="wp-list-table widefat fixed posts">
				<thead>
				<tr>
				<th>     
				<h4 class="params_subtitle">Parameters </h4>              
				<span class="save_all_paramss"> <button type="button" id="save_all_parametrs" class="save_all_section_parametrs button button-primary"><span class="save_button_span">Save All Sections</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button></span>
				</th>
				</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div id="countdown_page">
								<div class="left_sections">
								<?php 
								foreach($this->options as $params_grup_name =>$params_group_value){					
									if($params_group_value["location"]!="right"){
								?>
									<div class="main_parametrs_group_div closed_params " >
									<div class="head_panel_div" title="Click to toggle">
										<span class="title_parametrs_group"><?php echo esc_html( $params_group_value["heading_name"] ); ?></span>
										<span class="enabled_or_disabled_parametr"></span>
										<span class="open_or_closed"></span>         
									</div>
									<div class="inside_information_div">
										<table class="wp-list-table widefat fixed posts section_parametrs_table">                            
											<tbody> 
											<?php
											foreach($params_group_value['params'] as $param_name => $param_value){
												$args=array(
												"name"=>$param_name,
												"heading_name"=>$params_group_value['heading_name'],
												"heading_group"=>$params_grup_name,
												);
												$args=array_merge($args,$param_value);
												$function_name=$param_value['function_name'];
												Wpda_Countdown_Admin_Fields::$function_name($args);
											}
											?>
											</tbody>
											<tfoot>
												<?php if($params_grup_name!="general_options"){ ?>
												<th colspan="2" width="100%"><button type="button" id="<?php echo esc_attr( $params_grup_name ); ?>" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
												<?php }else{ ?>
												<th colspan="1"><button type="button" id="<?php echo esc_attr( $params_grup_name ); ?>" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
												<th><button type="button" onclick="countdown_set_cookies('countdown_popup','',2); alert('cookie removed')" class="save_button button button-primary" style="float:right;"><span class="save_button_span">Remove cookies</span></button></th>													
												<?php } ?>
											</tfoot>       
										</table>
									</div>     
									</div>
									<?php }} ?>
								</div>
								<div class="right_sections">
									<?php 
									foreach($this->options as $params_grup_name =>$params_group_value){					
										if($params_group_value["location"]=="right"){
									?>
									<div class="main_parametrs_group_div closed_params " >
									<div class="head_panel_div" title="Click to toggle">
										<span class="title_parametrs_group"><?php echo esc_html( $params_group_value["heading_name"] ); ?></span>
										<span class="enabled_or_disabled_parametr"></span>
										<span class="open_or_closed"></span>         
									</div>
									<div class="inside_information_div">
										<table class="wp-list-table widefat fixed posts section_parametrs_table">                            
											<tbody> 
											<?php
											foreach($params_group_value['params'] as $param_name => $param_value){
												$args=array(
												"name"=>$param_name,
												"heading_name"=>$params_group_value['heading_name'],
												"heading_group"=>$params_grup_name,
												);
												$args=array_merge($args,$param_value);
												$function_name=$param_value['function_name'];
												Wpda_Countdown_Admin_Fields::$function_name($args);
											}
											?>
											</tbody>
											<tfoot>
												<tr>												
													<?php if($params_grup_name!="general_options"){ ?>
													<th colspan="2" width="100%"><button type="button" id="<?php echo esc_attr( $params_grup_name ); ?>" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
													<?php }else{ ?>
													<th colspan="1"><button type="button" id="<?php echo esc_attr( $params_grup_name ); ?>" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
													<th><button type="button" onclick="countdown_set_cookies('countdown_popup','',2); alert('cookie removed')" class="save_button button button-primary" style="float:right;"><span class="save_button_span">Remove cookies</span></button></th>													
													<?php } ?>
												</tr>
											</tfoot>       
										</table>
									</div>     
									</div>
									<?php }} ?>       
								</div>
							</div>
							<div style="clear:both"></div>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th>                   
							<span class="save_all_paramss"><button type="button" id="save_all_parametrs" class="save_all_section_parametrs button button-primary"><span class="save_button_span">Save All Sections</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button></span>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>      
		<?php
		wp_nonce_field('countdown_options_nonce','countdown_options_nonce');
		?>
		<script>
		(function(){
			var icons={
				'General':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
				'Overlay':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="2" y="2" width="20" height="20" rx="2" opacity="0.4"/><rect x="6" y="6" width="12" height="12" rx="1"/></svg>',
				'Popup':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="5" width="15" height="11" rx="2" opacity="0.4"/><rect x="8" y="8" width="15" height="11" rx="2"/><line x1="11" y1="13" x2="20" y2="13"/><line x1="11" y1="16" x2="17" y2="16"/></svg>',
				'Close Button':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>',
				'Popup Title':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>',
				'Popup message':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>',
				'Countdown':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
			};
			document.querySelectorAll('.title_parametrs_group').forEach(function(el){
				var text=el.textContent.trim();
				if(icons[text]){
					var span=document.createElement('span');
					span.className='wpda_section_icon';
					span.innerHTML=icons[text];
					span.style.cssText='display:inline-flex;width:18px;height:18px;opacity:0.6;flex-shrink:0;';
					el.insertBefore(span,el.firstChild);
				}
			});
		})();
		</script>
		</div><!-- /.wpda_popup_page -->
		<?php
	}
	private function update_old_option_to_new(){
		$databases_parametrs=json_decode(get_option("wpda_countdown_params"), true);
		if(!is_array($databases_parametrs)) $databases_parametrs=array();
		if(isset($databases_parametrs["general_show_popup_on"])){
			if(isset($databases_parametrs["general_show_popup_on"]["everywhere"])){
				$databases_parametrs["general_show_popup_action"]="hide";
			}else{
				if(isset($databases_parametrs["general_show_popup_on"]["home"])){
					$databases_parametrs["general_show_popup_on_multiple"]["front_page"]="front_page";
				}
				if(isset($databases_parametrs["general_show_popup_on"]["post"])){
					$databases_parametrs["general_show_popup_on_multiple"]["custom_post_type_post"]="custom_post_type_post";
				}
				if(isset($databases_parametrs["general_show_popup_on"]["page"])){
					$databases_parametrs["general_show_popup_on_multiple"]["custom_post_type_page"]="custom_post_type_page";
				}
			}
			unset($databases_parametrs["general_show_popup_on"]);
			update_option("wpda_countdown_params",wp_json_encode($databases_parametrs));
		}
	}
		
}


 ?>
