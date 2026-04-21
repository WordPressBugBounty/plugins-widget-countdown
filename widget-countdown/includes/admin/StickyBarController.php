<?php
if ( ! defined( "ABSPATH" ) ) exit;
class wpda_countdown_sticky_bar_page{
	private $options;
	private $abstract_options;
	private static $option_key='wpda_countdown_sticky_bar_params';
	private static $enable_key='wpda_countdown_sticky_bar_enable';
	private static $text_parametrs=array('parametrs_sucsses_saved'=>'Successfully saved.','authorize_problem'=>'Authorization Problem');

	function __construct(){
		$this->options=self::return_params_array();
		$this->generete_settings();
		$this->generete_abstract_params_list();
	}

	public static function return_params_array(){
		$timers_array = array( '0' => '— Select Timer —' ) + wpda_countdown()->timer_repository()->all_names();
		$themes_array = array( '0' => '— Select Theme —' ) + wpda_countdown()->theme_repository()->all_names();
		$params = array(
			"sticky_general"=>array(
				"heading_name"=>"General",
				"location"=>"left",
				"params"=>array(
					"sticky_position"=>array(
						"title"=>"Position",
						"description"=>"Where to show the sticky bar on the page",
						"function_name"=>"simple_select",
						"values"=>array("top"=>"Top of page","bottom"=>"Bottom of page"),
						"default_value"=>"top",
					),
					"sticky_show_action"=>array(
						"title"=>"Show/Hide from below list",
						"description"=>"Choose to show the sticky bar only on selected pages, or hide it from selected pages.",
						"values"=>array("show"=>"Show on selected pages","hide"=>"Hide from selected pages"),
						"default_value"=>"hide",
						"function_name"=>"simple_select",
					),
					"sticky_show_on_multiple"=>array(
						"title"=>"Select the list",
						"description"=>"Click on the field and then choose something from list.",
						"default_value"=>array(),
						"function_name"=>"multiplay_select",
					),
					"sticky_hide_after_expire"=>array(
						"title"=>"Hide sticky bar after countdown expires",
						"description"=>"Automatically hide the sticky bar when the countdown reaches zero. Useful for time-limited events.",
						"values"=>array(
							"no"=>"No — keep sticky bar visible",
							"yes"=>"Yes — hide when expired",
						),
						"default_value"=>"no",
						"function_name"=>"simple_select",
					),
				),
			),
			"sticky_countdown"=>array(
				"heading_name"=>"Countdown",
				"location"=>"left",
				"params"=>array(
					"sticky_timer_id"=>array(
						"title"=>"Countdown timer",
						"description"=>"Select the countdown timer",
						"function_name"=>"simple_select",
						"values"=>$timers_array,
						"default_value"=>"0",
					),
					"sticky_theme_id"=>array(
						"title"=>"Countdown theme",
						"description"=>"Select the countdown theme",
						"function_name"=>"simple_select",
						"values"=>$themes_array,
						"default_value"=>"0",
					),
				),
			),
			"sticky_message_section"=>array(
				"heading_name"=>"Message",
				"location"=>"left",
				"params"=>array(
					"sticky_message_position"=>array(
						"title"=>"Message position",
						"description"=>"Show the message on the left or right side of the countdown",
						"function_name"=>"simple_select",
						"values"=>array("left"=>"Left of countdown","right"=>"Right of countdown"),
						"default_value"=>"left",
					),
					"sticky_message"=>array(
						"title"=>"Message text",
						"description"=>"Text shown next to the countdown (e.g. 'Sale ends in'). Leave empty for just the timer.",
						"function_name"=>"tinmce",
						"default_value"=>"",
					),
				),
			),
			"sticky_appearance"=>array(
				"heading_name"=>"Appearance",
				"location"=>"right",
				"params"=>array(
					"sticky_bg_color"=>array(
						"title"=>"Background color",
						"description"=>"Background color of the sticky bar",
						"function_name"=>"color_input",
						"default_value"=>"#1d2327",
					),
					"sticky_text_color"=>array(
						"title"=>"Text color",
						"description"=>"Text color of the sticky bar message and countdown labels",
						"function_name"=>"color_input",
						"default_value"=>"#ffffff",
					),
				),
			),
			"sticky_close_button"=>array(
				"heading_name"=>"Close Button",
				"location"=>"right",
				"params"=>array(
					"sticky_closable"=>array(
						"title"=>"Show close button",
						"description"=>"Show or hide the close button",
						"function_name"=>"simple_select",
						"values"=>array("yes"=>"Show","no"=>"Hide"),
						"default_value"=>"yes",
					),
					"sticky_close_icon"=>array(
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
					"sticky_close_position"=>array(
						"title"=>"Close button position",
						"description"=>"Select close button position",
						"function_name"=>"simple_select",
						"values"=>array("right"=>"Right","left"=>"Left"),
						"default_value"=>"right",
					),
					"sticky_close_color"=>array(
						"title"=>"Close button color",
						"description"=>"Color of the close button icon",
						"function_name"=>"color_input",
						"default_value"=>"#ffffff",
					),
					"sticky_close_bg_color"=>array(
						"title"=>"Close button background",
						"description"=>"Background color of the close button circle",
						"function_name"=>"color_input",
						"default_value"=>"#000000",
					),
					"sticky_close_bg_opacity"=>array(
						"title"=>"Close button background opacity",
						"description"=>"Set the close button background opacity",
						"default_value"=>"15",
						"small_text"=>"(%)",
						"min_value"=>"0",
						"max_value"=>"100",
						"function_name"=>"range_input",
					),
					"sticky_close_size"=>array(
						"title"=>"Close button size",
						"description"=>"Size of the close button in pixels",
						"function_name"=>"simple_input",
						"type"=>"number",
						"small_text"=>"(px)",
						"default_value"=>"22",
					),
				),
			),
		);

		// Sticky bar is a Pro feature — in the free build every field is marked (pro).
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

	public static function get_settings(){
		$saved=json_decode(get_option(self::$option_key,'{}'),true);
		if(!is_array($saved)) $saved=array();
		$defaults=self::get_default_values_array();
		return array_merge($defaults,$saved);
	}

	public static function is_enabled(){
		return get_option(self::$enable_key,'off')==='on';
	}

	public static function get_default_values_array(){
		$array=array();
		$options=self::return_params_array();
		foreach($options as $group){
			foreach($group['params'] as $key=>$val){
				$array[$key]=$val['default_value'];
			}
		}
		return $array;
	}

	public static function save_in_db(){
		$params_array=self::return_params_array();
		$db_params=json_decode(get_option(self::$option_key,'{}'),true);
		if(!is_array($db_params)) $db_params=array();

		if(isset($_POST['curent_page'])&&isset($_POST['sticky_bar_options_nonce'])&&wp_verify_nonce($_POST['sticky_bar_options_nonce'],'sticky_bar_options_nonce')){
			if($_POST['curent_page']=="general_save_parametr" && isset($_POST['sticky_bar_page_mode'])){
				update_option(self::$enable_key,sanitize_text_field($_POST['sticky_bar_page_mode']));
			} elseif ( isset( $params_array[ $_POST['curent_page'] ]['params'] ) ) {
				foreach ( $params_array[ $_POST['curent_page'] ]['params'] as $key => $value ) {
					if ( isset( $_POST[ $key ] ) ) {
						$db_params[ $key ] = Wpda_Countdown_Admin_Fields::sanitize_field_value( $value, $_POST[ $key ] );
					} elseif ( $value['function_name'] === 'multiplay_select' ) {
						$db_params[ $key ] = array();
					}
				}
				update_option( self::$option_key, wp_json_encode( $db_params ) );
			}
		}else{
			die(self::$text_parametrs['authorize_problem']);
		}
		die(self::$text_parametrs['parametrs_sucsses_saved']);
	}

	private function generete_settings(){
		$db_params=json_decode(get_option(self::$option_key,'{}'),true);
		if(!is_array($db_params)) $db_params=array();
		foreach($this->options as $gk=>$gv){
			foreach($gv['params'] as $key=>$val){
				$this->options[$gk]['params'][$key]['value']=isset($db_params[$key])?$db_params[$key]:$val['default_value'];
			}
		}
	}

	private function generete_abstract_params_list(){
		$abstract=array();
		foreach($this->options as $gk=>$gv){
			foreach($gv['params'] as $key=>$val){
				$abstract[$gk][$key]=is_array($val['default_value'])?wp_json_encode($val['default_value']):$val['default_value'];
			}
		}
		$this->abstract_options=$abstract;
	}

	public function controller_page(){
		$enable_disable=get_option(self::$enable_key,"off");
		$locked = WPDA_COUNTDOWN_IS_PRO ? '' : ' wpda_pro_locked_page';
		?>
		<script>
			var sticky_bar_ajaxurl="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>";
			var sticky_bar_plugin_url="<?php echo esc_url( wpda_countdown_plugin_url ); ?>";
			var sticky_bar_parametrs_sucsses_saved="<?php echo esc_js( self::$text_parametrs['parametrs_sucsses_saved'] ); ?>";
			var sticky_bar_all_parametrs=<?php echo wp_json_encode($this->abstract_options); ?>;
		</script>
		<?php Wpda_Countdown_Admin_Fields::render_pro_banner(); ?>
		<div class="wpda_sticky_bar_page<?php echo esc_attr( $locked ); ?>">
		<div class="countdown_title"><h1><span><svg class="wpda_section_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17v5"/><path d="M5 17h14v-1.76a2 2 0 00-1.11-1.79l-1.78-.9A2 2 0 0115 10.76V6h1a2 2 0 000-4H8a2 2 0 000 4h1v4.76a2 2 0 01-1.11 1.79l-1.78.9A2 2 0 005 15.24z"/></svg>Sticky Bar</span></h1></div>
		<div id="sticky_bar_enable" class="field switch">
			<label for="radio1" class="cb-enable <?php if($enable_disable=='on') echo 'selected'; ?>"><span>Enable</span></label>
			<label for="radio2" class="cb-disable <?php if($enable_disable=='off') echo 'selected'; ?>"><span>Disable</span></label>
			<span class="progress_enable_disable_buttons"><span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span><span class="error_massage"></span></span>
			<div style="clear:both"></div><br>
		</div>
		<div class="wp-table right_margin">
			<table class="wp-list-table widefat fixed posts">
				<thead><tr><th>
					<h4 class="params_subtitle">Settings</h4>
					<span class="save_all_paramss"><button type="button" id="save_all_parametrs" class="save_all_section_parametrs button button-primary"><span class="save_button_span">Save All Sections</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button></span>
				</th></tr></thead>
				<tbody><tr><td>
					<div id="sticky_bar_page">
						<div class="left_sections">
						<?php foreach($this->options as $gk=>$gv){ if($gv['location']!='right'){ ?>
							<div class="main_parametrs_group_div closed_params">
								<div class="head_panel_div" title="Click to toggle">
									<span class="title_parametrs_group"><?php echo esc_html( $gv['heading_name'] ); ?></span>
									<span class="open_or_closed"></span>
								</div>
								<div class="inside_information_div">
									<table class="wp-list-table widefat fixed posts section_parametrs_table"><tbody>
									<?php foreach($gv['params'] as $pk=>$pv){
										$args=array_merge(array('name'=>$pk,'heading_name'=>$gv['heading_name'],'heading_group'=>$gk),$pv);
										Wpda_Countdown_Admin_Fields::{$pv['function_name']}($args);
									} ?>
									</tbody><tfoot><tr>
										<th colspan="2"><button type="button" id="<?php echo esc_attr( $gk ); ?>" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
									</tr></tfoot></table>
								</div>
							</div>
						<?php }} ?>
						</div>
						<div class="right_sections">
						<?php foreach($this->options as $gk=>$gv){ if($gv['location']==='right'){ ?>
							<div class="main_parametrs_group_div closed_params">
								<div class="head_panel_div" title="Click to toggle">
									<span class="title_parametrs_group"><?php echo esc_html( $gv['heading_name'] ); ?></span>
									<span class="open_or_closed"></span>
								</div>
								<div class="inside_information_div">
									<table class="wp-list-table widefat fixed posts section_parametrs_table"><tbody>
									<?php foreach($gv['params'] as $pk=>$pv){
										$args=array_merge(array('name'=>$pk,'heading_name'=>$gv['heading_name'],'heading_group'=>$gk),$pv);
										Wpda_Countdown_Admin_Fields::{$pv['function_name']}($args);
									} ?>
									</tbody><tfoot><tr>
										<th colspan="2"><button type="button" id="<?php echo esc_attr( $gk ); ?>" class="save_section_parametrs button button-primary"><span class="save_button_span">Save Section</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button><span class="error_massage"> </span></th>
									</tr></tfoot></table>
								</div>
							</div>
						<?php }} ?>
						</div>
					</div>
					<div style="clear:both"></div>
				</td></tr></tbody>
				<tfoot><tr><th>
					<span class="save_all_paramss"><button type="button" id="save_all_parametrs" class="save_all_section_parametrs button button-primary"><span class="save_button_span">Save All Sections</span> <span class="saving_in_progress"> </span><span class="sucsses_save"> </span><span class="error_in_saving"> </span></button></span>
				</th></tr></tfoot>
			</table>
		</div>
		<?php wp_nonce_field('sticky_bar_options_nonce','sticky_bar_options_nonce'); ?>
		<script>
		(function(){
			var icons={
				'General':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>',
				'Countdown':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
				'Message':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>',
				'Appearance':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
				'Close Button':'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>',
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
		</div><!-- /.wpda_sticky_bar_page -->
		<?php
	}
}
