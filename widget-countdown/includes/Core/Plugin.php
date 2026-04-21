<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin — the main bootstrap class.
 *
 * Single responsibility: require files, create instances, wire hooks.
 * No business logic. No database access. No HTML output.
 */
class Wpda_Countdown_Plugin {

	private $timer_repo;
	private $theme_repo;
	private $installer;

	public function __construct() {
		$this->load_dependencies();
		$this->create_services();

		// Make wpda_countdown() work BEFORE register_hooks runs — the frontend
		// constructor can instantiate the popup whose own constructor calls
		// PopupController::return_params_array() → wpda_countdown()->timer_repository().
		global $wpdevart_countdown;
		$wpdevart_countdown = $this;

		$this->register_hooks();
	}

	/**
	 * Require all files in the correct order.
	 */
	private function load_dependencies() {
		$base = wpda_countdown_plugin_path . 'includes/';

		// ── Repository ──
		require_once $base . 'Repository/TimerRepository.php';
		require_once $base . 'Repository/ThemeRepository.php';

		// ── Core ──
		require_once $base . 'Core/Installer.php';
		require_once $base . 'Core/Assets.php';

		// ── Legacy animation settings class (used by old widget-countdown shortcode) ──
		if ( ! class_exists( 'wpdevart_countdown_setting' ) ) {
			require_once $base . 'legacy/library.php';
		}

		// ── Admin ──
		require_once $base . 'admin/Fields.php';
		require_once $base . 'admin/AdminMenu.php';
		require_once $base . 'admin/TimerController.php';
		require_once $base . 'admin/ThemeController.php';
		require_once $base . 'admin/PopupController.php';
		require_once $base . 'admin/StickyBarController.php';

		// ── Frontend ──
		require_once $base . 'frontend/CountdownEngine.php';
		require_once $base . 'frontend/Frontend.php';

		// ── Widget ──
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			require_once $base . 'Widget/ProWidget.php';
		}

		// ── Legacy (backward compat shortcode + widget) ──
		require_once $base . 'legacy/front_end.php';
		require_once $base . 'legacy/widget.php';
	}

	/**
	 * Create service instances (no hooks yet).
	 */
	private function create_services() {
		$this->timer_repo = new Wpda_Countdown_Timer_Repository();
		$this->theme_repo = new Wpda_Countdown_Theme_Repository();
		$this->installer  = new Wpda_Countdown_Installer( $this->timer_repo, $this->theme_repo );
	}

	/**
	 * Wire everything to WordPress hooks.
	 */
	private function register_hooks() {
		$plugin_file = wpda_countdown_plugin_path . 'wpdevart-countdown.php';

		// ── Activation ──
		register_activation_hook( $plugin_file, array( $this->installer, 'install' ) );
		register_activation_hook( $plugin_file, array( $this, 'on_activate' ) );

		// ── Safety net: create tables on plugins_loaded (fires before init on
		//    every request, frontend included) and also on admin_init so a
		//    missing-table state self-heals on the next page load even if the
		//    activation hook never fired (reinstall over existing files, FTP
		//    upload, manual DROP TABLE, etc.). The installer uses an option
		//    sentinel so the check is a no-op once tables are verified.
		add_action( 'plugins_loaded', array( $this->installer, 'ensure_tables' ) );
		add_action( 'admin_init', array( $this->installer, 'ensure_tables' ) );

		// ── Assets ──
		$assets = new Wpda_Countdown_Assets();
		add_action( 'init', array( $assets, 'register' ) );

		// ── Text domain ──
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// ── Admin ──
		$admin_menu = new Wpda_Countdown_Admin_Menu(
			$this->timer_repo,
			$this->theme_repo,
			$this->installer
		);
		$admin_menu->init();

		// ── Legacy admin menu (backward compat — registers the legacy shortcode button popup) ──
		require_once wpda_countdown_plugin_path . 'includes/legacy/admin_menu.php';
		new wpdevart_countdown_admin_menu( array(
			'menu_name'          => 'Countdown',
			'databese_parametrs' => null,
		) );

		// ── Frontend ──
		new wpdevart_countdown_frontend();

		// ── Legacy frontend ──
		new wpdevart_countdown_front_end( array(
			'menu_name'          => 'countdown',
			'databese_parametrs' => null,
		) );

		// ── Widgets ──
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		// ── Elementor ──
		add_action( 'elementor/init', array( $this, 'init_elementor' ) );

		// ── Activation notice ──
		add_action( 'admin_notices', array( $this, 'activation_notice' ) );

		// ── Plugins-list row "Upgrade to Pro" link (free version only) ──
		if ( ! WPDA_COUNTDOWN_IS_PRO ) {
			add_filter(
				'plugin_action_links_' . plugin_basename( $plugin_file ),
				array( $this, 'add_upgrade_action_link' )
			);
		}
	}

	/**
	 * Prepend "Upgrade to Pro" link before "Deactivate" in the plugins list row.
	 */
	public function add_upgrade_action_link( $links ) {
		$upgrade = '<a target="_blank" style="color:#7052fb;font-weight:bold;font-size:13px;" href="https://wpdevart.com/wordpress-countdown-plugin/">Upgrade to Pro</a>';
		array_unshift( $links, $upgrade );
		return $links;
	}

	// ─── Hook callbacks ─────────────────────────────────────────

	public function on_activate() {
		set_transient( 'wpda_countdown_activated', true, 60 );
	}

	public function load_textdomain() {
		$rel = basename( rtrim( wpda_countdown_plugin_path, '/\\' ) ) . '/languages';
		load_plugin_textdomain( 'wpdevart_countdown', false, $rel );
		load_plugin_textdomain( 'wpdevart_countdown_n', false, $rel );
	}

	public function register_widgets() {
		register_widget( 'wpdevart_countdown' );
		if ( WPDA_COUNTDOWN_IS_PRO ) {
			register_widget( 'wpdevart_countdown_pro' );
		}
	}

	public function init_elementor() {
		require_once wpda_countdown_plugin_path . 'includes/Integration/Elementor.php';
		new Wpda_Countdown_Elementor();
	}

	public function activation_notice() {
		if ( ! get_transient( 'wpda_countdown_activated' ) ) return;
		delete_transient( 'wpda_countdown_activated' );
		$url = admin_url( 'admin.php?page=wpda_countdown_menu&task=add_wpda_countdown_timer' );
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo '<strong>Countdown ' . ( WPDA_COUNTDOWN_IS_PRO ? 'Pro' : '' ) . ' activated!</strong> ';
		echo '<a href="' . esc_url( $url ) . '">Create your first countdown timer &rarr;</a>';
		echo '</p></div>';
	}

	// ─── Public accessors (for external code that needs repos) ──

	public function timer_repository() {
		return $this->timer_repo;
	}

	public function theme_repository() {
		return $this->theme_repo;
	}
}

/**
 * Global accessor — use this from any file that needs the Plugin instance.
 *
 *     wpda_countdown()->timer_repository()->all_names()
 */
function wpda_countdown() {
	global $wpdevart_countdown;
	return $wpdevart_countdown;
}

/**
 * Multisite-safe check for whether a plugin is active.
 */
function wpda_countdown_is_plugin_active( $plugin ) {
	if ( in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ) {
		return true;
	}
	if ( ! is_multisite() ) {
		return false;
	}
	$sitewide = get_site_option( 'active_sitewide_plugins', array() );
	return isset( $sitewide[ $plugin ] );
}
