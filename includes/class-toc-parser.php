<?php
/**
 * TOC HTML Parser & Hierarchy Builder.
 *
 * @package AdvancedElementorTOC
 */

namespace AdvancedElementorTOC;

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TOC_Parser
 */
class TOC_Parser {

	/**
	 * Unique slug tracker per parse session.
	 *
	 * @var array
	 */
	private $used_slugs = [];

	/**
	 * Settings for parsing.
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * Constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings = [] ) {
		$this->settings = wp_parse_args(
			$settings,
			[
				'headings'             => [ 'h2', 'h3', 'h4' ],
				'exclude_text'         => '',
				'exclude_classes'      => '',
				'exclude_selectors'    => '',
				'exclude_first_n'      => 0,
				'exclude_last_n'       => 0,
				'force_regenerate_ids' => false,
				'numbering_style'      => 'numeric', // none, numeric, hierarchical, alphabetical, roman, custom
				'numbering_prefix'     => '',
				'numbering_separator'  => '.',
				'max_depth'            => 6,
			]
		);
	}

	/**
	 * Parse content string and extract headings.
	 *
	 * @param string $content HTML content.
	 * @return array Array of heading objects: [ 'id', 'text', 'tag', 'level', 'number', 'children' => [] ]
	 */
	public function parse_content( $content ) {
		$this->used_slugs = [];

		if ( empty( $content ) ) {
			return [];
		}

		$allowed_tags = array_map( 'strtolower', (array) $this->settings['headings'] );
		if ( empty( $allowed_tags ) ) {
			return [];
		}

		// Regex to match heading tags H1-H6.
		$tags_regex = implode( '|', $allowed_tags );
		$pattern    = '/<(' . $tags_regex . ')([^>]*)>(.*?)<\/\1>/is';

		if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			return [];
		}

		$raw_items = [];
		$exclude_texts = $this->parse_comma_list( $this->settings['exclude_text'] );
		$exclude_classes = $this->parse_comma_list( $this->settings['exclude_classes'] );

		foreach ( $matches as $match ) {
			$tag        = strtolower( $match[1] );
			$attributes = $match[2];
			$inner_html = $match[3];
			$level      = (int) str_replace( 'h', '', $tag );

			// Strip tags to get clean heading text.
			$text = trim( strip_tags( $inner_html ) );

			if ( '' === $text ) {
				continue;
			}

			// Check excluded text.
			if ( $this->is_text_excluded( $text, $exclude_texts ) ) {
				continue;
			}

			// Check excluded class in attributes.
			if ( $this->has_excluded_class( $attributes, $exclude_classes ) ) {
				continue;
			}

			// Extract or generate ID.
			$id = '';
			if ( ! $this->settings['force_regenerate_ids'] && preg_match( '/id=[\'"]([^\'"]+)[\'"]/i', $attributes, $id_match ) ) {
				$id = sanitize_title( $id_match[1] );
			}

			if ( empty( $id ) ) {
				$id = $this->generate_unique_slug( $text );
			} else {
				$this->used_slugs[] = $id;
			}

			$raw_items[] = [
				'id'       => $id,
				'text'     => $text,
				'tag'      => $tag,
				'level'    => $level,
				'children' => [],
			];
		}

		// Apply exclude first N and last N.
		$total = count( $raw_items );
		$first_n = max( 0, (int) $this->settings['exclude_first_n'] );
		$last_n  = max( 0, (int) $this->settings['exclude_last_n'] );

		if ( $first_n > 0 && $first_n < $total ) {
			$raw_items = array_slice( $raw_items, $first_n );
			$total     = count( $raw_items );
		}

		if ( $last_n > 0 && $last_n < $total ) {
			$raw_items = array_slice( $raw_items, 0, $total - $last_n );
		}

		if ( empty( $raw_items ) ) {
			return [];
		}

		// Build nested tree hierarchy and assign numbering.
		return $this->build_hierarchy_and_numbering( $raw_items );
	}

	/**
	 * Build hierarchical tree and assign numbers.
	 *
	 * @param array $items Flat array of items.
	 * @return array Nested tree of items.
	 */
	public function build_hierarchy_and_numbering( $items ) {
		if ( empty( $items ) ) {
			return [];
		}

		$root     = [];
		$stack    = []; // Stack of references to current parent nodes.
		$counters = [ 0, 0, 0, 0, 0, 0, 0 ]; // Level 1 to 6 counters.

		foreach ( $items as $item ) {
			$level = $item['level'];

			// Adjust stack for current level.
			while ( ! empty( $stack ) && end( $stack )['level'] >= $level ) {
				array_pop( $stack );
			}

			// Reset deeper level counters.
			for ( $i = $level + 1; $i <= 6; $i++ ) {
				$counters[ $i ] = 0;
			}
			$counters[ $level ]++;

			// Calculate formatted number.
			$item['number'] = $this->format_number( $counters, $level );

			if ( empty( $stack ) ) {
				// Top level item.
				$index = count( $root );
				$root[ $index ] = $item;
				$stack[] = [
					'level' => $level,
					'path'  => [ $index ],
				];
			} else {
				// Nested child item.
				$parent_info = end( $stack );
				$parent_ref  = &$this->get_node_by_path( $root, $parent_info['path'] );

				$child_index = count( $parent_ref['children'] );
				$parent_ref['children'][ $child_index ] = $item;

				$new_path   = $parent_info['path'];
				$new_path[] = 'children';
				$new_path[] = $child_index;

				$stack[] = [
					'level' => $level,
					'path'  => $new_path,
				];
			}
		}

		return $root;
	}

	/**
	 * Helper to get a node reference in nested array by path.
	 *
	 * @param array $array
	 * @param array $path
	 * @return array
	 */
	private function &get_node_by_path( &$array, $path ) {
		$current = &$array;
		foreach ( $path as $key ) {
			$current = &$current[ $key ];
		}
		return $current;
	}

	/**
	 * Format number string based on style.
	 *
	 * @param array $counters
	 * @param int   $current_level
	 * @return string
	 */
	private function format_number( $counters, $current_level ) {
		$style     = $this->settings['numbering_style'];
		$prefix    = $this->settings['numbering_prefix'];
		$separator = ! empty( $this->settings['numbering_separator'] ) ? $this->settings['numbering_separator'] : '.';

		if ( 'none' === $style ) {
			return '';
		}

		$formatted = '';

		switch ( $style ) {
			case 'hierarchical':
				// Collect active chain of numbers.
				$parts = [];
				for ( $l = 1; $l <= $current_level; $l++ ) {
					if ( $counters[ $l ] > 0 ) {
						$parts[] = $counters[ $l ];
					}
				}
				$formatted = implode( $separator, $parts );
				break;

			case 'alphabetical':
				$val = $counters[ $current_level ];
				$formatted = $this->number_to_alpha( $val );
				break;

			case 'roman':
				$val = $counters[ $current_level ];
				$formatted = $this->number_to_roman( $val );
				break;

			case 'custom':
				$val = $counters[ $current_level ];
				if ( ! empty( $prefix ) ) {
					$formatted = sprintf( '%s %d', $prefix, $val );
				} else {
					$formatted = (string) $val;
				}
				break;

			case 'numeric':
			default:
				$formatted = (string) $counters[ $current_level ];
				break;
		}

		if ( ! empty( $prefix ) && 'custom' !== $style ) {
			$formatted = $prefix . ' ' . $formatted;
		}

		return $formatted;
	}

	/**
	 * Convert number to letter (1 -> A, 2 -> B, 27 -> AA).
	 *
	 * @param int $num
	 * @return string
	 */
	private function number_to_alpha( $num ) {
		$alpha = '';
		while ( $num > 0 ) {
			$remainder = ( $num - 1 ) % 26;
			$alpha     = chr( 65 + $remainder ) . $alpha;
			$num       = (int) ( ( $num - $remainder ) / 26 );
		}
		return $alpha ?: 'A';
	}

	/**
	 * Convert integer to Roman numeral.
	 *
	 * @param int $num
	 * @return string
	 */
	private function number_to_roman( $num ) {
		$map = [
			'M'  => 1000,
			'CM' => 900,
			'D'  => 500,
			'CD' => 400,
			'C'  => 100,
			'XC' => 90,
			'L'  => 50,
			'XL' => 40,
			'X'  => 10,
			'IX' => 9,
			'V'  => 5,
			'IV' => 4,
			'I'  => 1,
		];
		$res = '';
		foreach ( $map as $roman => $val ) {
			while ( $num >= $val ) {
				$res .= $roman;
				$num -= $val;
			}
		}
		return $res ?: 'I';
	}

	/**
	 * Generate clean, unique, SEO-friendly anchor slug.
	 *
	 * @param string $text
	 * @return string
	 */
	public function generate_unique_slug( $text ) {
		// Clean and generate slug with UTF-8 / multilingual awareness.
		$raw_slug = strip_tags( $text );
		$raw_slug = html_entity_decode( $raw_slug, ENT_QUOTES, 'UTF-8' );
		
		// Remove ampersand cleanly.
		$raw_slug = str_replace( '&', ' and ', $raw_slug );

		// Use WordPress sanitize_title which supports multilingual characters.
		$slug = sanitize_title( $raw_slug );

		if ( empty( $slug ) ) {
			$slug = 'section';
		}

		$base_slug = $slug;
		$counter   = 2;

		while ( in_array( $slug, $this->used_slugs, true ) ) {
			$slug = $base_slug . '-' . $counter;
			$counter++;
		}

		$this->used_slugs[] = $slug;
		return $slug;
	}

	/**
	 * Check if heading text is in excluded list.
	 *
	 * @param string $text
	 * @param array  $excluded_list
	 * @return bool
	 */
	private function is_text_excluded( $text, $excluded_list ) {
		if ( empty( $excluded_list ) ) {
			return false;
		}

		$lower_text = mb_strtolower( trim( $text ), 'UTF-8' );
		foreach ( $excluded_list as $excluded ) {
			$excluded_lower = mb_strtolower( trim( $excluded ), 'UTF-8' );
			if ( '' !== $excluded_lower && ( $lower_text === $excluded_lower || strpos( $lower_text, $excluded_lower ) !== false ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if HTML attributes contain excluded class.
	 *
	 * @param string $attributes
	 * @param array  $excluded_classes
	 * @return bool
	 */
	private function has_excluded_class( $attributes, $excluded_classes ) {
		if ( empty( $excluded_classes ) ) {
			return false;
		}

		if ( ! preg_match( '/class=[\'"]([^\'"]+)[\'"]/i', $attributes, $match ) ) {
			return false;
		}

		$classes = preg_split( '/\s+/', trim( $match[1] ) );
		foreach ( $excluded_classes as $exc_class ) {
			$clean_exc = ltrim( trim( $exc_class ), '.' );
			if ( in_array( $clean_exc, $classes, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Split comma or newline separated string into array.
	 *
	 * @param string $str
	 * @return array
	 */
	private function parse_comma_list( $str ) {
		if ( empty( $str ) || ! is_string( $str ) ) {
			return [];
		}

		$lines = preg_split( '/[\r\n,]+/', $str );
		return array_filter( array_map( 'trim', $lines ) );
	}
}
