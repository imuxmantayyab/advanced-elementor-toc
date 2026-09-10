<?php
/**
 * Plugin Name:       Advanced Table of Contents for Elementor
 * Plugin URI:        https://github.com/imuxmantayyab/advanced-elementor-toc
 * Description:       A powerful, highly customizable, accessible, and high-performance Table of Contents Elementor Widget with live search, reading progress, smooth scroll spy, multi-layout presets, and mobile drawer support.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Usman Tayyab
 * Author URI:        https://www.linkedin.com/in/imuxmantayyab/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       advanced-elementor-toc
 * Domain Path:       /languages
 *
 * @package           AdvancedElementorTOC
 */

namespace AdvancedElementorTOC;

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 */
define( 'AETOC_VERSION', '1.0.0' );
define( 'AETOC_PLUGIN_FILE', __FILE__ );
define( 'AETOC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AETOC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AETOC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'AETOC_MINIMUM_ELEMENTOR_VERSION', '3.5.0' );
define( 'AETOC_MINIMUM_PHP_VERSION', '7.4' );

/**
 * Autoloader for plugin classes.
 *
 * @param string $class_name Class name with namespace.
 */
spl_autoload_register( function ( $class_name ) {
	// Only load classes from our namespace.
	if ( 0 !== strpos( $class_name, __NAMESPACE__ . '\\' ) ) {
		return;
	}

	$relative_class = substr( $class_name, strlen( __NAMESPACE__ . '\\' ) );
	$file_name      = 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';
	$file_path      = AETOC_PLUGIN_DIR . 'includes/' . $file_name;

	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
} );

/**
 * Main plugin bootstrap function.
 */
function aetoc_run() {
	// Check PHP version requirement.
	if ( version_compare( PHP_VERSION, AETOC_MINIMUM_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', function () {
			$message = sprintf(
				/* translators: 1: Plugin name, 2: PHP version required, 3: Current PHP version */
				esc_html__( '%1$s requires PHP version %2$s or higher. Your server is running PHP %3$s. Please upgrade your PHP version.', 'advanced-elementor-toc' ),
				'<strong>' . esc_html__( 'Advanced Table of Contents for Elementor', 'advanced-elementor-toc' ) . '</strong>',
				AETOC_MINIMUM_PHP_VERSION,
				PHP_VERSION
			);
			printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
		} );
		return;
	}

	// Initialize the singleton plugin instance.
	Plugin::instance();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\aetoc_run' );

/**
 * Activation hook.
 */
register_activation_hook( __FILE__, function () {
	// Set default options if they do not exist.
	if ( false === get_option( 'aetoc_settings' ) ) {
		$default_settings = [
			'enable_plugin'         => 'yes',
			'enable_cache'          => 'no',
			'cache_duration'        => 86400,
			'default_headings'      => [ 'h2', 'h3', 'h4' ],
			'default_numbering'     => 'numeric',
			'default_smooth_scroll' => 'yes',
			'asset_loading'         => 'conditional', // conditional or always
			'debug_mode'            => 'no',
		];
		update_option( 'aetoc_settings', $default_settings );
	}
} );

/**
 * Deactivation hook.
 */
register_deactivation_hook( __FILE__, function () {
	// Clear any cached TOC transient entries.
	if ( class_exists( __NAMESPACE__ . '\\TOC_Cache' ) ) {
		TOC_Cache::clear_all_transients();
	}
} );
