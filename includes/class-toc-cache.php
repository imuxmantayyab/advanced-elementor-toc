<?php
/**
 * TOC Cache Manager.
 *
 * @package AdvancedElementorTOC
 */

namespace AdvancedElementorTOC;

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TOC_Cache
 */
class TOC_Cache {

	/**
	 * Transient key prefix.
	 */
	const CACHE_PREFIX = 'aetoc_cache_';

	/**
	 * Transient registry key (to track all active transients).
	 */
	const REGISTRY_KEY = 'aetoc_cache_registry';

	/**
	 * Initialize cache hooks.
	 */
	public static function init() {
		add_action( 'save_post', [ __CLASS__, 'invalidate_post_cache' ], 10, 2 );
		add_action( 'edit_post', [ __CLASS__, 'invalidate_post_cache' ], 10, 2 );
		add_action( 'delete_post', [ __CLASS__, 'invalidate_post_cache' ], 10, 2 );
		add_action( 'elementor/editor/after_save', [ __CLASS__, 'invalidate_elementor_cache' ], 10, 2 );
	}

	/**
	 * Check if caching is enabled globally.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$settings = get_option( 'aetoc_settings', [] );
		return isset( $settings['enable_cache'] ) && 'yes' === $settings['enable_cache'];
	}

	/**
	 * Get cache duration in seconds.
	 *
	 * @return int
	 */
	public static function get_duration() {
		$settings = get_option( 'aetoc_settings', [] );
		return isset( $settings['cache_duration'] ) ? max( 300, (int) $settings['cache_duration'] ) : 86400;
	}

	/**
	 * Get cached TOC data for a specific post and widget instance.
	 *
	 * @param int    $post_id
	 * @param string $widget_id
	 * @return mixed false if miss, array if hit
	 */
	public static function get( $post_id, $widget_id ) {
		if ( ! self::is_enabled() || ! $post_id ) {
			return false;
		}

		$cache_key = self::generate_key( $post_id, $widget_id );
		return get_transient( $cache_key );
	}

	/**
	 * Set cached TOC data.
	 *
	 * @param int    $post_id
	 * @param string $widget_id
	 * @param mixed  $data
	 * @return bool
	 */
	public static function set( $post_id, $widget_id, $data ) {
		if ( ! self::is_enabled() || ! $post_id ) {
			return false;
		}

		$cache_key = self::generate_key( $post_id, $widget_id );
		$duration  = self::get_duration();

		// Record key in registry for easy clearing.
		self::register_key( $cache_key, $post_id );

		return set_transient( $cache_key, $data, $duration );
	}

	/**
	 * Invalidate cache for a specific post.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public static function invalidate_post_cache( $post_id, $post = null ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$registry = get_option( self::REGISTRY_KEY, [] );
		if ( ! empty( $registry[ $post_id ] ) ) {
			foreach ( $registry[ $post_id ] as $key ) {
				delete_transient( $key );
			}
			unset( $registry[ $post_id ] );
			update_option( self::REGISTRY_KEY, $registry, false );
		}
	}

	/**
	 * Invalidate cache on Elementor editor save.
	 *
	 * @param int   $post_id
	 * @param array $editor_data
	 */
	public static function invalidate_elementor_cache( $post_id, $editor_data = [] ) {
		self::invalidate_post_cache( $post_id );
	}

	/**
	 * Clear all plugin transients.
	 */
	public static function clear_all_transients() {
		global $wpdb;

		// Clear tracked transients.
		$registry = get_option( self::REGISTRY_KEY, [] );
		if ( ! empty( $registry ) ) {
			foreach ( $registry as $post_id => $keys ) {
				if ( is_array( $keys ) ) {
					foreach ( $keys as $k ) {
						delete_transient( $k );
					}
				}
			}
			delete_option( self::REGISTRY_KEY );
		}

		// Fallback query for transients matching prefix.
		$pattern = '_transient_' . self::CACHE_PREFIX . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $pattern ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_' . self::CACHE_PREFIX . '%' ) );
	}

	/**
	 * Generate transient key.
	 *
	 * @param int    $post_id
	 * @param string $widget_id
	 * @return string
	 */
	private static function generate_key( $post_id, $widget_id ) {
		return self::CACHE_PREFIX . md5( $post_id . '_' . $widget_id );
	}

	/**
	 * Track key in registry.
	 *
	 * @param string $key
	 * @param int    $post_id
	 */
	private static function register_key( $key, $post_id ) {
		$registry = get_option( self::REGISTRY_KEY, [] );
		if ( ! isset( $registry[ $post_id ] ) ) {
			$registry[ $post_id ] = [];
		}
		if ( ! in_array( $key, $registry[ $post_id ], true ) ) {
			$registry[ $post_id ][] = $key;
			update_option( self::REGISTRY_KEY, $registry, false );
		}
	}
}
