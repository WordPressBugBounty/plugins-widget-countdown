<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AdminMenu — menu registration, script enqueues, AJAX handlers.
 *
 * Replaces the god-class admin.php. Each responsibility is a focused method.
 * DB operations are NEVER done inside enqueue hooks.
 */
class Wpda_Countdown_Admin_Menu {

	private $timer_repo;
	private $theme_repo;
	private $installer;

	public function __construct(
		Wpda_Countdown_Timer_Repository $timer_repo,
		Wpda_Countdown_Theme_Repository $theme_repo,
		Wpda_Countdown_Installer $installer
	) {
		$this->timer_repo = $timer_repo;
		$this->theme_repo = $theme_repo;
		$this->installer  = $installer;
	}

	/**
	 * Wire all admin hooks. Called once from Plugin bootstrap.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_filter( 'mce_external_plugins', array( $this, 'mce_plugins' ) );
		add_filter( 'mce_buttons', array( $this, 'mce_buttons' ) );
		add_action( 'wp_ajax_wpda_countdown_post_page_content', array( $this, 'ajax_post_popup' ) );

		if ( WPDA_COUNTDOWN_IS_PRO ) {
			add_action( 'wp_ajax_countdown_popup_page_save', array( $this, 'ajax_save_popup' ) );
			add_action( 'wp_ajax_sticky_bar_page_save', array( $this, 'ajax_save_sticky_bar' ) );
		}

		if ( $this->is_woocommerce_active() ) {
			$this->init_woocommerce();
		}

		$this->init_gutenberg();
	}

	// ─── Menu registration ──────────────────────────────────────

	public function register_menus() {
		global $submenu;

		$main = add_menu_page(
			'Countdown', 'Countdown', 'manage_options',
			'wpda_countdown_menu', array( $this, 'page_timer' ),
			'dashicons-clock'
		);
		add_submenu_page(
			'wpda_countdown_menu', 'Timer', 'Timer', 'manage_options',
			'wpda_countdown_menu', array( $this, 'page_timer' )
		);
		$theme_page = add_submenu_page(
			'wpda_countdown_menu', 'Themes', 'Themes', 'manage_options',
			'wpda_countdown_themes', array( $this, 'page_theme' )
		);
		$popup_label  = WPDA_COUNTDOWN_IS_PRO ? 'Popup'      : 'Popup <span style="color:#7052fb;font-weight:600;">(pro)</span>';
		$sticky_label = WPDA_COUNTDOWN_IS_PRO ? 'Sticky Bar' : 'Sticky Bar <span style="color:#7052fb;font-weight:600;">(pro)</span>';
		$popup_page = add_submenu_page(
			'wpda_countdown_menu', 'Popup', $popup_label, 'manage_options',
			'wpda_countdown_popup_settings', array( $this, 'page_popup' )
		);
		$sticky_page = add_submenu_page(
			'wpda_countdown_menu', 'Sticky Bar', $sticky_label, 'manage_options',
			'wpda_countdown_sticky_bar', array( $this, 'page_sticky_bar' )
		);
		add_submenu_page(
			'wpda_countdown_menu', 'Featured Plugins', 'Featured Plugins', 'manage_options',
			'wpda_countdown_featured_plugins', array( $this, 'page_featured_plugins' )
		);
		$hire_page = add_submenu_page(
			'wpda_countdown_menu', 'Hire an Expert',
			'<span style="color:#00ff66">Hire an Expert</span>', 'manage_options',
			'wpda_countdown_hire_expert', array( $this, 'page_hire_expert' )
		);

		if ( isset( $submenu['wpda_countdown_menu'] ) ) {
			add_submenu_page(
				'wpda_countdown_menu', 'Support or Any Ideas?',
				'<span style="color:#00ff66">Support or Any Ideas?</span>', 'manage_options',
				'any_ideas', array( $this, 'page_any_ideas' ), 155
			);
		}

		// Enqueue assets per page
		add_action( 'admin_print_styles-' . $main, array( $this, 'enqueue_timer_page' ) );
		add_action( 'admin_print_styles-' . $theme_page, array( $this, 'enqueue_theme_page' ) );
		add_action( 'admin_print_styles-' . $popup_page, array( $this, 'enqueue_popup_page' ) );
		add_action( 'admin_print_styles-' . $sticky_page, array( $this, 'enqueue_sticky_bar_page' ) );
		add_action( 'admin_print_styles-' . $hire_page, array( $this, 'enqueue_hire_expert_page' ) );

		// Redirect support link
		if ( isset( $submenu['wpda_countdown_menu'] ) ) {
			$count = count( $submenu['wpda_countdown_menu'] ) - 1;
			$submenu['wpda_countdown_menu'][ $count ][2] = wpdevart_countdown_support_url;
		}
	}

	// ─── Script enqueues (NO database operations here!) ─────────

	public function enqueue_timer_page() {
		// Actions (delete, set default) run here so the page body renders against the post-action state.
		$this->process_timer_actions();

		$is_edit = $this->is_edit_task( array( 'add_wpda_countdown_timer', 'add_edit_timer', 'update_timer' ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'wpda_countdown_timer_page_css' );

		if ( $is_edit ) {
			wp_enqueue_script( 'wpda_flatpickr_js' );
			wp_enqueue_style( 'wpda_flatpickr_css' );
			wp_enqueue_script( 'wpda_countdown_timer_page_js' );
			wp_enqueue_script( 'wpda_countdown_timeline_js' );
			wp_enqueue_style( 'wpda_admin_add_edit_header' );
		} else {
			wp_enqueue_style( 'wpda_admin_list_header' );
			wp_enqueue_style( 'wpda_table_maker_css' );
			wp_enqueue_script( 'wpda_table_maker_js' );
			// Only table-info is localized here (stable). Row list is output inline in
			// display_table_list_timer() so it reflects rows created/updated in the
			// same request (save runs AFTER enqueue).
			$timer_page = new wpda_countdown_timer_page( $this->timer_repo );
			wp_localize_script( 'wpda_table_maker_js', 'wpdaPageRowsInfo', $timer_page->get_table_info() );
		}
	}

	public function enqueue_theme_page() {
		// Actions (delete, set default) run here so the page body renders against the post-action state.
		$this->process_theme_actions();

		$is_edit = $this->is_edit_task( array( 'add_wpda_countdown_theme', 'add_edit_theme', 'update_theme' ) );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'wpda_countdown_theme_page_css' );

		if ( $is_edit ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'wpda_countdown_theme_page_js' );
			wp_enqueue_style( 'wpda_admin_add_edit_header' );
			// Load the frontend CSS on the theme editor so the live preview uses the
			// same theme-isolation reset and specific rules as the actual frontend.
			wp_enqueue_style( 'wpdevart_countdown_front' );
		} else {
			wp_enqueue_style( 'wpda_admin_list_header' );
			wp_enqueue_style( 'wpda_table_maker_css' );
			wp_enqueue_script( 'wpda_table_maker_js' );
			// Row list is output inline in display_table_list_theme() (see note above).
			$theme_page = new wpda_countdown_theme_page( $this->theme_repo );
			wp_localize_script( 'wpda_table_maker_js', 'wpdaPageRowsInfo', $theme_page->get_table_info() );
		}
	}

	public function enqueue_popup_page() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'wpdevart-countdown-pro-jquery-ui' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'wpdevart_chosen' );
		wp_enqueue_script( 'wpdevart_prism' );
		wp_enqueue_style( 'wpda_admin_add_edit_header' );
		wp_enqueue_style( 'wpda_countdown_popup_page_css' );
		wp_enqueue_script( 'wpda_countdown_popup_page_js' );
		if ( function_exists( 'wp_enqueue_media' ) ) wp_enqueue_media();
	}

	public function enqueue_sticky_bar_page() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'wpdevart_chosen' );
		wp_enqueue_style( 'wpda_admin_add_edit_header' );
		wp_enqueue_style( 'wpda_countdown_popup_page_css' );
		wp_enqueue_script( 'wpda_countdown_sticky_bar_js' );
		if ( function_exists( 'wp_enqueue_media' ) ) wp_enqueue_media();
	}

	public function enqueue_hire_expert_page() {
		wp_enqueue_style( 'wpda_countdown_hire_expert_css' );
	}

	// ─── Page callbacks ─────────────────────────────────────────

	public function page_timer() {
		$this->installer->ensure_tables();
		$this->process_timer_actions();
		$timer_page = new wpda_countdown_timer_page( $this->timer_repo );
		$timer_page->controller_page();
	}

	public function page_theme() {
		$this->installer->ensure_tables();
		$this->process_theme_actions();
		$theme_page = new wpda_countdown_theme_page( $this->theme_repo );
		$theme_page->controller_page();
	}

	public function page_popup() {
		$this->installer->ensure_tables();
		$popup_page = new wpda_countdown_popup_page();
		$popup_page->controller_page();
	}

	public function page_sticky_bar() {
		$this->installer->ensure_tables();
		$sticky_page = new wpda_countdown_sticky_bar_page();
		$sticky_page->controller_page();
	}

	public function page_featured_plugins() {
		include wpda_countdown_plugin_path . 'includes/admin/Views/featured-plugins.php';
	}

	public function page_hire_expert() {
		include wpda_countdown_plugin_path . 'includes/admin/Views/hire-expert.php';
	}

	public function page_any_ideas() {
		// Callback for Support submenu — link is redirected via $submenu override
	}

	// ─── Action processing ──────────────────────────────────────
	// Called from enqueue hooks (fires before page render) so the
	// localized JS list data reflects the current state. Safe to call
	// multiple times thanks to the $processed flags.

	private $timer_actions_done = false;
	private $theme_actions_done = false;

	private function process_timer_actions() {
		if ( $this->timer_actions_done ) return;
		$this->timer_actions_done = true;

		$task = isset( $_GET['task'] ) ? sanitize_text_field( $_GET['task'] ) : '';
		$id   = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		if ( $task === 'remove_timer' && $id ) {
			$this->verify_action_nonce( 'wpda_timer_action' );
			$this->timer_repo->delete( $id );
		}
	}

	private function process_theme_actions() {
		if ( $this->theme_actions_done ) return;
		$this->theme_actions_done = true;

		$task = isset( $_GET['task'] ) ? sanitize_text_field( $_GET['task'] ) : '';
		$id   = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

		if ( $task === 'remove_theme' && $id ) {
			$this->verify_action_nonce( 'wpda_theme_action' );
			// Protect default theme from deletion
			$row = $this->theme_repo->find( $id );
			if ( $row && $row->default ) {
				add_action( 'admin_notices', array( $this, 'notice_cannot_delete_default' ) );
				return;
			}
			$this->theme_repo->delete( $id );
		}
		if ( $task === 'set_default_theme' && $id ) {
			$this->verify_action_nonce( 'wpda_theme_action' );
			$this->theme_repo->set_default( $id );
		}
	}

	/**
	 * Verify nonce for destructive GET actions. wp_die on failure.
	 */
	private function verify_action_nonce( $action ) {
		$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die(
				'Security check failed. Please return and try again.',
				'Security Check',
				array( 'response' => 403, 'back_link' => true )
			);
		}
	}

	public function notice_cannot_delete_default() {
		echo '<div class="notice notice-error is-dismissible"><p>You cannot delete the default theme. Set a different theme as default first.</p></div>';
	}

	// ─── TinyMCE integration ────────────────────────────────────

	public function mce_plugins( $plugin_array ) {
		$plugin_array['wpda_countdown'] = wpda_countdown_plugin_url . 'includes/admin/js/post_page_insert_button.js';
		return $plugin_array;
	}

	public function mce_buttons( $buttons ) {
		$buttons[] = 'wpda_countdown';
		return $buttons;
	}

	// ─── AJAX handlers ──────────────────────────────────────────

	public function ajax_post_popup() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
		require_once wpda_countdown_plugin_path . 'includes/admin/PostPopup.php';
		new wpda_countdown_post_page_popup();
	}

	public function ajax_save_popup() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
		// PopupController already loaded by Plugin.php
		wpda_countdown_popup_page::save_in_db();
	}

	public function ajax_save_sticky_bar() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
		// StickyBarController already loaded by Plugin.php
		wpda_countdown_sticky_bar_page::save_in_db();
	}

	// ─── Integrations ───────────────────────────────────────────

	private function init_woocommerce() {
		require_once wpda_countdown_plugin_path . 'includes/Integration/WooCommerce.php';
		$woo = new wpda_countdown_woocomerce();
		add_action( 'add_meta_boxes', array( $woo, 'add_metabox' ) );
		add_action( 'save_post_product', array( $woo, 'save_metabox' ) );
		add_filter( 'woocommerce_get_settings_pages', array( $woo, 'woocommerce_settings' ) );
	}

	private function init_gutenberg() {
		require_once wpda_countdown_plugin_path . 'includes/Integration/Gutenberg.php';
		new wpda_countdown_gutenberg();
	}

	// ─── Helpers ────────────────────────────────────────────────

	private function is_edit_task( $tasks ) {
		$task = isset( $_GET['task'] ) ? sanitize_text_field( $_GET['task'] ) : '';
		return in_array( $task, $tasks, true );
	}

	private function is_woocommerce_active() {
		return wpda_countdown_is_plugin_active( 'woocommerce/woocommerce.php' );
	}
}
