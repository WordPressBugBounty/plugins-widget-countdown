<?php
if ( ! defined( "ABSPATH" ) ) exit;
class wpda_countdown_timer_page{

	private $options;
	private $repo;

	function __construct( $repo = null ){
		$this->repo = $repo ?: new Wpda_Countdown_Timer_Repository();
		$this->options=self::return_params_array();
	}
	
	public static function return_params_array(){	
		return array(
			"timer_set_time"=>array(
				"heading_name"=>"Set Time",
				"params"=>array(
					"timer_coundown_type"=>array(
						"title"=>"Timer type",
						"description"=>"Choose the timer type. Countdown counts down to a date. Count up counts up from a date. Evergreen countdown starts fresh for each visitor and counts down from the duration you set. Evergreen count up works the same way but counts up.",
						"function_name"=>"simple_select",
						"values"=>WPDA_COUNTDOWN_IS_PRO
							? array("countdown"=>"Countdown timer","countup"=>"Count up timer","evergreen_countdown"=>"Evergreen countdown","evergreen_countup"=>"Evergreen count up")
							: array("countdown"=>"Countdown timer","countup"=>"Count up timer (Pro)","evergreen_countdown"=>"Evergreen countdown (Pro)","evergreen_countup"=>"Evergreen count up (Pro)"),
						"default_value"=>"countdown",
					),					
					"timer_seesion_time"=>array(
						"title"=>"Evergreen timer duration",
						"description"=>"Set how long the evergreen timer runs for each visitor",
						"function_name"=>"many_inputs",
						"values"=>array("day"=>"0","hour"=>"0","minute"=>"0"),
						"default_value"=>array("day"=>"0","hour"=>"0","minute"=>"1"),
						"type"=>array("day"=>"number","hour"=>"number","minute"=>"number"),
						"small_text"=>array("day"=>"(Days)","hour"=>"(Hours)","minute"=>"(Minutes)"),
					),
					"evergreen_restart"=>array(
						"title"=>"After evergreen expires",
						"description"=>"What happens when the evergreen timer reaches zero for a visitor. Restart immediately resets the countdown right away. Restart after delay waits a set period before showing a fresh timer. Don't restart means the timer stays expired.",
						"function_name"=>"simple_select",
						"values"=>array("none"=>"Don't restart","immediate"=>"Restart immediately","delay"=>"Restart after delay"),
						"default_value"=>"none",
					),
					"evergreen_restart_delay"=>array(
						"title"=>"Restart waiting period",
						"description"=>"How long to wait before showing a fresh evergreen timer to the same visitor after it expires",
						"function_name"=>"many_inputs",
						"values"=>array("hour"=>"0","minute"=>"0"),
						"default_value"=>array("hour"=>"24","minute"=>"0"),
						"type"=>array("hour"=>"number","minute"=>"number"),
						"small_text"=>array("hour"=>"(Hours)","minute"=>"(Minutes)"),
					),
					"evergreen_expire_mode"=>array(
						"title"=>"Evergreen expire mode",
						"description"=>"Duration: timer runs for the set duration from first visit. Daily at time: timer always expires at a specific time of day in the visitor's local timezone (e.g. midnight), creating natural daily urgency.",
						"function_name"=>"simple_select",
						"values"=>array("duration"=>"Fixed duration","daily_time"=>"Daily at specific time"),
						"default_value"=>"duration",
					),
					"evergreen_daily_expire_time"=>array(
						"title"=>"Expire at time of day",
						"description"=>"The timer will expire at this time every day in the visitor's local timezone. For example, set 23:59 to create a 'deal ends tonight' urgency.",
						"function_name"=>"time_input",
						"min_value"=>"00:00",
						"max_value"=>"23:59",
						"default_value"=>"23:59",
					),
					"timer_start_time"=>array(
						"title"=>"Start Date",
						"description"=>"Select the start date for count up timer.",
						"function_name"=>"calendar_input",
						"small_text"=>"(Date)",
						"default_value"=>date('d/m/Y', (time()-(24 * 60 * 60)))." 00:00",
					),
					"timer_end_date"=>array(
						"title"=>"End Date",
						"description"=>"Select the end date for countdown(count up) timer.",
						"function_name"=>"calendar_input",
						"small_text"=>"(Date)",
						"default_value"=>date('d/m/Y', (time()+(24 * 60 * 60)))." 00:00",
					),
					"timer_timezone"=>array(
						"title"=>"Time Zone",
						"description"=>"Select the timer Time Zone",
						"values"=>self::timezone_array(),
						"function_name"=>"simple_select",
						"default_value"=>((date_default_timezone_get()=="UTC")?"Europe/London":date_default_timezone_get()),
					),
					"timer_coundown_repeat"=>array(
						"title"=>"Repeat",
						"description"=>"How the timer should repeat after it reaches zero",
						"function_name"=>"simple_select",
						"values"=>WPDA_COUNTDOWN_IS_PRO?array("none"=>"Don't repeat","when_end"=>"Restart after countdown ends","hourly"=>"Every X hours","daily"=>"Daily","weekly"=>"Weekly","monthly"=>"Monthly"):array("none"=>"Don't repeat","when_end"=>"Restart after ends (Pro)","hourly"=>"Every X hours (Pro)","daily"=>"Daily (Pro)","weekly"=>"Weekly (Pro)","monthly"=>"Monthly (Pro)"),
						"default_value"=>"none",
					),
					"after_countdown_repeat_time"=>array(
						"title"=>"Countdown duration",
						"description"=>"How long the countdown actively runs in each cycle. For 'Restart after ends' this is the full cycle length. For 'Every X hours' this is how long the countdown runs within each interval (the rest is idle). If set to 0 for hourly mode, the countdown fills the entire interval.",
						"function_name"=>"many_inputs",
						"values"=>array("hour"=>"0","minute"=>"0"),
						"default_value"=>array("hour"=>"1","minute"=>"0"),
						"type"=>array("hour"=>"number","minute"=>"number"),
						"small_text"=>array("hour"=>"(Hours)","minute"=>"(Minutes)"),
					),
					"repeat_hourly_interval"=>array(
						"title"=>"Wait between repeats",
						"description"=>"How long to wait after the countdown ends before starting the next one. For example, 1-hour duration + 3-hour wait = countdown runs for 1 hour, pauses 3 hours, then runs again.",
						"function_name"=>"range_input",
						"min_value"=>'1',
						"max_value"=>'24',
						"default_value"=>'3',
						"small_text"=>'hour(s)',
					),
					"repeat_daily_quantity"=>array(
						"title"=>"Every",
						"description"=>"Timer restarts every X days",
						"function_name"=>"range_input",
						"min_value"=>'1',
						"max_value"=>'365',
						"default_value"=>'1',
						"small_text"=>'day(s)',
					),
					"repeat_weekly_days"=>array(
						"title"=>"Repeat on days",
						"description"=>"Select which days of the week the timer should run",
						"function_name"=>"checkbox_group",
						"values"=>array(
							"mon"=>"Mon","tue"=>"Tue","wed"=>"Wed","thu"=>"Thu","fri"=>"Fri","sat"=>"Sat","sun"=>"Sun",
						),
						"default_value"=>array("mon","tue","wed","thu","fri","sat","sun"),
					),
					"repeat_monthly_day"=>array(
						"title"=>"Day of month",
						"description"=>"Which day of the month the timer starts (1-28)",
						"function_name"=>"range_input",
						"min_value"=>'1',
						"max_value"=>'28',
						"default_value"=>'1',
						"small_text"=>'',
					),
					"repeat_countdown_start_time"=>array(
						"title"=>"Starts at",
						"description"=>"Time of day when the countdown begins",
						"function_name"=>"time_input",
						"min_value"=>'00:00',
						"max_value"=>'23:59',
						"default_value"=>'00:00',
					),
					"repeat_countdown_end_time"=>array(
						"title"=>"Ends at",
						"description"=>"Time of day when the countdown expires. The timer counts down from 'Starts at' to 'Ends at'.",
						"function_name"=>"time_input",
						"min_value"=>'00:00',
						"max_value"=>'23:59',
						"default_value"=>'23:59',
					),
					"repeat_end"=>array(
						"title"=>"Stop repeating",
						"description"=>"When to stop the repeat cycle",
						"function_name"=>"simple_select",
						"values"=>array("never"=>"Never (repeat forever)","after"=>"After X times","on_date"=>"On specific date"),
						"default_value"=>"never",
					),
					"repeat_ending_after"=>array(
						"title"=>"Stop after",
						"description"=>"How many times to repeat before stopping",
						"function_name"=>"range_input",
						"min_value"=>'1',
						"max_value"=>'365',
						"default_value"=>'10',
						"small_text"=>'times',
					),
					"repeat_ending_after_date"=>array(
						"title"=>"Stop on date",
						"description"=>"Repeating stops on this date",
						"function_name"=>"calendar_input",
						"small_text"=>"(Date)",
						"default_value"=>date('d/m/Y', (time()+(30 * 24 * 60 * 60)))." 00:00",
					),
					
				)
			),
			"after_timer"=>array(
				"heading_name"=>"After timer expires",
				"params"=>array(					
					"after_countdown_end_type"=>array(
						"title"=>"Action after timer expires",
						"description"=>"Select what happens when the countdown reaches zero",
						"function_name"=>"simple_select",
						"values"=>WPDA_COUNTDOWN_IS_PRO?array("hide"=>"Hide countdown","text"=>"Show text/HTML","redirect"=>"Redirect to URL","button"=>"Show button","hide_content"=>"Hide page content","show_content"=>"Show hidden content"):array("hide"=>"Hide countdown","text"=>"Show text/HTML","redirect"=>"Redirect (Pro)","button"=>"Show button (Pro)","hide_content"=>"Hide content (Pro)","show_content"=>"Show content (Pro)"),
						"default_value"=>"text",
					),
					"after_countdown_text"=>array(
						"title"=>"Text / HTML",
						"description"=>"Content to display after the timer expires",
						"function_name"=>"tinmce",
						"default_value"=>"<h3>After countdown text</h3>",
					),
					"after_countdown_redirect"=>array(
						"title"=>"Redirect URL",
						"description"=>"Visitors will be redirected to this URL when the timer expires",
						"function_name"=>"simple_input",
						"small_text"=>"(url)",
						"default_value"=>"",
					),
					"after_countdown_button_text"=>array(
						"title"=>"Button text",
						"description"=>"Text displayed on the button",
						"function_name"=>"simple_input",
						"default_value"=>"Shop Now",
					),
					"after_countdown_button_url"=>array(
						"title"=>"Button URL",
						"description"=>"Where the button links to",
						"function_name"=>"simple_input",
						"small_text"=>"(url)",
						"default_value"=>"",
					),
					"after_countdown_css_selector"=>array(
						"title"=>"CSS selector",
						"description"=>"jQuery CSS selector for the page element to hide or show (e.g. .my-offer-box or #special-deal). Use 'Hide page content' to hide this element when timer expires, or 'Show hidden content' to reveal it.",
						"function_name"=>"simple_input",
						"small_text"=>"(e.g. .my-class or #my-id)",
						"default_value"=>"",
					),		
				),
			),	
			"before_timer"=>array(
				"heading_name"=>"Before timer start",
				"params"=>array(
					"before_countup_start_type"=>array(
						"title"=>"Action before timer starts",
						"description"=>"What to show or do before the count up timer reaches its start date",
						"function_name"=>"simple_select",
						"values"=>array("none"=>"Hide timer","text"=>"Show text/HTML","redirect"=>"Redirect to URL"),
						"default_value"=>"text",
					),
					"before_countup_text"=>array(
						"title"=>"Text / HTML",
						"description"=>"Content to display before the timer starts",
						"function_name"=>"tinmce",
						"default_value"=>"<h3>Before countdown text</h3>",
					),
					"before_countup_redirect"=>array(
						"title"=>"Redirect URL",
						"description"=>"Redirect visitors to this URL before the timer starts. Useful for coming soon pages.",
						"function_name"=>"simple_input",
						"small_text"=>"(url)",
						"default_value"=>"",
					),
				),
			),
			"timer_text"=>array(
				"heading_name"=>"Text during the countdown",
				"aditional_heading_name"=>"Countdown top and bottom texts",
				"params"=>array(
					"top_countdown_show_html"=>array(
						"title"=>"Top text",
						"description"=>"Text/HTML shown above the countdown",
						"function_name"=>"tinmce",
						"default_value"=>"",
					),
					"bottom_countdown_show_html"=>array(
						"title"=>"Bottom text",
						"description"=>"Text/HTML shown below the countdown",
						"function_name"=>"tinmce",
						"default_value"=>"",
					),
				),
			),
				
		);
	}
	/**
	 * Parse a timer date stored as "d/m/Y H:i" to a UNIX timestamp.
	 * Returns 0 if the string is malformed.
	 */
	private static function parse_timer_date( $str ) {
		if ( ! is_string( $str ) || $str === '' ) return 0;
		$parts = explode( ' ', trim( $str ) );
		$d     = isset( $parts[0] ) ? explode( '/', $parts[0] ) : array();
		$t     = isset( $parts[1] ) ? explode( ':', $parts[1] ) : array( 0, 0 );
		if ( count( $d ) !== 3 ) return 0;
		return mktime( (int) $t[0], (int) ( isset( $t[1] ) ? $t[1] : 0 ), 0, (int) $d[1], (int) $d[0], (int) $d[2] );
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
			case 'add_wpda_countdown_timer':
			case 'add_edit_timer':
				$this->add_edit_timer( $id );
				break;

			case 'save_timer':
				$this->save_timer( $id );
				$this->display_table_list_timer();
				break;

			case 'update_timer':
				$result_id = $this->save_timer( $id );
				if ( ! $id && $result_id ) $id = $result_id;
				$this->add_edit_timer( $id );
				break;

			case 'remove_timer':
				// Delete already handled by AdminMenu::process_timer_actions() before render
				$this->display_table_list_timer();
				break;

			default:
				$this->display_table_list_timer();
		}
	}
	
/*############  Save / Update (unified via Repository) ################*/

	private function save_timer( $id = 0 ) {
		if ( count( $_POST ) === 0 ) return 0;
		if ( ! isset( $_POST['wpda_timer_nonce'] ) || ! wp_verify_nonce( $_POST['wpda_timer_nonce'], 'wpda_timer_save' ) ) return 0;

		$name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : 'Timer';
		if ( $name === '' || $name === '0' ) $name = 'Unnamed';

		$data = array( 'name' => $name, 'version' => '1.2' );
		foreach ( $this->options as $group ) {
			foreach ( $group['params'] as $key => $field ) {
				$data[ $key ] = isset( $_POST[ $key ] )
					? Wpda_Countdown_Admin_Fields::sanitize_field_value( $field, $_POST[ $key ] )
					: $field['default_value'];
			}
		}

		// Validate start/end dates (only for non-evergreen timers where dates are authoritative)
		if ( ! in_array( $data['timer_coundown_type'], array( 'evergreen_countdown', 'evergreen_countup' ), true ) ) {
			$start_ts = self::parse_timer_date( $data['timer_start_time'] );
			$end_ts   = self::parse_timer_date( $data['timer_end_date'] );
			if ( $start_ts && $end_ts && $start_ts >= $end_ts ) {
				echo '<div id="message" class="error"><p><strong>Start date must be earlier than end date.</strong> Timer was not saved.</p></div>';
				return 0;
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


	private function display_table_list_timer(){
		// Output fresh list data so the table reflects any save/delete
		// that happened earlier in this request (the enqueue hook localized
		// data BEFORE the save ran, so JS would otherwise see a stale snapshot).
		echo '<script>window.wpdaPageRowsList = ' . wp_json_encode( $this->get_row_list() ) . ';</script>';

		$params = array(
			'name'         => 'Timers',
			'add_new_link' => 'admin.php?page=wpda_countdown_menu&task=add_wpda_countdown_timer',
			'support_link' => wpdevart_countdown_support_url,
		);
		include wpda_countdown_plugin_path . 'includes/admin/Views/list-header.php';
	}

	public function get_row_list(){
		$rows = $this->repo->all();
		$type_labels=array('countdown'=>'Countdown','countup'=>'Count Up','evergreen_countdown'=>'Evergreen CD','evergreen_countup'=>'Evergreen CU');
		$result = array();
		foreach($rows as $row){
			$data=json_decode($row->option_value,true);
			$type=isset($data['timer_coundown_type'])?$data['timer_coundown_type']:'countdown';
			$result[] = array(
				'id' => $row->id,
				'name' => strip_tags($row->name),
				'type' => isset($type_labels[$type])?$type_labels[$type]:$type,
			);
		}
		return $result;
	}

	public function get_table_info(){
		$nonce = wp_create_nonce( 'wpda_timer_action' );
		return array(
			'keys' => array(
				'id'     => array( 'name' => 'ID',     'sortable' => true ),
				'name'   => array( 'name' => 'Name',   'link' => '&task=add_edit_timer', 'sortable' => true ),
				'type'   => array( 'name' => 'Type',   'sortable' => true ),
				'edit'   => array( 'name' => 'Edit',   'link' => '&task=add_edit_timer' ),
				'delete' => array( 'name' => 'Delete', 'link' => '&task=remove_timer&_wpnonce=' . $nonce ),
			),
			'link_page' => 'wpda_countdown_menu',
		);
	}
	
	private function generete_timer_parametrs($id=0){
		$timer_params = $id ? $this->repo->find( $id ) : null;	
		if($timer_params==NULL){			
			foreach($this->options as $param_heading_key=>$param_heading_value){
				foreach($param_heading_value['params'] as $key=>$value){
					$this->options[$param_heading_key]['params'][$key]["value"]=$this->options[$param_heading_key]['params'][$key]["default_value"];
				}
			}
		}else{
			$databases_parametrs=json_decode($timer_params->option_value, true);
			foreach($this->options as $param_heading_key=>$param_heading_value){
				foreach($param_heading_value['params'] as $key=>$value){
					if(isset($databases_parametrs[$key])){
						$this->options[$param_heading_key]['params'][$key]["value"]=$databases_parametrs[$key];
					}else{
						$this->options[$param_heading_key]['params'][$key]["value"]=$this->options[$param_heading_key]['params'][$key]["default_value"];
					}
				}
			}
			return $timer_params->name;
		}
	}
	
	
	private function add_edit_timer( $id = 0 ) {
		$name = $this->generete_timer_parametrs( $id );
		?>		
		<form action="admin.php?page=wpda_countdown_menu<?php if($id) echo '&id='.$id; ?>" method="post" name="adminForm" class="top_description_table" id="adminForm">
			<?php wp_nonce_field('wpda_timer_save','wpda_timer_nonce'); ?>
            <div class="conteiner">
                <?php Wpda_Countdown_Admin_Fields::render_pro_banner(); ?>
                <div class="header">
                    <span><h2 class="wpda_timer_title"><?php
                        if ( $id ) {
                            echo 'Edit Timer ';
                            if ( isset( $name ) ) echo '<span style="color:#2abf00">' . esc_html( $name ) . '</span>';
                        } else {
                            echo 'Add Timer';
                        }
                    ?></h2></span>
                    <div class="header_action_buttons">
                        <span><input type="button" onclick="submitbutton('save_timer')" value="Save" class="button-primary action"> </span>
                        <span><input type="button" onclick="submitbutton('update_timer')" value="Apply" class="button-primary action"> </span>
                        <span><input type="button" onclick="window.location.href='admin.php?page=wpda_countdown_menu'" value="Cancel" class="button-secondary action"> </span>
                    </div>
                </div>
                <div id="wpda_timeline_wrap" style="margin:15px 0;background:#fff;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;">
                    <div style="padding:10px 16px 0;display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:13px;font-weight:600;color:#1d2327;">Timeline Preview</span>
                        <span style="display:flex;gap:8px;align-items:center;">
                            <button type="button" id="wpda_timeline_goto_now" style="font-size:11px;padding:3px 10px;border:1px solid #c3c4c7;border-radius:4px;background:#fff;cursor:pointer;color:#2271b1;font-weight:500;">Go to Now</button>
                            <button type="button" id="wpda_timeline_fit_all" style="font-size:11px;padding:3px 10px;border:1px solid #c3c4c7;border-radius:4px;background:#fff;cursor:pointer;color:#50575e;">Fit All</button>
                            <span style="font-size:11px;color:#8c8f94;">Scroll to zoom &middot; Drag to pan</span>
                        </span>
                    </div>
                    <canvas id="wpda_timeline_canvas" width="800" height="140" style="width:100%;height:140px;cursor:grab;"></canvas>
                </div>
                <div class="option_panel">
                    <div class="parametr_name"></div>
                    <div class="all_options_panel">
                        <input type="text" class="timer_name" name="name" placeholder="Enter name here" value="<?php echo esc_attr( isset( $name ) ? $name : '' ); ?>">
                        <div class="wpda_timer_link_tabs">
							<?php
								echo '<ul>';
								foreach ( $this->options as $params_grup_name => $params_group_value ) {
									echo '<li id="' . esc_attr( $params_grup_name ) . '_tab">' . esc_html( $params_group_value['heading_name'] ) . '</li>';
								}
								echo '</ul>';
							?>
						</div>
                        <table>
						<?php 
						foreach($this->options as $params_grup_name =>$params_group_value){ 
							Wpda_Countdown_Admin_Fields::heading((isset($params_group_value['aditional_heading_name'])?$params_group_value['aditional_heading_name']:$params_group_value['heading_name']),$params_grup_name);
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
						}

						?>
					</table>
                    </div>
                </div>
            </div>
		</form>
		<?php

		 
	}
	private static function timezone_array(){
		return array(
			'Pacific/Midway'       => "(GMT-11:00) Midway Island",
			'US/Samoa'             => "(GMT-11:00) Samoa",
			'US/Hawaii'            => "(GMT-10:00) Hawaii",
			'US/Alaska'            => "(GMT-09:00) Alaska",
			'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
			'America/Tijuana'      => "(GMT-08:00) Tijuana",
			'US/Arizona'           => "(GMT-07:00) Arizona",
			'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
			'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
			'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
			'America/Mexico_City'  => "(GMT-06:00) Mexico City",
			'America/Monterrey'    => "(GMT-06:00) Monterrey",
			'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
			'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
			'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
			'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
			'America/Bogota'       => "(GMT-05:00) Bogota",
			'America/Lima'         => "(GMT-05:00) Lima",
			'America/Caracas'      => "(GMT-04:30) Caracas",
			'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
			'America/La_Paz'       => "(GMT-04:00) La Paz",
			'America/Santiago'     => "(GMT-04:00) Santiago",
			'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
			'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
			'Greenland'            => "(GMT-03:00) Greenland",
			'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
			'Atlantic/Azores'      => "(GMT-01:00) Azores",
			'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
			'Africa/Casablanca'    => "(GMT) Casablanca",
			'Europe/Dublin'        => "(GMT) Dublin",
			'Europe/Lisbon'        => "(GMT) Lisbon",
			'Europe/London'        => "(GMT) London",
			'Africa/Monrovia'      => "(GMT) Monrovia",
			'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
			'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
			'Europe/Berlin'        => "(GMT+01:00) Berlin",
			'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
			'Europe/Brussels'      => "(GMT+01:00) Brussels",
			'Europe/Budapest'      => "(GMT+01:00) Budapest",
			'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
			'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
			'Europe/Madrid'        => "(GMT+01:00) Madrid",
			'Europe/Paris'         => "(GMT+01:00) Paris",
			'Europe/Prague'        => "(GMT+01:00) Prague",
			'Europe/Rome'          => "(GMT+01:00) Rome",
			'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
			'Europe/Skopje'        => "(GMT+01:00) Skopje",
			'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
			'Europe/Vienna'        => "(GMT+01:00) Vienna",
			'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
			'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
			'Europe/Athens'        => "(GMT+02:00) Athens",
			'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
			'Africa/Cairo'         => "(GMT+02:00) Cairo",
			'Africa/Harare'        => "(GMT+02:00) Harare",
			'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
			'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
			'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
			'Europe/Kiev'          => "(GMT+02:00) Kyiv",
			'Europe/Minsk'         => "(GMT+02:00) Minsk",
			'Europe/Riga'          => "(GMT+02:00) Riga",
			'Europe/Sofia'         => "(GMT+02:00) Sofia",
			'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
			'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
			'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
			'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
			'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
			'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
			'Europe/Moscow'        => "(GMT+03:00) Moscow",
			'Asia/Tehran'          => "(GMT+03:30) Tehran",
			'Asia/Baku'            => "(GMT+04:00) Baku",
			'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
			'Asia/Muscat'          => "(GMT+04:00) Muscat",
			'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
			'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
			'Asia/Kabul'           => "(GMT+04:30) Kabul",
			'Asia/Karachi'         => "(GMT+05:00) Karachi",
			'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
			'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
			'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
			'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
			'Asia/Almaty'          => "(GMT+06:00) Almaty",
			'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
			'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
			'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
			'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
			'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
			'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
			'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
			'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
			'Australia/Perth'      => "(GMT+08:00) Perth",
			'Asia/Singapore'       => "(GMT+08:00) Singapore",
			'Asia/Taipei'          => "(GMT+08:00) Taipei",
			'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
			'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
			'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
			'Asia/Seoul'           => "(GMT+09:00) Seoul",
			'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
			'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
			'Australia/Darwin'     => "(GMT+09:30) Darwin",
			'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
			'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
			'Australia/Canberra'   => "(GMT+10:00) Canberra",
			'Pacific/Guam'         => "(GMT+10:00) Guam",
			'Australia/Hobart'     => "(GMT+10:00) Hobart",
			'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
			'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
			'Australia/Sydney'     => "(GMT+10:00) Sydney",
			'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
			'Asia/Magadan'         => "(GMT+12:00) Magadan",
			'Pacific/Auckland'     => "(GMT+12:00) Auckland",
			'Pacific/Fiji'         => "(GMT+12:00) Fiji",
		);
	}
	
}


 ?>
