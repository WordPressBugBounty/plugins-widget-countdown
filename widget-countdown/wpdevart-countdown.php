<?php
/*
Plugin Name: Countdown Wpdevart 
Plugin URI: http://wpdevart.com/wordpress-countdown-plugin/
Description: WordPress Countdown Plugin - Create and embed countdown timers in your posts, pages, and widgets (integrated with WooCommerce and Elementor).
Version: 3.0.0
Author: wpdevart
Author URI: http://wpdevart.com 
License: GPL3 http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'WPDA_COUNTDOWN_IS_PRO' ) )          define( 'WPDA_COUNTDOWN_IS_PRO', false );
if ( ! defined( 'WPDA_COUNTDOWN_VERSION' ) )         define( 'WPDA_COUNTDOWN_VERSION', '11.0' );
if ( ! defined( 'wpdevart_countdown_support_url' ) ) define( 'wpdevart_countdown_support_url', 'https://wordpress.org/support/plugin/widget-countdown' );
if ( ! defined( 'wpda_countdown_plugin_url' ) )      define( 'wpda_countdown_plugin_url', trailingslashit( plugins_url( '', __FILE__ ) ) );
if ( ! defined( 'wpda_countdown_plugin_path' ) )     define( 'wpda_countdown_plugin_path', trailingslashit( plugin_dir_path( __FILE__ ) ) );

require_once wpda_countdown_plugin_path . 'includes/Core/Plugin.php';

$wpdevart_countdown = new Wpda_Countdown_Plugin();
