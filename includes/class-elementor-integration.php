<?php
/**
 * Elementor Integration Class.
 *
 * @package AdvancedElementorTOC
 */

namespace AdvancedElementorTOC;

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Elementor_Integration
 */
class Elementor_Integration {

	/**
	 * Singleton instance.
	 *
	 * @var Elementor_Integration|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Elementor_Integration
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize integration after WordPress init.
	 */
	public function init() {
		// Check if Elementor is installed and activated.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
			return;
		}

		// Check Elementor version compatibility.
		if ( ! version_compare( ELEMENTOR_VERSION, AETOC_MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Register custom category.
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );

		// Register custom widget (Elementor 3.5.0+ API).
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	/**
	 * Register Advanced Elements category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'advanced-elements',
			[
				'title' => esc_html__( 'Advanced Elements', 'advanced-elementor-toc' ),
				'icon'  => 'eicon-font',
			]
		);
	}

	/**
	 * Register the Table of Contents widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets( $widgets_manager ) {
		require_once AETOC_PLUGIN_DIR . 'includes/class-widget-toc.php';
		$widgets_manager->register( new Widget_TOC() );
	}

	/**
	 * Admin notice when Elementor is not installed or active.
	 */
	public function admin_notice_missing_elementor() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$screen = get_current_screen();
		if ( $screen && in_array( $screen->id, [ 'plugins', 'plugins-network' ], true ) ) {
			$message = sprintf(
				/* translators: 1: Plugin name, 2: Elementor */
				esc_html__( '%1$s requires %2$s to be installed and activated.', 'advanced-elementor-toc' ),
				'<strong>' . esc_html__( 'Advanced Table of Contents for Elementor', 'advanced-elementor-toc' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'advanced-elementor-toc' ) . '</strong>'
			);

			$button_text = esc_html__( 'Install Elementor', 'advanced-elementor-toc' );
			$install_url = wp_nonce_url(
				self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ),
				'install-plugin_elementor'
			);

			if ( file_exists( WP_PLUGIN_DIR . '/elementor/elementor.php' ) ) {
				$button_text = esc_html__( 'Activate Elementor', 'advanced-elementor-toc' );
				$install_url = wp_nonce_url(
					self_admin_url( 'plugins.php?action=activate&plugin=elementor/elementor.php' ),
					'activate-plugin_elementor/elementor.php'
				);
			}

			printf(
				'<div class="notice notice-warning is-dismissible"><p>%1$s</p><p><a href="%2$s" class="button button-primary">%3$s</a></p></div>',
				wp_kses_post( $message ),
				esc_url( $install_url ),
				esc_html( $button_text )
			);
		}
	}

	/**
	 * Admin notice for minimum required Elementor version.
	 */
	public function admin_notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name, 2: Elementor, 3: Required version, 4: Current version */
			esc_html__( '%1$s requires %2$s version %3$s or greater. You are currently running %4$s.', 'advanced-elementor-toc' ),
			'<strong>' . esc_html__( 'Advanced Table of Contents for Elementor', 'advanced-elementor-toc' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'advanced-elementor-toc' ) . '</strong>',
			AETOC_MINIMUM_ELEMENTOR_VERSION,
			ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}
