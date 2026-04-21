<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Installer — database table creation and default data seeding.
 *
 * Called on plugin activation and by ensure_tables() safety net.
 * Replaces the old install_databese.php with cleaner code.
 */
class Wpda_Countdown_Installer {

	private $timer_repo;
	private $theme_repo;

	public function __construct(
		Wpda_Countdown_Timer_Repository $timer_repo,
		Wpda_Countdown_Theme_Repository $theme_repo
	) {
		$this->timer_repo = $timer_repo;
		$this->theme_repo = $theme_repo;
	}

	/**
	 * Run full installation: create tables + seed defaults.
	 */
	public function install() {
		$this->create_timer_table();
		$this->create_theme_table();
	}

	/**
	 * Safety check — runs on every admin page + plugin load.
	 * Uses an option sentinel so the SHOW TABLES probe only happens once per
	 * successful verification. If tables are missing or the sentinel says
	 * they weren't confirmed yet, runs the full installer. Also cleans up
	 * any duplicate "Classic B&W" rows in case the installer ran twice.
	 */
	public function ensure_tables() {
		if ( get_option( 'wpda_countdown_tables_ready' ) === '1'
		  && $this->timer_repo->table_exists()
		  && $this->theme_repo->table_exists() ) {
			return;
		}
		$this->install();
		if ( $this->timer_repo->table_exists() && $this->theme_repo->table_exists() ) {
			$this->cleanup_duplicate_base_theme();
			update_option( 'wpda_countdown_tables_ready', '1' );
		}
	}

	/**
	 * Self-heal: if multiple rows named "Classic B&W" exist (a race between
	 * activation and plugins_loaded seeding, or a double-install), keep the
	 * one with the lowest id and drop the rest.
	 */
	private function cleanup_duplicate_base_theme() {
		global $wpdb;
		$table = $this->theme_repo->get_table_name();
		$ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT id FROM {$table} WHERE name = %s ORDER BY id ASC",
			'Classic B&W'
		) );
		if ( count( $ids ) <= 1 ) return;
		$keep = (int) array_shift( $ids );
		foreach ( $ids as $dup_id ) {
			$wpdb->delete( $table, array( 'id' => (int) $dup_id ), array( '%d' ) );
		}
		// Make sure the kept row stays marked as default.
		$wpdb->update( $table, array( 'default' => 1 ), array( 'id' => $keep ), array( '%d' ), array( '%d' ) );
	}

	// ─── Timer table ───────────────────────────────────────────

	private function create_timer_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = $this->timer_repo->get_table_name();
		$charset = $wpdb->get_charset_collate();

		dbDelta( "CREATE TABLE IF NOT EXISTS {$table} (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`name` varchar(512) NOT NULL,
			`option_value` longtext NOT NULL,
			UNIQUE KEY id (id)
		) {$charset};" );

		$this->seed_default_timers();
	}

	private function seed_default_timers() {
		if ( $this->timer_repo->count() > 0 ) return;

		$base = array(
			'version'                      => '1.2',
			'timer_start_time'             => date( 'd/m/Y', strtotime( '-1 day' ) ) . ' 00:00',
			'timer_end_date'               => date( 'd/m/Y', strtotime( '+30 day' ) ) . ' 00:00',
			'timer_timezone'               => date_default_timezone_get() === 'UTC' ? 'Europe/London' : date_default_timezone_get(),
			'timer_coundown_repeat'        => 'none',
			'after_countdown_repeat_time'  => array( 'hour' => '1', 'minute' => '0' ),
			'repeat_hourly_interval'       => '3',
			'repeat_daily_quantity'        => '1',
			'repeat_weekly_days'           => array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ),
			'repeat_monthly_day'           => '1',
			'repeat_countdown_start_time'  => '00:00',
			'repeat_countdown_end_time'    => '23:59',
			'repeat_end'                   => 'never',
			'repeat_ending_after'          => '10',
			'repeat_ending_after_date'     => date( 'd/m/Y', strtotime( '+30 days' ) ) . ' 00:00',
			'after_countdown_end_type'     => 'text',
			'after_countdown_text'         => '<h3>Time is up!</h3>',
			'after_countdown_redirect'     => '',
			'after_countdown_button_text'  => 'Shop Now',
			'after_countdown_button_url'   => '',
			'after_countdown_css_selector' => '',
			'before_countup_start_type'    => 'none',
			'before_countup_text'          => '',
			'before_countup_redirect'      => '',
			'top_countdown_show_html'      => '',
			'bottom_countdown_show_html'   => '',
		);

		if ( WPDA_COUNTDOWN_IS_PRO ) {
			$timers = array(
				array(
					'name' => 'Evergreen 10 min countdown',
					'data' => array_merge( $base, array(
						'name'                        => 'Evergreen 10 min countdown',
						'timer_coundown_type'         => 'evergreen_countdown',
						'timer_seesion_time'          => array( 'day' => '0', 'hour' => '0', 'minute' => '10' ),
						'evergreen_expire_mode'       => 'duration',
						'evergreen_restart'           => 'delay',
						'evergreen_restart_delay'     => array( 'hour' => '24', 'minute' => '0' ),
						'evergreen_daily_expire_time' => '23:59',
						'after_countdown_text'        => '<h3>Offer expired!</h3><p>Come back tomorrow for a new deal.</p>',
					) ),
				),
				array(
					'name' => 'Evergreen daily midnight',
					'data' => array_merge( $base, array(
						'name'                        => 'Evergreen daily midnight',
						'timer_coundown_type'         => 'evergreen_countdown',
						'timer_seesion_time'          => array( 'day' => '0', 'hour' => '0', 'minute' => '1' ),
						'evergreen_expire_mode'       => 'daily_time',
						'evergreen_restart'           => 'none',
						'evergreen_restart_delay'     => array( 'hour' => '0', 'minute' => '0' ),
						'evergreen_daily_expire_time' => '23:59',
						'after_countdown_text'        => '<h3>Today\'s deal has ended</h3>',
					) ),
				),
				array(
					'name' => 'Evergreen 1 hour countup',
					'data' => array_merge( $base, array(
						'name'                        => 'Evergreen 1 hour countup',
						'timer_coundown_type'         => 'evergreen_countup',
						'timer_seesion_time'          => array( 'day' => '0', 'hour' => '1', 'minute' => '0' ),
						'evergreen_expire_mode'       => 'duration',
						'evergreen_restart'           => 'immediate',
						'evergreen_restart_delay'     => array( 'hour' => '0', 'minute' => '0' ),
						'evergreen_daily_expire_time' => '23:59',
						'after_countdown_end_type'    => 'hide',
						'after_countdown_text'        => '',
					) ),
				),
			);
		} else {
			// Free — a single simple countdown timer
			$timers = array(
				array(
					'name' => 'My first countdown',
					'data' => array_merge( $base, array(
						'name'                => 'My first countdown',
						'timer_coundown_type' => 'countdown',
					) ),
				),
			);
		}

		foreach ( $timers as $t ) {
			$this->timer_repo->insert( $t['name'], $t['data'] );
		}
	}

	// ─── Theme table ───────────────────────────────────────────

	private function create_theme_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = $this->theme_repo->get_table_name();
		$charset = $wpdb->get_charset_collate();

		dbDelta( "CREATE TABLE IF NOT EXISTS {$table} (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`name` varchar(512) NOT NULL,
			`option_value` longtext NOT NULL,
			`default` tinyint(4) NOT NULL,
			UNIQUE KEY id (id)
		) {$charset};" );

		$this->seed_base_theme();
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			$this->seed_pro_themes();
		}
	}

	private function seed_base_theme() {
		// Two-stage guard against duplicate seeding:
		//   1. count() — normal fast path (skip if any theme exists).
		//   2. name-specific check — catches race conditions where two calls
		//      (activation hook + plugins_loaded safety net) both see count=0
		//      before either insert commits, or where an older install left
		//      the themes table present but empty.
		if ( $this->theme_repo->count() > 0 ) return;
		global $wpdb;
		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$this->theme_repo->get_table_name()} WHERE name = %s LIMIT 1",
			'Classic B&W'
		) );
		if ( $exists ) return;

		// Classic B&W — the shared base theme for Free and Pro. Values pulled
		// from the production DB after hand-tuning against the theme-isolation
		// CSS reset, so what's seeded now matches the intended rendered design.
		$name = 'Classic B&W';
		$data = array(
			'name'                                         => $name,
			'countdown_type'                               => 'standart',
			// Common layout
			'countdown_global_width'                       => '100',
			'countdown_global_width_metrick'               => '%',
			'countdown_horizontal_position'                => 'center',
			'countdown_date_display'                       => array( 'day' => 'day', 'hour' => 'hour', 'minut' => 'minut', 'second' => 'second' ),
			'countdown_text_type'                          => 'po_mo',
			'text_for_weeks'                               => 'Weeks',
			'text_for_day'                                 => 'Days',
			'text_for_hour'                                => 'Hours',
			'text_for_minute'                              => 'Minutes',
			'text_for_second'                              => 'Seconds',
			// Standard
			'countdown_standart_elements_width'            => '60',
			'countdown_standart_elements_distance'         => '15',
			'countdown_standart_time_bg_color'             => '#FFFFFF',
			'countdown_standart_time_text_bg_color'        => '#000000',
			'countdown_standart_time_font_size'            => '34',
			'countdown_standart_time_text_font_size'       => '10',
			'countdown_standart_time_color'                => '#000000',
			'countdown_standart_time_text_color'           => '#FFFFFF',
			'countdown_standart_time_font_famely'          => 'Garamond,Hoefler Text,Times New Roman,Times,serif',
			'countdown_standart_time_text_font_famely'     => 'Consolas,Andale Mono,Monaco,Courier,Courier New,Verdana,sans-serif',
			'countdown_standart_time_padding'              => array( 'top' => '2', 'right' => '14', 'bottom' => '2', 'left' => '14' ),
			'countdown_standart_time_text_padding'         => array( 'top' => '2', 'right' => '5',  'bottom' => '2', 'left' => '5'  ),
			'countdown_standart_time_margin'               => array( 'top' => '0', 'right' => '0',  'bottom' => '0', 'left' => '0'  ),
			'countdown_standart_time_text_margin'          => array( 'top' => '2', 'right' => '0',  'bottom' => '0', 'left' => '0'  ),
			'countdown_standart_time_border_width'         => '1',
			'countdown_standart_time_text_border_width'    => '0',
			'countdown_standart_time_border_radius'        => '4',
			'countdown_standart_time_text_border_radius'   => '2',
			'countdown_standart_time_border_color'         => '#333333',
			'countdown_standart_time_text_border_color'    => '#000000',
			'countdown_standart_animation_type'            => 'none',
			'countdown_standart_gorup_animation'           => 'group',
			'countdown_standart_display_inline'            => '0',
			// Vertical (pro) — placeholders so frontend renders correctly if user switches to vertical
			'countdown_vertical_elements_distance'         => '12',
			'countdown_vertical_background_color'          => '#FFFFFF',
			'countdown_vertical_time_font_size'            => '5',
			'countdown_vertical_time_color'                => '#FFFFFF',
			'countdown_vertical_time_font_famely'          => 'Arial,Helvetica Neue,Helvetica,sans-serif',
			'countdown_vertical_time_border_width'         => '1',
			'countdown_vertical_time_border_color'         => '#000000',
			'countdown_vertical_time_text_bg_color'        => '#FFFFFF',
			'countdown_vertical_time_text_font_size'       => '5',
			'countdown_vertical_time_text_color'           => '#FFFFFF',
			'countdown_vertical_time_text_font_famely'     => 'Arial,Helvetica Neue,Helvetica,sans-serif',
			'countdown_vertical_time_text_padding'         => array( 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0' ),
			'countdown_vertical_time_text_margin'          => array( 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0' ),
			'countdown_vertical_time_text_border_width'    => '0',
			'countdown_vertical_time_text_border_radius'   => '0',
			'countdown_vertical_time_text_border_color'    => '#FFFFFF',
			'countdown_vertical_display_inline'            => '0',
			'countdown_vertical_gorup_animation'           => 'group',
			'countdown_vertical_animation_type'            => 'none',
			// Circle (pro)
			'countdown_circle_elements_width_height'       => '120',
			'countdown_circle_elements_distance'           => '12',
			'countdown_circle_background_color'            => '#ffffff',
			'countdown_circle_background_color_opacity'    => '100',
			'countdown_circle_border_color_outside'        => '#e0e0e0',
			'countdown_circle_border_color_inside'         => '#2271b1',
			'countdown_circle_width_parcents'              => '8',
			'countdown_circle_type_of_rounding'            => 'round',
			'countdown_circle_border_direction'            => 'right',
			'countdown_circle_time_font_size'              => '28',
			'countdown_circle_time_text_font_size'         => '12',
			'countdown_circle_time_color'                  => '#1d2327',
			'countdown_circle_time_text_color'             => '#50575e',
			'countdown_circle_time_font_famely'            => 'Arial,Helvetica Neue,Helvetica,sans-serif',
			'countdown_circle_display_inline'              => '0',
			'countdown_circle_gorup_animation'             => 'group',
			'countdown_circle_animation_type'              => 'none',
			// Flip (pro)
			'countdown_flip_card_bg'                       => '#1d2327',
			'countdown_flip_card_color'                    => '#ffffff',
			'countdown_flip_label_color'                   => '#50575e',
			'countdown_flip_card_width'                    => '70',
			'countdown_flip_card_height'                   => '80',
			'countdown_flip_font_size'                     => '36',
			'countdown_flip_gap'                           => '12',
			'countdown_flip_border_radius'                 => '8',
			'countdown_flip_font_family'                   => 'Arial,Helvetica Neue,Helvetica,sans-serif',
			'countdown_flip_display_inline'                => '0',
			'countdown_flip_gorup_animation'               => 'group',
			'countdown_flip_animation_type'                => 'none',
		);

		$this->theme_repo->insert( $name, $data, true );
	}

	private function seed_pro_themes() {
		if ( get_option( 'wpda_countdown_pro_themes_installed' ) ) return;

		$themes_file = wpda_countdown_plugin_path . 'includes/default_themes.php';
		if ( ! file_exists( $themes_file ) ) return;

		$themes = include $themes_file;
		if ( ! is_array( $themes ) ) return;

		$has_default = $this->theme_repo->get_default_id();

		foreach ( $themes as $theme ) {
			$data         = $theme['data'];
			$data['name'] = $theme['name'];
			$is_default   = ( $theme['default'] && ! $has_default ) ? true : false;
			if ( $is_default ) $has_default = true;

			$this->theme_repo->insert( $theme['name'], $data, $is_default );
		}

		update_option( 'wpda_countdown_pro_themes_installed', '1' );
	}
}
