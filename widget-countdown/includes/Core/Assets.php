<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Assets — single place for ALL script and style registration.
 *
 * Hooked to 'init'. Individual pages only need to call wp_enqueue_*.
 * No more wp_register_script scattered across multiple files.
 */
class Wpda_Countdown_Assets {

	public function register() {
		$v   = WPDA_COUNTDOWN_VERSION;
		$url = wpda_countdown_plugin_url;

		// ─── Legacy frontend ────────────────────────────────────
		wp_register_script( 'countdown-front-end', $url . 'includes/legacy/javascript/front_end_js.js', array( 'jquery' ), $v, false );
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			wp_register_script( 'canvase_countedown_main', $url . 'includes/legacy/javascript/jquery.classycountdown.js', array(), $v );
			wp_register_script( 'canvase_countedown_jquery_lib_knop', $url . 'includes/legacy/javascript/jquery.knob.js', array(), $v );
			wp_register_script( 'canvase_countedown_jquery_lib_thortle', $url . 'includes/legacy/javascript/jquery.throttle.js', array(), $v );
			wp_register_script( 'canvase_countedown_lexsus', $url . 'includes/legacy/javascript/jquery.timeTo.min.js', array(), $v );
		}
		wp_register_style( 'countdown_css', $url . 'includes/legacy/style/style.css', array(), $v );
		wp_register_style( 'animated', $url . 'includes/legacy/style/effects.css', array(), $v );

		wp_register_script( 'foundation-datepicker', $url . 'includes/legacy/javascript/foundation-datepicker.min.js', array(), $v );
		wp_register_style( 'foundation-datepicker', $url . 'includes/legacy/style/foundation-datepicker.min.css', array(), $v );

		// ─── Legacy Gutenberg ───────────────────────────────────
		wp_register_script( 'wpda_countdown_basic_gutenberg_js', $url . 'includes/legacy/gutenberg/block.js', array( 'wp-element', 'wp-blocks', 'wp-i18n', 'wp-components', 'underscore' ), $v );
		wp_register_style( 'wpda_countdown_basic_gutenberg_css', $url . 'includes/legacy/gutenberg/style.css', array(), $v );

		// ─── Admin shared ───────────────────────────────────────
		$protocol = is_ssl() ? 'https' : 'http';
		wp_register_style( 'FontAwesome', $url . 'includes/admin/css/font-awesome.min.css', array(), $v );
		wp_register_style( 'wpda_countdown_gutenberg_css', $url . 'includes/admin/gutenberg/style.css', array(), $v );
		wp_register_style( 'wpdevart-countdown-pro-jquery-ui', $protocol . '://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );

		// Flatpickr — modern date-time picker (replaces legacy jQuery UI timepicker)
		wp_register_style( 'wpda_flatpickr_css', $url . 'includes/admin/js/flatpickr/flatpickr.min.css', array(), '4.6.13' );
		wp_register_script( 'wpda_flatpickr_js', $url . 'includes/admin/js/flatpickr/flatpickr.min.js', array(), '4.6.13', true );

		wp_register_script( 'wpdevart_chosen', $url . 'includes/admin/js/chosen.js', array( 'jquery' ), $v );
		wp_register_script( 'wpdevart_prism', $url . 'includes/admin/js/prism.js', array(), $v );
		wp_register_script( 'wpda_countdown_gutenberg_js', $url . 'includes/admin/gutenberg/block.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'underscore' ), $v );

		// ─── Frontend (pro) ─────────────────────────────────────
		wp_register_script( 'wpdevart_countdown_standart', $url . 'includes/frontend/js/front_timer_pro.js', array(), $v, true );
		wp_register_style( 'wpdevart_countdown_front', $url . 'includes/frontend/css/front_end.css', array(), $v );
		wp_register_script( 'wpdevart_countdown_popup_front', $url . 'includes/frontend/js/popup.js', array(), $v );

		// ─── Admin page-specific ────────────────────────────────
		wp_register_style( 'wpda_countdown_timer_page_css', $url . 'includes/admin/css/timer_page.css', array(), $v );
		wp_register_style( 'wpda_countdown_theme_page_css', $url . 'includes/admin/css/theme_page.css', array(), $v );
		wp_register_style( 'wpda_countdown_popup_page_css', $url . 'includes/admin/css/popup_page.css', array(), $v );
		wp_register_style( 'wpda_countdown_hire_expert_css', $url . 'includes/admin/css/hire_expert.css', array(), $v );
		wp_register_style( 'wpda_admin_add_edit_header', $url . 'includes/admin/css/admin-page-task-add-edit-header.css', array(), $v );
		wp_register_style( 'wpda_admin_list_header', $url . 'includes/admin/css/admin-page-task-list-header.css', array(), $v );
		wp_register_style( 'wpda_table_maker_css', $url . 'includes/admin/css/wpda_table_maker.css', array(), $v );

		wp_register_script( 'wpda_countdown_timer_page_js', $url . 'includes/admin/js/timer_page.js', array( 'jquery' ), $v );
		wp_register_script( 'wpda_countdown_timeline_js', $url . 'includes/admin/js/timeline.js', array(), $v, true );
		wp_register_script( 'wpda_countdown_theme_page_js', $url . 'includes/admin/js/theme_page.js', array( 'jquery' ), $v );
		wp_register_script( 'wpda_countdown_popup_page_js', $url . 'includes/admin/js/popup_page.js', array( 'jquery' ), $v );
		wp_register_script( 'wpda_countdown_sticky_bar_js', $url . 'includes/admin/js/sticky_bar_page.js', array( 'jquery' ), $v );
		wp_register_script( 'wpda_table_maker_js', $url . 'includes/admin/js/wpda_table_maker.js', array(), $v, true );
	}
}
