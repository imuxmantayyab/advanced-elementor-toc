<?php
/**
 * Main Plugin Class.
 *
 * @package AdvancedElementorTOC
 */

namespace AdvancedElementorTOC;

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Settings cache.
	 *
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
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
		$this->init_hooks();
	}

	/**
	 * Initialize plugin hooks.
	 */
	private function init_hooks() {
		// Load textdomain.
		add_action( 'init', [ $this, 'load_textdomain' ] );

		// Initialize Elementor integration.
		Elementor_Integration::instance();

		// Initialize Admin settings if in admin area.
		if ( is_admin() ) {
			Admin::instance();
		}

		// Register scripts & styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_assets' ] );
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_assets' ] );

		// Register cache invalidation hooks.
		TOC_Cache::init();
	}

	/**
	 * Load translation textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'advanced-elementor-toc',
			false,
			dirname( AETOC_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Get plugin settings.
	 *
	 * @param string $key Optional specific setting key.
	 * @param mixed  $default Default value if key is not found.
	 * @return mixed
	 */
	public function get_setting( $key = '', $default = null ) {
		if ( is_null( $this->settings ) ) {
			$this->settings = (array) get_option( 'aetoc_settings', [] );
		}

		if ( empty( $key ) ) {
			return $this->settings;
		}

		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
	}

	/**
	 * Register frontend scripts and styles.
	 */
	public function register_assets() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '';

		// Register Main CSS.
		wp_register_style(
			'advanced-elementor-toc',
			AETOC_PLUGIN_URL . 'assets/css/toc' . $suffix . '.css',
			[],
			AETOC_VERSION
		);

		// Register Responsive CSS.
		wp_register_style(
			'advanced-elementor-toc-responsive',
			AETOC_PLUGIN_URL . 'assets/css/toc-responsive' . $suffix . '.css',
			[ 'advanced-elementor-toc' ],
			AETOC_VERSION
		);

		// Register Vanilla JS engine.
		wp_register_script(
			'advanced-elementor-toc',
			AETOC_PLUGIN_URL . 'assets/js/toc' . $suffix . '.js',
			[], // Pure vanilla JS, no jQuery required!
			AETOC_VERSION,
			true
		);

		// Localize script with global strings & defaults.
		wp_localize_script(
			'advanced-elementor-toc',
			'aetocData',
			[
				'i18n' => [
					'noHeadings'       => esc_html__( 'No headings found.', 'advanced-elementor-toc' ),
					'noHeadingsEditor' => esc_html__( 'No headings found. Add H2-H6 headings to your page to generate the Table of Contents.', 'advanced-elementor-toc' ),
					'searchPlaceholder'=> esc_html__( 'Search headings...', 'advanced-elementor-toc' ),
					'viewMore'         => esc_html__( 'View More', 'advanced-elementor-toc' ),
					'viewLess'         => esc_html__( 'View Less', 'advanced-elementor-toc' ),
					'backToTop'        => esc_html__( 'Back to Top', 'advanced-elementor-toc' ),
					'collapse'         => esc_html__( 'Collapse', 'advanced-elementor-toc' ),
					'expand'           => esc_html__( 'Expand', 'advanced-elementor-toc' ),
					'toc'              => esc_html__( 'Table of Contents', 'advanced-elementor-toc' ),
				],
				'isRtl'     => is_rtl(),
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'debugMode' => 'yes' === $this->get_setting( 'debug_mode', 'no' ),
			]
		);

		// If global asset loading is set to 'always', enqueue immediately.
		if ( 'always' === $this->get_setting( 'asset_loading', 'conditional' ) ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Enqueue assets when needed.
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'advanced-elementor-toc' );
		wp_enqueue_style( 'advanced-elementor-toc-responsive' );
		wp_enqueue_script( 'advanced-elementor-toc' );
	}

	/**
	 * Enqueue editor assets so preview and live controls work smoothly.
	 */
	public function enqueue_editor_assets() {
		$this->enqueue_assets();
	}
}
