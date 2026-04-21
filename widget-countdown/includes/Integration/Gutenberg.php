<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class wpda_countdown_gutenberg {

	function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) return;

		$block_args = array(
			'style'           => 'wpda_countdown_gutenberg_css',
			'editor_script'   => 'wpda_countdown_gutenberg_js',
			'render_callback' => array( $this, 'render_block' ),
			'attributes'      => array(
				'mode'    => array( 'type' => 'string', 'default' => 'timer' ),
				'timer'   => array( 'type' => 'string', 'default' => '' ),
				'theme'   => array( 'type' => 'string', 'default' => '' ),
				'endDate' => array( 'type' => 'string', 'default' => '' ),
				'endTime' => array( 'type' => 'string', 'default' => '23:59' ),
			),
		);
		register_block_type( 'wpdevart-countdown/countdown-pro', $block_args );

		wp_add_inline_script(
			'wpda_countdown_gutenberg_js',
			sprintf(
				'var wpda_countdown_gutenberg_data = { timers: %s, themes: %s, other_data: %s };',
				wp_json_encode( $this->get_timers() ),
				wp_json_encode( $this->get_themes() ),
				wp_json_encode( $this->get_misc_data() )
			),
			'before'
		);
	}

	private function get_timers() {
		return wpda_countdown()->timer_repository()->all_names();
	}

	private function get_themes() {
		return wpda_countdown()->theme_repository()->all_names();
	}

	private function get_misc_data() {
		return array(
			'icon_src' => wpda_countdown_plugin_url . 'includes/admin/images/icon.svg',
			'is_pro'   => WPDA_COUNTDOWN_IS_PRO ? 1 : 0,
		);
	}

	public function render_block( $attributes ) {
		$mode     = isset( $attributes['mode'] ) && in_array( $attributes['mode'], array( 'timer', 'date' ) ) ? $attributes['mode'] : 'timer';
		$timer_id = isset( $attributes['timer'] )   ? intval( $attributes['timer'] )   : 0;
		$theme_id = isset( $attributes['theme'] )   ? intval( $attributes['theme'] )   : 0;
		$end_date = isset( $attributes['endDate'] ) ? sanitize_text_field( $attributes['endDate'] ) : '';
		$end_time = isset( $attributes['endTime'] ) ? sanitize_text_field( $attributes['endTime'] ) : '23:59';

		if ( $mode === 'date' && ! empty( $end_date ) ) {
			$parts = explode( '-', $end_date );
			if ( count( $parts ) === 3 ) {
				$formatted = $parts[2] . '/' . $parts[1] . '/' . $parts[0] . ' ' . $end_time;
				$sc = '[wpda_countdown end_date="' . $formatted . '"';
				if ( ! empty( $theme_id ) && $theme_id !== '0' ) $sc .= ' theme_id="' . $theme_id . '"';
				$sc .= ']';
				return do_shortcode( $sc );
			}
		}

		if ( ! empty( $timer_id ) ) {
			return do_shortcode( '[wpda_countdown timer_id="' . $timer_id . '" theme_id="' . ( $theme_id ? $theme_id : '0' ) . '"]' );
		}

		return '';
	}
}
