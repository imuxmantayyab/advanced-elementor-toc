<?php
/**
 * Admin Settings Page & Management.
 *
 * @package AdvancedElementorTOC
 */

namespace AdvancedElementorTOC;

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin
 */
class Admin {

	/**
	 * Singleton instance.
	 *
	 * @var Admin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Admin
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
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'handle_actions' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_filter( 'plugin_action_links_' . AETOC_PLUGIN_BASENAME, [ $this, 'add_action_links' ] );
	}

	/**
	 * Register submenu under Settings.
	 */
	public function register_admin_menu() {
		add_options_page(
			esc_html__( 'Advanced Table of Contents Settings', 'advanced-elementor-toc' ),
			esc_html__( 'Advanced TOC', 'advanced-elementor-toc' ),
			'manage_options',
			'advanced-elementor-toc',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Add Quick Settings link on Plugins list page.
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=advanced-elementor-toc' ) ),
			esc_html__( 'Settings', 'advanced-elementor-toc' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Enqueue admin CSS if on settings page.
	 *
	 * @param string $hook
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_advanced-elementor-toc' !== $hook ) {
			return;
		}

		wp_add_inline_style(
			'wp-admin',
			'
			.aetoc-admin-wrap { max-width: 900px; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
			.aetoc-card { background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 8px; padding: 24px; margin-bottom: 24px; }
			.aetoc-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 16px; margin-bottom: 20px; }
			.aetoc-header h1 { margin: 0; font-size: 22px; font-weight: 600; color: #1d2327; }
			.aetoc-header .aetoc-badge { background: #2271b1; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 12px; font-weight: bold; }
			.aetoc-tabs { display: flex; gap: 8px; border-bottom: 1px solid #c3c4c7; margin-bottom: 20px; }
			.aetoc-tab-link { padding: 10px 18px; text-decoration: none; color: #50575e; font-weight: 500; font-size: 14px; border: 1px solid transparent; border-bottom: none; border-radius: 6px 6px 0 0; margin-bottom: -1px; }
			.aetoc-tab-link.active { background: #fff; border-color: #c3c4c7; border-bottom-color: #fff; color: #1d2327; font-weight: 600; }
			.aetoc-form-table th { width: 220px; font-weight: 600; padding: 16px 10px 16px 0; }
			.aetoc-form-table td { padding: 16px 10px; }
			.aetoc-status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; }
			.aetoc-status-badge.ok { background: #e7f7ed; color: #007a3d; }
			.aetoc-status-badge.warning { background: #fef7e0; color: #8a6d00; }
			.aetoc-field-desc { color: #646970; font-size: 13px; margin-top: 4px; display: block; }
			'
		);
	}

	/**
	 * Handle admin save and clear cache actions.
	 */
	public function handle_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle Save Settings.
		if ( isset( $_POST['aetoc_save_settings'] ) ) {
			check_admin_referer( 'aetoc_settings_nonce' );

			$enable_plugin      = isset( $_POST['enable_plugin'] ) ? 'yes' : 'no';
			$enable_cache       = isset( $_POST['enable_cache'] ) ? 'yes' : 'no';
			$cache_duration     = isset( $_POST['cache_duration'] ) ? max( 300, (int) $_POST['cache_duration'] ) : 86400;
			$default_headings   = isset( $_POST['default_headings'] ) && is_array( $_POST['default_headings'] )
				? array_map( 'sanitize_text_field', $_POST['default_headings'] )
				: [ 'h2', 'h3', 'h4' ];
			$default_numbering  = isset( $_POST['default_numbering'] ) ? sanitize_text_field( $_POST['default_numbering'] ) : 'numeric';
			$default_smooth     = isset( $_POST['default_smooth_scroll'] ) ? 'yes' : 'no';
			$asset_loading      = isset( $_POST['asset_loading'] ) && in_array( $_POST['asset_loading'], [ 'conditional', 'always' ], true )
				? $_POST['asset_loading']
				: 'conditional';
			$debug_mode         = isset( $_POST['debug_mode'] ) ? 'yes' : 'no';

			$new_settings = [
				'enable_plugin'         => $enable_plugin,
				'enable_cache'          => $enable_cache,
				'cache_duration'        => $cache_duration,
				'default_headings'      => $default_headings,
				'default_numbering'     => $default_numbering,
				'default_smooth_scroll' => $default_smooth,
				'asset_loading'         => $asset_loading,
				'debug_mode'            => $debug_mode,
			];

			update_option( 'aetoc_settings', $new_settings );

			wp_safe_redirect( add_query_arg( [ 'page' => 'advanced-elementor-toc', 'tab' => isset( $_POST['current_tab'] ) ? sanitize_text_field( $_POST['current_tab'] ) : 'general', 'settings-updated' => 'true' ], admin_url( 'options-general.php' ) ) );
			exit;
		}

		// Handle Clear Cache action.
		if ( isset( $_POST['aetoc_clear_cache'] ) ) {
			check_admin_referer( 'aetoc_clear_cache_nonce' );

			TOC_Cache::clear_all_transients();

			wp_safe_redirect( add_query_arg( [ 'page' => 'advanced-elementor-toc', 'tab' => 'performance', 'cache-cleared' => 'true' ], admin_url( 'options-general.php' ) ) );
			exit;
		}
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
		$settings    = get_option( 'aetoc_settings', [] );

		$enable_plugin      = isset( $settings['enable_plugin'] ) ? $settings['enable_plugin'] : 'yes';
		$enable_cache       = isset( $settings['enable_cache'] ) ? $settings['enable_cache'] : 'no';
		$cache_duration     = isset( $settings['cache_duration'] ) ? (int) $settings['cache_duration'] : 86400;
		$default_headings   = isset( $settings['default_headings'] ) ? (array) $settings['default_headings'] : [ 'h2', 'h3', 'h4' ];
		$default_numbering  = isset( $settings['default_numbering'] ) ? $settings['default_numbering'] : 'numeric';
		$default_smooth     = isset( $settings['default_smooth_scroll'] ) ? $settings['default_smooth_scroll'] : 'yes';
		$asset_loading      = isset( $settings['asset_loading'] ) ? $settings['asset_loading'] : 'conditional';
		$debug_mode         = isset( $settings['debug_mode'] ) ? $settings['debug_mode'] : 'no';
		?>
		<div class="wrap aetoc-admin-wrap">
			<div class="aetoc-card">
				<div class="aetoc-header">
					<div>
						<h1><?php esc_html_e( 'Advanced Table of Contents for Elementor', 'advanced-elementor-toc' ); ?></h1>
						<p style="margin: 4px 0 0 0; color: #646970;"><?php esc_html_e( 'Configure global defaults, caching parameters, and verify environment compatibility.', 'advanced-elementor-toc' ); ?></p>
					</div>
					<span class="aetoc-badge">v<?php echo esc_html( AETOC_VERSION ); ?></span>
				</div>

				<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
					<div class="notice notice-success is-dismissible">
						<p><strong><?php esc_html_e( 'Settings saved successfully.', 'advanced-elementor-toc' ); ?></strong></p>
					</div>
				<?php endif; ?>

				<?php if ( isset( $_GET['cache-cleared'] ) ) : ?>
					<div class="notice notice-success is-dismissible">
						<p><strong><?php esc_html_e( 'All TOC cached transients cleared.', 'advanced-elementor-toc' ); ?></strong></p>
					</div>
				<?php endif; ?>

				<nav class="aetoc-tabs" aria-label="<?php esc_attr_e( 'Settings Tabs', 'advanced-elementor-toc' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=advanced-elementor-toc&tab=general' ) ); ?>" class="aetoc-tab-link <?php echo 'general' === $current_tab ? 'active' : ''; ?>">
						<?php esc_html_e( 'General', 'advanced-elementor-toc' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=advanced-elementor-toc&tab=performance' ) ); ?>" class="aetoc-tab-link <?php echo 'performance' === $current_tab ? 'active' : ''; ?>">
						<?php esc_html_e( 'Performance & Cache', 'advanced-elementor-toc' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=advanced-elementor-toc&tab=defaults' ) ); ?>" class="aetoc-tab-link <?php echo 'defaults' === $current_tab ? 'active' : ''; ?>">
						<?php esc_html_e( 'Defaults', 'advanced-elementor-toc' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=advanced-elementor-toc&tab=compatibility' ) ); ?>" class="aetoc-tab-link <?php echo 'compatibility' === $current_tab ? 'active' : ''; ?>">
						<?php esc_html_e( 'System & Compatibility', 'advanced-elementor-toc' ); ?>
					</a>
				</nav>

				<?php if ( 'general' === $current_tab ) : ?>
					<form method="post" action="">
						<?php wp_nonce_field( 'aetoc_settings_nonce' ); ?>
						<input type="hidden" name="current_tab" value="general" />

						<table class="form-table aetoc-form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable Plugin', 'advanced-elementor-toc' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="enable_plugin" value="yes" <?php checked( 'yes', $enable_plugin ); ?> />
										<?php esc_html_e( 'Enable Advanced Table of Contents functionality across Elementor widgets.', 'advanced-elementor-toc' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Asset Loading', 'advanced-elementor-toc' ); ?></th>
								<td>
									<select name="asset_loading">
										<option value="conditional" <?php selected( 'conditional', $asset_loading ); ?>><?php esc_html_e( 'Conditional (Only when widget is present on page)', 'advanced-elementor-toc' ); ?></option>
										<option value="always" <?php selected( 'always', $asset_loading ); ?>><?php esc_html_e( 'Always Load (Site-wide)', 'advanced-elementor-toc' ); ?></option>
									</select>
									<span class="aetoc-field-desc"><?php esc_html_e( 'Conditional loading provides optimal page speed scores by loading assets only when necessary.', 'advanced-elementor-toc' ); ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Debug Mode', 'advanced-elementor-toc' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="debug_mode" value="yes" <?php checked( 'yes', $debug_mode ); ?> />
										<?php esc_html_e( 'Output detailed console logs for TOC parsing and scroll spy tracking.', 'advanced-elementor-toc' ); ?>
									</label>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" name="aetoc_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'advanced-elementor-toc' ); ?>" />
						</p>
					</form>

				<?php elseif ( 'performance' === $current_tab ) : ?>
					<form method="post" action="">
						<?php wp_nonce_field( 'aetoc_settings_nonce' ); ?>
						<input type="hidden" name="current_tab" value="performance" />

						<table class="form-table aetoc-form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable Server Cache', 'advanced-elementor-toc' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="enable_cache" value="yes" <?php checked( 'yes', $enable_cache ); ?> />
										<?php esc_html_e( 'Cache generated TOC headings to reduce server processing on high-traffic sites.', 'advanced-elementor-toc' ); ?>
									</label>
									<span class="aetoc-field-desc"><?php esc_html_e( 'Cache automatically invalidates upon editing posts, pages, or saving Elementor templates.', 'advanced-elementor-toc' ); ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Cache Duration', 'advanced-elementor-toc' ); ?></th>
								<td>
									<select name="cache_duration">
										<option value="3600" <?php selected( 3600, $cache_duration ); ?>><?php esc_html_e( '1 Hour', 'advanced-elementor-toc' ); ?></option>
										<option value="21600" <?php selected( 21600, $cache_duration ); ?>><?php esc_html_e( '6 Hours', 'advanced-elementor-toc' ); ?></option>
										<option value="86400" <?php selected( 86400, $cache_duration ); ?>><?php esc_html_e( '24 Hours (Recommended)', 'advanced-elementor-toc' ); ?></option>
										<option value="604800" <?php selected( 604800, $cache_duration ); ?>><?php esc_html_e( '7 Days', 'advanced-elementor-toc' ); ?></option>
									</select>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" name="aetoc_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'advanced-elementor-toc' ); ?>" />
						</p>
					</form>

					<hr style="margin: 24px 0; border: 0; border-top: 1px solid #eee;" />

					<h3><?php esc_html_e( 'Clear Cached Transients', 'advanced-elementor-toc' ); ?></h3>
					<p style="color: #646970;"><?php esc_html_e( 'Manually flush all saved Table of Contents caches across all posts and templates.', 'advanced-elementor-toc' ); ?></p>
					<form method="post" action="">
						<?php wp_nonce_field( 'aetoc_clear_cache_nonce' ); ?>
						<input type="submit" name="aetoc_clear_cache" class="button button-secondary" value="<?php esc_attr_e( 'Flush All TOC Caches', 'advanced-elementor-toc' ); ?>" />
					</form>

				<?php elseif ( 'defaults' === $current_tab ) : ?>
					<form method="post" action="">
						<?php wp_nonce_field( 'aetoc_settings_nonce' ); ?>
						<input type="hidden" name="current_tab" value="defaults" />

						<table class="form-table aetoc-form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Default Heading Tags', 'advanced-elementor-toc' ); ?></th>
								<td>
									<?php
									$headings_list = [ 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6' ];
									foreach ( $headings_list as $key => $label ) :
									?>
										<label style="margin-right: 14px;">
											<input type="checkbox" name="default_headings[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $default_headings, true ) ); ?> />
											<?php echo esc_html( $label ); ?>
										</label>
									<?php endforeach; ?>
									<span class="aetoc-field-desc"><?php esc_html_e( 'Default headings selected when adding a new TOC widget to any Elementor page.', 'advanced-elementor-toc' ); ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Default Numbering Style', 'advanced-elementor-toc' ); ?></th>
								<td>
									<select name="default_numbering">
										<option value="none" <?php selected( 'none', $default_numbering ); ?>><?php esc_html_e( 'None (Plain)', 'advanced-elementor-toc' ); ?></option>
										<option value="numeric" <?php selected( 'numeric', $default_numbering ); ?>><?php esc_html_e( 'Numeric (1., 2., 3.)', 'advanced-elementor-toc' ); ?></option>
										<option value="hierarchical" <?php selected( 'hierarchical', $default_numbering ); ?>><?php esc_html_e( 'Hierarchical (1.1, 1.2, 1.2.1)', 'advanced-elementor-toc' ); ?></option>
										<option value="alphabetical" <?php selected( 'alphabetical', $default_numbering ); ?>><?php esc_html_e( 'Alphabetical (A., B., C.)', 'advanced-elementor-toc' ); ?></option>
										<option value="roman" <?php selected( 'roman', $default_numbering ); ?>><?php esc_html_e( 'Roman (I., II., III.)', 'advanced-elementor-toc' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Smooth Scrolling', 'advanced-elementor-toc' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="default_smooth_scroll" value="yes" <?php checked( 'yes', $default_smooth ); ?> />
										<?php esc_html_e( 'Enable smooth animated scrolling to heading anchors by default.', 'advanced-elementor-toc' ); ?>
									</label>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" name="aetoc_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'advanced-elementor-toc' ); ?>" />
						</p>
					</form>

				<?php elseif ( 'compatibility' === $current_tab ) : ?>
					<table class="form-table aetoc-form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Elementor Status', 'advanced-elementor-toc' ); ?></th>
							<td>
								<?php if ( did_action( 'elementor/loaded' ) ) : ?>
									<span class="aetoc-status-badge ok">✓ <?php echo esc_html( sprintf( __( 'Active (v%s)', 'advanced-elementor-toc' ), ELEMENTOR_VERSION ) ); ?></span>
								<?php else : ?>
									<span class="aetoc-status-badge warning">⚠ <?php esc_html_e( 'Elementor is not detected or inactive.', 'advanced-elementor-toc' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Elementor Pro', 'advanced-elementor-toc' ); ?></th>
							<td>
								<?php if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) : ?>
									<span class="aetoc-status-badge ok">✓ <?php echo esc_html( sprintf( __( 'Active (v%s)', 'advanced-elementor-toc' ), ELEMENTOR_PRO_VERSION ) ); ?></span>
								<?php else : ?>
									<span style="color: #646970;"><?php esc_html_e( 'Not installed (Optional - free Elementor is fully supported).', 'advanced-elementor-toc' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'PHP Version', 'advanced-elementor-toc' ); ?></th>
							<td>
								<?php if ( version_compare( PHP_VERSION, AETOC_MINIMUM_PHP_VERSION, '>=' ) ) : ?>
									<span class="aetoc-status-badge ok">✓ <?php echo esc_html( PHP_VERSION ); ?></span>
								<?php else : ?>
									<span class="aetoc-status-badge warning">⚠ <?php echo esc_html( sprintf( __( 'PHP %s (Minimum %s required)', 'advanced-elementor-toc' ), PHP_VERSION, AETOC_MINIMUM_PHP_VERSION ) ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'WordPress Version', 'advanced-elementor-toc' ); ?></th>
							<td>
								<span class="aetoc-status-badge ok">✓ <?php echo esc_html( get_bloginfo( 'version' ) ); ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'WooCommerce Compatibility', 'advanced-elementor-toc' ); ?></th>
							<td>
								<?php if ( class_exists( 'WooCommerce' ) ) : ?>
									<span class="aetoc-status-badge ok">✓ <?php esc_html_e( 'Active & Compatible', 'advanced-elementor-toc' ); ?></span>
								<?php else : ?>
									<span style="color: #646970;"><?php esc_html_e( 'Not active (Optional).', 'advanced-elementor-toc' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
