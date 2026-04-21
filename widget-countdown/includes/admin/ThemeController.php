<?php
if ( ! defined( "ABSPATH" ) ) exit;
class wpda_countdown_theme_page{

	private $options;
	private $repo;

	function __construct( $repo = null ){
		$this->repo = $repo ?: new Wpda_Countdown_Theme_Repository();
		$this->options=self::return_params_array();
		// Free mode: keep every field visible but mark Pro ones with a (pro)
		// badge and lock interaction via the shared pro-lock script.
		if ( ! WPDA_COUNTDOWN_IS_PRO ) {
			$this->options = self::mark_pro_fields( $this->options );
		}
	}

	/**
	 * Individual Pro-only option keys inside mixed groups (General and Standard).
	 * Standard view is almost entirely free; only the responsive layout toggle
	 * and the two animation settings are Pro-gated.
	 */
	public static function pro_field_keys() {
		return array(
			// General group — Pro
			'countdown_date_display',              // Which time units to show (free is fixed to day/hour/minut/second)
			// Standard view — Pro (responsive layout + animations)
			'countdown_standart_display_inline',   // Fit on one row
			'countdown_standart_gorup_animation',  // Set animation for each element or for group
			'countdown_standart_animation_type',   // Animation Effect
		);
	}

	/**
	 * Groups that are entirely Pro (every field inside them is Pro).
	 */
	public static function pro_groups() {
		return array( 'vertical_countdown', 'circle_countdown', 'countdown_flip_settings' );
	}

	/**
	 * Add `pro => true` to every Pro-only field so description_cell() renders
	 * the (pro) badge and the shared JS lock intercepts interactions.
	 */
	private static function mark_pro_fields( array $options ) {
		$pro_keys   = self::pro_field_keys();
		$pro_groups = self::pro_groups();
		foreach ( $options as $gk => $group ) {
			if ( empty( $group['params'] ) ) continue;
			$whole_group_pro = in_array( $gk, $pro_groups, true );
			foreach ( $group['params'] as $pk => $field ) {
				if ( $whole_group_pro || in_array( $pk, $pro_keys, true ) ) {
					$options[ $gk ]['params'][ $pk ]['pro'] = true;
				}
			}
		}
		return $options;
	}
	
	public static function return_params_array(){	
		return array(
			"countdown_theme_general"=>array(
				"heading_name"=>"General Settings",
				"params"=>array(
					"countdown_type"=>array(
						"title"=>"Countdown design type",
						"description"=>"Select the countdown design type",				
						"function_name"=>"simple_select",
						"values"=>WPDA_COUNTDOWN_IS_PRO?array("standart"=>"Standard","vertical"=>"Vertical","circle"=>"Circle","flip"=>"Flip Clock"):array("standart"=>"Standard","vertical"=>"Vertical (Pro)","circle"=>"Circle (Pro)","flip"=>"Flip Clock (Pro)"),
						"default_value"=>"standart",
					),
					"countdown_global_width"=>array(
						"title"=>"Countdown width",
						"description"=>"Set the countdown container width and unit",
						"function_name"=>"input_with_unit",
						"unit_name"=>"countdown_global_width_metrick",
						"unit_default"=>"%",
						"units"=>array("px"=>"px","%"=>"%"),
						"default_value"=>"100",
					),
					"countdown_global_width_metrick"=>array(
						"title"=>"",
						"description"=>"",
						"function_name"=>"hidden_input",
						"default_value"=>"%",
					),
					"countdown_horizontal_position"=>array(
						"title"=>"Countdown position",
						"description"=>"Select the countdown position",				
						"function_name"=>"simple_select",
						"values"=>array("left"=>"Left","center"=>"Center","right"=>"Right"),
						"default_value"=>"center",
					),
					"countdown_date_display"=>array(
						"title"=>"Select the countdown timer fields you want to display",
						"description"=>"Slect the timer fields you want to display in front-end",
						"values"=>array(
							"week"=>"Weeks",
							"day"=>"Days",
							"hour"=>"Hours",
							"minut"=>"Minutes",
							"second"=>"Seconds",
						),
						"function_name"=>"simple_checkbox",
						"default_value"=>array("day","hour","minut","second"),
					),	
					"countdown_text_type"=>array(
						"title"=>"Type timer texts or use translation file",
						"description"=>"You can type texts manually or use WordPress standard translation method with .po and .mo files",				
						"function_name"=>"simple_select",
						"values"=>array("standart"=>"Type texts","po_mo"=>"Use .mo translation file"),
						"default_value"=>"po_mo",
					),
					"text_for_weeks"=>array(
						"title"=>"Text for weeks",
						"description"=>"Type text for weeks",				
						"function_name"=>"simple_input",
						"default_value"=>"Weeks",
					),
					"text_for_day"=>array(
						"title"=>"Text for days",
						"description"=>"Type text for days",				
						"function_name"=>"simple_input",
						"default_value"=>"Days",
					),
					"text_for_hour"=>array(
						"title"=>"Text for hours",
						"description"=>"Type text for hours",				
						"function_name"=>"simple_input",
						"default_value"=>"Hours",
					),
					"text_for_minute"=>array(
						"title"=>"Text for minutes",
						"description"=>"Type text for minutes",				
						"function_name"=>"simple_input",
						"default_value"=>"Minutes",
					),
					"text_for_second"=>array(
						"title"=>"Text for seconds",
						"description"=>"Type text for seconds",				
						"function_name"=>"simple_input",
						"default_value"=>"Seconds",
					),
					
				)
			),
			"Standart_countdown"=>array(
				"heading_name"=>"Standard countdown",
				"params"=>array(					
					"countdown_standart_elements_width"=>array(
						"title"=>"Countdown elements(fields) width",
						"description"=>"Type here the width of countdown timer elements(fields)",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"80",
					),
					"countdown_standart_elements_distance"=>array(
						"title"=>"Distance between countdown elements",
						"description"=>"Type here distance between countdown elements",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"5",
					),
					"countdown_standart_time_bg_color"=>array(
						"title"=>"Time(numbers) fields background color",
						"description"=>"Select time fields background color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),
					"countdown_standart_time_text_bg_color"=>array(
						"title"=>"Time text fields background color",
						"description"=>"Select time text fields(Weeks,Days,Hours,Minutes,Seconds) background color",
						"default_value"=>"#000000",
						"function_name"=>"color_input",
					),
					"countdown_standart_time_font_size"=>array(
						"title"=>"Time(numbers) font size",
						"description"=>"Type here time(numbers) font size",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"21",
					),
					"countdown_standart_time_text_font_size"=>array(
						"title"=>"Time text font size",
						"description"=>"Type here time text font size",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"11",
					),
			
					"countdown_standart_time_color"=>array(
						"title"=>"Time(numbers) color",
						"description"=>"Type here time(numbers) color",
						"default_value"=>"#000000",
						"function_name"=>"color_input",
					),
					"countdown_standart_time_text_color"=>array(
						"title"=>"Time text color",
						"description"=>"Type here time text color",
						"default_value"=>"#000000",
						"function_name"=>"color_input",
					),
					"countdown_standart_time_font_famely"=>array(
						"title"=>"Time(numbers) font family",
						"description"=>"Type here time(numbers) font family",				
						"function_name"=>"font_select",
						"values"=>Wpda_Countdown_Admin_Fields::font_choices(),
						"default_value"=>"Arial,Helvetica Neue,Helvetica,sans-serif",
					),
					"countdown_standart_time_text_font_famely"=>array(
						"title"=>"Time text font family",
						"description"=>"Type here time text font family",				
						"function_name"=>"font_select",
						"values"=>Wpda_Countdown_Admin_Fields::font_choices(),
						"default_value"=>"Arial,Helvetica Neue,Helvetica,sans-serif",
					),
					"countdown_standart_time_padding"=>array(
						"title"=>"Time(numbers) padding",
						"description"=>"Type here time(numbers) padding",				
						"function_name"=>"padding_margin_input",
						"values"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
						"default_value"=>array("top"=>"0","right"=>"5","bottom"=>"0","left"=>"5"),
					),
					"countdown_standart_time_text_padding"=>array(
						"title"=>"Time text padding",
						"description"=>"Type here time text padding",				
						"function_name"=>"padding_margin_input",
						"values"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
						"default_value"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
					),
					"countdown_standart_time_margin"=>array(
						"title"=>"Time(numbers) margin",
						"description"=>"Type here time(numbers) margin",				
						"function_name"=>"padding_margin_input",
						"values"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
						"default_value"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
					),
					"countdown_standart_time_text_margin"=>array(
						"title"=>"Time text margin",
						"description"=>"Type here time text margin",				
						"function_name"=>"padding_margin_input",
						"values"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
						"default_value"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
					),
					"countdown_standart_time_border_width"=>array(
						"title"=>"Time(numbers) fields border width",
						"description"=>"Type here time(numbers) fields border width",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"0",
					),
					"countdown_standart_time_text_border_width"=>array(
						"title"=>"Time text fields border width",
						"description"=>"Type here time text fields border width",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"0",
					),
					"countdown_standart_time_border_radius"=>array(
						"title"=>"Time(numbers) fields border radius",
						"description"=>"Type here time(numbers) fields border radius",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"0",
					),
					"countdown_standart_time_text_border_radius"=>array(
						"title"=>"Time text fields border radius",
						"description"=>"Type here time text fields border radius",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"0",
					),
					"countdown_standart_time_border_color"=>array(
						"title"=>"Time(numbers) fields border color",
						"description"=>"Type here time(numbers) fields border color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),
					"countdown_standart_time_text_border_color"=>array(
						"title"=>"Time text fields border color",
						"description"=>"Type here time text fields border color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),
					"countdown_standart_display_inline"=>array(
						"title"=>"Fit on one row",
						"description"=>"If elements would overflow the container, automatically scale them down so they fit on one row.",
						"function_name"=>"simple_select",
						"values"=>array("0"=>"Original sizes","1"=>"Scale to fit row"),
						"default_value"=>"0",
					),
					"countdown_standart_gorup_animation"=>array(
						"title"=>"Set animation for each element or for group",
						"description"=>"Select the animation type for elements",				
						"function_name"=>"simple_select",
						"values"=>array("group"=>"Group","one"=>"One element"),
						"default_value"=>"standart",
					),					
					"countdown_standart_animation_type"=>array(
						"title"=>"Animation Effect",
						"description"=>"Select the animation effect",				
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
				),
			),	
			"vertical_countdown"=>array(
				"heading_name"=>"Vertical countdown",
				"params"=>array(	
					"countdown_vertical_time_font_size"=>array(
						"title"=>"Time(numbers) font size",
						"description"=>"Type here time(numbers) font size",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"5",
					),
					"countdown_vertical_elements_distance"=>array(
						"title"=>"Distance between countdown elements",
						"description"=>"Type here distance between countdown elements",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"12",
						"small_text"=>"px"
					),
					"countdown_vertical_time_font_famely"=>array(
						"title"=>"Time(numbers) font family",
						"description"=>"Type here time(numbers) font family",				
						"function_name"=>"font_select",
						"values"=>Wpda_Countdown_Admin_Fields::font_choices(),
						"default_value"=>"Arial,Helvetica Neue,Helvetica,sans-serif",
					),
					"countdown_vertical_time_color"=>array(
						"title"=>"Time(numbers) color",
						"description"=>"Type here time(numbers) color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),
					"countdown_vertical_background_color"=>array(
						"title"=>"Time(numbers) fields background color",
						"description"=>"Type here time(numbers) fields background color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),	
					"countdown_vertical_time_border_width"=>array(
						"title"=>"Time(numbers) fields border width",
						"description"=>"Type here time(numbers) fields border width",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"1",
					),
					"countdown_vertical_time_border_color"=>array(
						"title"=>"Time(numbers) fields border color",
						"description"=>"Type here time(numbers) fields border color",
						"default_value"=>"#000000",
						"function_name"=>"color_input",
					),
					"countdown_vertical_time_text_bg_color"=>array(
						"title"=>"Time text fields background color",
						"description"=>"Select time text fields(Weeks,Days,Hours,Minutes,Seconds) background color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),
					
					"countdown_vertical_time_text_font_size"=>array(
						"title"=>"Time text font size",
						"description"=>"Type here time text font size",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"5",
					),		
					"countdown_vertical_time_text_color"=>array(
						"title"=>"Time text color",
						"description"=>"Type here time text color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),
					"countdown_vertical_time_text_font_famely"=>array(
						"title"=>"Time text font family",
						"description"=>"Select here time text font family",				
						"function_name"=>"font_select",
						"values"=>Wpda_Countdown_Admin_Fields::font_choices(),
						"default_value"=>"Arial,Helvetica Neue,Helvetica,sans-serif",
					),
					"countdown_vertical_time_text_padding"=>array(
						"title"=>"Time text padding",
						"description"=>"Type here time text padding",				
						"function_name"=>"padding_margin_input",
						"values"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
						"default_value"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
					),
					"countdown_vertical_time_text_margin"=>array(
						"title"=>"Time text margin",
						"description"=>"Type here time text margin",				
						"function_name"=>"padding_margin_input",
						"values"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
						"default_value"=>array("top"=>"0","right"=>"0","bottom"=>"0","left"=>"0"),
					),
					"countdown_vertical_time_text_border_width"=>array(
						"title"=>"Time text fields border width",
						"description"=>"Type here time text fields border width",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"0",
					),
					"countdown_vertical_time_text_border_radius"=>array(
						"title"=>"Time text fields border radius",
						"description"=>"Type here time text fields border radius",				
						"function_name"=>"simple_input",
						"type"=>"number",
						"default_value"=>"0",
					),
					"countdown_vertical_time_text_border_color"=>array(
						"title"=>"Time text fields border color",
						"description"=>"Type here time text fields border color",
						"default_value"=>"#FFFFFF",
						"function_name"=>"color_input",
					),	
					"countdown_vertical_display_inline"=>array(
						"title"=>"Fit on one row",
						"description"=>"If elements would overflow the container, automatically scale them down so they fit on one row.",
						"function_name"=>"simple_select",
						"values"=>array("0"=>"Original sizes","1"=>"Scale to fit row"),
						"default_value"=>"0",
					),
					"countdown_vertical_gorup_animation"=>array(
						"title"=>"Set animation for each element or for group",
						"description"=>"Select the animation type for elements",				
						"function_name"=>"simple_select",
						"values"=>array("group"=>"Group","one"=>"One element"),
						"default_value"=>"standart",
					),
					"countdown_vertical_animation_type"=>array(
						"title"=>"Animation Effect",
						"description"=>"Select the animation effect",				
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
				),
			),
			"circle_countdown"=>array(
				"heading_name"=>"Circle countdown",
				"params"=>array(
					"countdown_circle_elements_width_height"=>array(
						"title"=>"Circle size",
						"description"=>"Diameter of each circle in pixels",
						"function_name"=>"range_input",
						"min_value"=>"60",
						"max_value"=>"250",
						"default_value"=>"120",
						"small_text"=>"px",
					),
					"countdown_circle_elements_distance"=>array(
						"title"=>"Gap between circles",
						"description"=>"Space between each circle element",
						"function_name"=>"range_input",
						"min_value"=>"0",
						"max_value"=>"50",
						"default_value"=>"12",
						"small_text"=>"px",
					),
					"countdown_circle_background_color"=>array(
						"title"=>"Circle fill color",
						"description"=>"Background color inside the circle",
						"default_value"=>"#ffffff",
						"function_name"=>"color_input",
					),
					"countdown_circle_background_color_opacity"=>array(
						"title"=>"Circle fill opacity",
						"description"=>"Opacity of the circle fill (0 = transparent)",
						"function_name"=>"range_input",
						"small_text"=>"%",
						"default_value"=>"100",
					),
					"countdown_circle_border_color_outside"=>array(
						"title"=>"Track color",
						"description"=>"Color of the ring background track",
						"default_value"=>"#e0e0e0",
						"function_name"=>"color_input",
					),
					"countdown_circle_border_color_inside"=>array(
						"title"=>"Progress color",
						"description"=>"Color of the ring progress arc",
						"default_value"=>"#2271b1",
						"function_name"=>"color_input",
					),
					"countdown_circle_width_parcents"=>array(
						"title"=>"Ring thickness",
						"description"=>"Thickness of the progress ring as percentage of circle size",
						"function_name"=>"range_input",
						"min_value"=>"1",
						"max_value"=>"30",
						"small_text"=>"%",
						"default_value"=>"8",
					),
					"countdown_circle_type_of_rounding"=>array(
						"title"=>"Ring end caps",
						"description"=>"Shape of the progress ring endpoints",
						"function_name"=>"simple_select",
						"values"=>array("butt"=>"Flat","round"=>"Rounded"),
						"default_value"=>"round",
					),
					"countdown_circle_border_direction"=>array(
						"title"=>"Progress direction",
						"description"=>"Which direction the ring fills",
						"function_name"=>"simple_select",
						"values"=>array("right"=>"Clockwise","left"=>"Counter-clockwise"),
						"default_value"=>"right",
					),
					"countdown_circle_time_font_size"=>array(
						"title"=>"Number font size",
						"description"=>"Font size for the countdown digits",
						"function_name"=>"range_input",
						"min_value"=>"10",
						"max_value"=>"60",
						"default_value"=>"28",
						"small_text"=>"px",
					),
					"countdown_circle_time_text_font_size"=>array(
						"title"=>"Label font size",
						"description"=>"Font size for the labels (Days, Hours, etc.)",
						"function_name"=>"range_input",
						"min_value"=>"8",
						"max_value"=>"30",
						"default_value"=>"12",
						"small_text"=>"px",
					),
					"countdown_circle_time_color"=>array(
						"title"=>"Number color",
						"description"=>"Color of the countdown digits",
						"default_value"=>"#1d2327",
						"function_name"=>"color_input",
					),
					"countdown_circle_time_text_color"=>array(
						"title"=>"Label color",
						"description"=>"Color of the labels",
						"default_value"=>"#50575e",
						"function_name"=>"color_input",
					),
					"countdown_circle_time_font_famely"=>array(
						"title"=>"Font family",
						"description"=>"Font for numbers and labels",
						"function_name"=>"font_select",
						"values"=>Wpda_Countdown_Admin_Fields::font_choices(),
						"default_value"=>"Arial,Helvetica Neue,Helvetica,sans-serif",
					),
					"countdown_circle_display_inline"=>array(
						"title"=>"Fit on one row",
						"description"=>"If circles would overflow the container, automatically scale them down so they fit on one row.",
						"function_name"=>"simple_select",
						"values"=>array("0"=>"Original sizes","1"=>"Scale to fit row"),
						"default_value"=>"0",
					),
					"countdown_circle_gorup_animation"=>array(
						"title"=>"Set animation for each element or for group",
						"description"=>"Select the animation type for elements",
						"function_name"=>"simple_select",
						"values"=>array("group"=>"Group","one"=>"One element"),
						"default_value"=>"group",
					),
					"countdown_circle_animation_type"=>array(
						"title"=>"Animation Effect",
						"description"=>"Select the animation effect",				
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
				),
			),
			"countdown_flip_settings"=>array(
				"heading_name"=>"Flip Clock",
				"params"=>array(
					"countdown_flip_card_bg"=>array(
						"title"=>"Card background",
						"description"=>"Background color of the flip cards",
						"function_name"=>"color_input",
						"default_value"=>"#1d2327",
					),
					"countdown_flip_card_color"=>array(
						"title"=>"Card text color",
						"description"=>"Digit color on the flip cards",
						"function_name"=>"color_input",
						"default_value"=>"#ffffff",
					),
					"countdown_flip_label_color"=>array(
						"title"=>"Label color",
						"description"=>"Color of the labels below the cards (Days, Hours, etc.)",
						"function_name"=>"color_input",
						"default_value"=>"#50575e",
					),
					"countdown_flip_card_width"=>array(
						"title"=>"Card width",
						"description"=>"Width of each flip card in pixels",
						"function_name"=>"range_input",
						"min_value"=>"40",
						"max_value"=>"150",
						"default_value"=>"70",
						"small_text"=>"px",
					),
					"countdown_flip_card_height"=>array(
						"title"=>"Card height",
						"description"=>"Height of each flip card in pixels",
						"function_name"=>"range_input",
						"min_value"=>"40",
						"max_value"=>"180",
						"default_value"=>"80",
						"small_text"=>"px",
					),
					"countdown_flip_font_size"=>array(
						"title"=>"Digit font size",
						"description"=>"Font size of the digits in pixels",
						"function_name"=>"range_input",
						"min_value"=>"16",
						"max_value"=>"80",
						"default_value"=>"36",
						"small_text"=>"px",
					),
					"countdown_flip_gap"=>array(
						"title"=>"Gap between cards",
						"description"=>"Space between flip cards in pixels",
						"function_name"=>"range_input",
						"min_value"=>"4",
						"max_value"=>"40",
						"default_value"=>"12",
						"small_text"=>"px",
					),
					"countdown_flip_border_radius"=>array(
						"title"=>"Card border radius",
						"description"=>"Corner roundness of the flip cards",
						"function_name"=>"range_input",
						"min_value"=>"0",
						"max_value"=>"20",
						"default_value"=>"8",
						"small_text"=>"px",
					),
					"countdown_flip_font_family"=>array(
						"title"=>"Font family",
						"description"=>"Font for the digits",
						"function_name"=>"font_select",
						"values"=>Wpda_Countdown_Admin_Fields::font_choices(),
						"default_value"=>"Arial,Helvetica Neue,Helvetica,sans-serif",
					),
					"countdown_flip_display_inline"=>array(
						"title"=>"Fit on one row",
						"description"=>"If flip cards would overflow the container, automatically scale them down so they fit on one row.",
						"function_name"=>"simple_select",
						"values"=>array("0"=>"Original sizes","1"=>"Scale to fit row"),
						"default_value"=>"0",
					),
					"countdown_flip_gorup_animation"=>array(
						"title"=>"Set animation for each element or for group",
						"description"=>"Select the animation type for elements",
						"function_name"=>"simple_select",
						"values"=>array("group"=>"Group","one"=>"One element"),
						"default_value"=>"group",
					),
					"countdown_flip_animation_type"=>array(
						"title"=>"Animation Effect",
						"description"=>"Select the animation effect",
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
				),
			),
		);
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
	public function controller_page(){
		$task = isset( $_GET['task'] ) ? sanitize_text_field( $_GET['task'] ) : 'default';
		$id   = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		switch ( $task ) {
			case 'add_wpda_countdown_theme':
			case 'add_edit_theme':
				$this->add_edit_theme( $id );
				break;

			case 'save_theme':
				$this->save_theme( $id );
				$this->display_table_list_theme();
				break;

			case 'update_theme':
				$result_id = $this->save_theme( $id );
				if ( ! $id && $result_id ) $id = $result_id;
				$this->add_edit_theme( $id );
				break;

			case 'set_default_theme':
				// Default-setting already handled by AdminMenu::process_theme_actions() before render
				$this->display_table_list_theme();
				break;

			case 'remove_theme':
				// Delete already handled by AdminMenu::process_theme_actions() before render
				$this->display_table_list_theme();
				break;

			default:
				$this->display_table_list_theme();
		}
	}
	
/*############  Save / Update (unified via Repository) ################*/

	private function save_theme( $id = 0 ) {
		if ( count( $_POST ) === 0 ) return 0;
		if ( ! isset( $_POST['wpda_theme_nonce'] ) || ! wp_verify_nonce( $_POST['wpda_theme_nonce'], 'wpda_theme_save' ) ) return 0;

		$name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : 'theme';
		if ( $name === '' || $name === '0' ) $name = 'Unnamed';

		// Free mode: Pro fields render but are locked from editing. Load the
		// existing theme data so we can preserve all Pro field values (spacing,
		// sizing, vertical/circle/flip settings) across a free-mode save.
		$existing = ( $id && ! WPDA_COUNTDOWN_IS_PRO ) ? ( $this->repo->find_data( $id ) ?: array() ) : array();
		$pro_keys   = self::pro_field_keys();
		$pro_groups = self::pro_groups();

		$data = array( 'name' => $name );
		foreach ( $this->options as $gk => $group ) {
			if ( empty( $group['params'] ) ) continue;
			$whole_group_pro = in_array( $gk, $pro_groups, true );
			foreach ( $group['params'] as $key => $field ) {
				$is_pro_field = ! WPDA_COUNTDOWN_IS_PRO && ( $whole_group_pro || in_array( $key, $pro_keys, true ) );
				if ( $is_pro_field ) {
					// Keep the stored value (or fall back to the field default)
					$data[ $key ] = isset( $existing[ $key ] ) ? $existing[ $key ] : $field['default_value'];
					continue;
				}
				$data[ $key ] = isset( $_POST[ $key ] )
					? Wpda_Countdown_Admin_Fields::sanitize_field_value( $field, $_POST[ $key ] )
					: $field['default_value'];
			}
		}

		$result_id = $this->repo->save( $id, $name, $data );
		if ( $result_id ) {
			echo '<div class="updated"><p><strong>Item Saved</strong></p></div>';
		} else {
			echo '<div id="message" class="error"><p>Error please reinstall plugin</p></div>';
		}
		return $result_id;
	}
	
	
	private function display_table_list_theme(){
		// Output fresh list data so the table reflects any save/delete
		// that happened earlier in this request (enqueue hook localized
		// data BEFORE the save, so JS would otherwise see a stale snapshot).
		echo '<script>window.wpdaPageRowsList = ' . wp_json_encode( $this->get_row_list() ) . ';</script>';

		$params = array(
			'name'         => 'Themes',
			'add_new_link' => 'admin.php?page=wpda_countdown_themes&task=add_wpda_countdown_theme',
			'support_link' => wpdevart_countdown_support_url,
		);
		include wpda_countdown_plugin_path . 'includes/admin/Views/list-header.php';
	}

	public function get_row_list(){
		$rows = $this->repo->all();
		$type_labels=array('standart'=>'Standard','vertical'=>'Vertical','circle'=>'Circle','flip'=>'Flip Clock');
		$result = array();
		foreach($rows as $row){
			$data=json_decode($row->option_value,true);
			$type=isset($data['countdown_type'])?$data['countdown_type']:'standart';
			$result[] = array('id' => $row->id, 'name' => strip_tags($row->name), 'design' => isset($type_labels[$type])?$type_labels[$type]:$type, 'default' => $row->default);
		}
		return $result;
	}

	public function get_table_info(){
		$nonce = wp_create_nonce( 'wpda_theme_action' );
		return array(
			'keys' => array(
				'id'      => array( 'name' => 'ID',     'sortable' => true ),
				'name'    => array( 'name' => 'Name',   'link' => '&task=add_edit_theme', 'sortable' => true ),
				'design'  => array( 'name' => 'Design', 'sortable' => true ),
				'default' => array( 'name' => 'Default', 'link' => '&task=set_default_theme&_wpnonce=' . $nonce, 'replace_value' => array(
					'1' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="#f0b849" stroke="#f0b849" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
					'0' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c3c4c7" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
				) ),
				'edit'    => array( 'name' => 'Edit',   'link' => '&task=add_edit_theme' ),
				'delete'  => array( 'name' => 'Delete', 'link' => '&task=remove_theme&_wpnonce=' . $nonce ),
			),
			'link_page' => 'wpda_countdown_themes',
		);
	}
	
	private function generete_theme_parametrs($id=0){
		$theme_params = null;
		$new_theme = 1;
		if ( $id ) {
			$theme_params = $this->repo->find( $id );
			$new_theme = 0;
		} else {
			$theme_params = $this->repo->find_default();
		}
		if($theme_params==NULL){			
			foreach($this->options as $param_heading_key=>$param_heading_value){
				foreach($param_heading_value['params'] as $key=>$value){
					$this->options[$param_heading_key]['params'][$key]["value"]=$this->options[$param_heading_key]['params'][$key]["default_value"];
				}
			}
		}else{
			$databases_parametrs=json_decode($theme_params->option_value, true);
			foreach($this->options as $param_heading_key=>$param_heading_value){
				foreach($param_heading_value['params'] as $key=>$value){
					if(isset($databases_parametrs[$key])){
						$this->options[$param_heading_key]['params'][$key]["value"]=$databases_parametrs[$key];
					}else{
						$this->options[$param_heading_key]['params'][$key]["value"]=$this->options[$param_heading_key]['params'][$key]["default_value"];
					}
				}
			}
			if($new_theme){
				return "New Theme";
			}else{
				return $theme_params->name;
			}
		}
	}
	
	
	private function add_edit_theme( $id = 0 ) {
		$name = $this->generete_theme_parametrs( $id );
		?>
		<form action="admin.php?page=wpda_countdown_themes<?php if($id) echo '&id='.$id; ?>" method="post" name="adminForm" class="top_description_table" id="adminForm">
			<?php wp_nonce_field('wpda_theme_save','wpda_theme_nonce'); ?>
            <div class="conteiner">
                <?php Wpda_Countdown_Admin_Fields::render_pro_banner(); ?>
                <div class="header">
                    <span><h2 class="wpda_theme_title"><?php echo $id?"Edit":"Add" ?> Theme</h2></span>
                    <div class="header_action_buttons">
                        <span><input type="button" onclick="submitbutton('save_theme')" value="Save" class="button-primary action"> </span>
                        <span><input type="button" onclick="submitbutton('update_theme')" value="Apply" class="button-primary action"> </span>
                        <span><input type="button" onclick="window.location.href='admin.php?page=wpda_countdown_themes'" value="Cancel" class="button-secondary action"> </span>
                    </div>
                </div>
                <div id="wpda_theme_preview_wrap" style="margin:12px 0;padding:20px;background:#f0f0f1;border:1px solid #ddd;border-radius:8px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <span style="font-weight:600;font-size:13px;color:#1d2327;">Preview</span>
                        <button type="button" id="wpda_refresh_preview" class="button button-small">Refresh Preview</button>
                    </div>
                    <div id="wpda_theme_preview" style="padding:20px;border-radius:6px;text-align:center;background:#fff;min-height:60px;display:flex;align-items:center;justify-content:center;"></div>
                </div>
                <div class="option_panel">
                    <div class="parametr_name"></div>
                    <div class="all_options_panel">
                        <input type="text" class="theme_name" name="name" placeholder="Enter name here" value="<?php echo esc_attr( isset( $name ) ? $name : '' ); ?>">
                        <div class="wpda_theme_link_tabs">
							<?php
								$type_map=array('standart'=>'Standart_countdown','vertical'=>'vertical_countdown','circle'=>'circle_countdown','flip'=>'countdown_flip_settings');
								$cur_type=isset($this->options['countdown_theme_general']['params']['countdown_type']['value'])?$this->options['countdown_theme_general']['params']['countdown_type']['value']:'standart';
								$active_type_tab=isset($type_map[$cur_type])?$type_map[$cur_type]:'Standart_countdown';
								echo '<ul>';
								foreach ( $this->options as $params_grup_name => $params_group_value ) {
									$is_type_tab    = in_array( $params_grup_name, $type_map );
									$is_active_type = ( $params_grup_name === $active_type_tab );
									$cls            = ( $is_type_tab && ! $is_active_type ) ? 'wpda_type_tab' : 'wpda_type_tab wpda_type_tab_visible';
									if ( ! $is_type_tab ) $cls = '';
									echo '<li id="' . esc_attr( $params_grup_name ) . '_tab" class="' . esc_attr( $cls ) . '">' . esc_html( $params_group_value['heading_name'] ) . '</li>';
								}
								echo '</ul>';
							?>
						</div>
                        <table>
						<?php 
						foreach($this->options as $params_grup_name =>$params_group_value){ 
							Wpda_Countdown_Admin_Fields::heading($params_group_value['heading_name'],$params_grup_name);
							foreach($params_group_value['params'] as $param_name => $param_value){
								$args=array(
									"name"=>$param_name,
									"heading_name"=>$params_group_value['heading_name'],
									"heading_group"=>$params_grup_name,
								);
								$args=array_merge($args,$param_value);
								if($param_value['function_name']==='input_with_unit'&&isset($param_value['unit_name'])){
									$un=$param_value['unit_name'];
									if(isset($params_group_value['params'][$un]['value']))
										$args['unit_value']=$params_group_value['params'][$un]['value'];
									elseif(isset($param_value['unit_default']))
										$args['unit_value']=$param_value['unit_default'];
								}
								$function_name=$param_value['function_name'];
								Wpda_Countdown_Admin_Fields::$function_name($args);
							}
						}

						?>
					</table>
                    </div>
                </div>
            </div>
		</form>
		<?php

		 
}
}
