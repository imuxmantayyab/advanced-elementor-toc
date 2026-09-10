<?php
/**
 * Advanced Table of Contents Widget for Elementor.
 *
 * @package AdvancedElementorTOC
 */

namespace AdvancedElementorTOC;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Widget_TOC
 */
class Widget_TOC extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'advanced-elementor-toc';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Advanced Table of Contents', 'advanced-elementor-toc' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-table-of-contents';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'advanced-elements', 'general' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'toc', 'table of contents', 'headings', 'navigation', 'scrollspy', 'index', 'seo', 'reading progress' ];
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'advanced-elementor-toc' ];
	}

	/**
	 * Retrieve the list of styles the widget depended on.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'advanced-elementor-toc', 'advanced-elementor-toc-responsive' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		$this->register_content_headings_controls();
		$this->register_content_exclusion_controls();
		$this->register_content_structure_controls();
		$this->register_content_title_controls();
		$this->register_content_collapse_controls();
		$this->register_content_search_progress_controls();
		$this->register_content_sticky_floating_controls();
		$this->register_content_back_to_top_controls();

		// Style Controls.
		$this->register_style_preset_controls();
		$this->register_style_container_controls();
		$this->register_style_title_controls();
		$this->register_style_items_controls();
		$this->register_style_active_item_controls();
		$this->register_style_search_progress_controls();
		$this->register_style_back_to_top_controls();

		// Advanced JS & SEO Controls.
		$this->register_advanced_js_controls();
		$this->register_mobile_experience_controls();
	}

	/**
	 * Content -> Headings Detection Controls.
	 */
	protected function register_content_headings_controls() {
		$this->start_controls_section(
			'section_headings',
			[
				'label' => esc_html__( 'Headings Detection', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'headings_included',
			[
				'label'       => esc_html__( 'Include Headings', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'default'     => [ 'h2', 'h3', 'h4' ],
				'options'     => [
					'h1' => esc_html__( 'H1', 'advanced-elementor-toc' ),
					'h2' => esc_html__( 'H2', 'advanced-elementor-toc' ),
					'h3' => esc_html__( 'H3', 'advanced-elementor-toc' ),
					'h4' => esc_html__( 'H4', 'advanced-elementor-toc' ),
					'h5' => esc_html__( 'H5', 'advanced-elementor-toc' ),
					'h6' => esc_html__( 'H6', 'advanced-elementor-toc' ),
				],
				'description' => esc_html__( 'Select any combination of heading levels to include in the TOC.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'heading_source',
			[
				'label'   => esc_html__( 'Heading Source', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'entire_page',
				'options' => [
					'entire_page'        => esc_html__( 'Entire Page (Body)', 'advanced-elementor-toc' ),
					'post_content'       => esc_html__( 'Post / Entry Content (.entry-content, .post-content)', 'advanced-elementor-toc' ),
					'elementor_content'  => esc_html__( 'Elementor Content (.elementor)', 'advanced-elementor-toc' ),
					'custom_selector'    => esc_html__( 'Custom CSS Selector', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_control(
			'custom_source_selector',
			[
				'label'       => esc_html__( 'Custom CSS Selector', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => '.article-content, .entry-content, #main-content',
				'default'     => '.article-content',
				'condition'   => [
					'heading_source' => 'custom_selector',
				],
				'description' => esc_html__( 'Provide one or more CSS selectors separated by commas.', 'advanced-elementor-toc' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content -> Headings Exclusion Controls.
	 */
	protected function register_content_exclusion_controls() {
		$this->start_controls_section(
			'section_exclusion',
			[
				'label' => esc_html__( 'Heading Exclusion & Filtering', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'exclude_text',
			[
				'label'       => esc_html__( 'Exclude by Heading Text', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => "Introduction\nConclusion\nRelated Posts\nLeave a Reply",
				'description' => esc_html__( 'Exclude headings containing these exact words or phrases (one per line or comma-separated).', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'exclude_classes',
			[
				'label'       => esc_html__( 'Exclude by CSS Class', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => '.no-toc, .exclude-from-toc, .ignore-heading',
				'description' => esc_html__( 'Exclude headings that have any of these CSS classes.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'exclude_selectors',
			[
				'label'       => esc_html__( 'Exclude by CSS Selector', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => '.sidebar h2, .footer h2, .comments-area h3',
				'description' => esc_html__( 'Exclude headings matched by full CSS selectors.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'exclude_first_n',
			[
				'label'       => esc_html__( 'Exclude First N Headings', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 20,
				'step'        => 1,
				'default'     => 0,
				'description' => esc_html__( 'Skip the first N detected headings from the top of the content.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'exclude_last_n',
			[
				'label'       => esc_html__( 'Exclude Last N Headings', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 20,
				'step'        => 1,
				'default'     => 0,
				'description' => esc_html__( 'Skip the last N detected headings from the bottom of the content.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'force_regenerate_ids',
			[
				'label'       => esc_html__( 'Force Regenerate Heading IDs', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'no',
				'description' => esc_html__( 'By default, manually assigned IDs in HTML tags are preserved. Enable to overwrite with clean generated slugs.', 'advanced-elementor-toc' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content -> Structure & Numbering Controls.
	 */
	protected function register_content_structure_controls() {
		$this->start_controls_section(
			'section_structure',
			[
				'label' => esc_html__( 'TOC Structure & Numbering', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'hierarchy_layout',
			[
				'label'   => esc_html__( 'Layout Structure', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'nested',
				'options' => [
					'nested' => esc_html__( 'Nested Tree (Hierarchical Sub-levels)', 'advanced-elementor-toc' ),
					'flat'   => esc_html__( 'Flat List (Single Level Indent)', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_control(
			'numbering_style',
			[
				'label'   => esc_html__( 'Numbering Style', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'numeric',
				'options' => [
					'none'         => esc_html__( 'None (No Numbers)', 'advanced-elementor-toc' ),
					'numeric'      => esc_html__( 'Numeric (1., 2., 3.)', 'advanced-elementor-toc' ),
					'hierarchical' => esc_html__( 'Hierarchical (1.1, 1.2, 1.2.1)', 'advanced-elementor-toc' ),
					'alphabetical' => esc_html__( 'Alphabetical (A., B., C.)', 'advanced-elementor-toc' ),
					'roman'        => esc_html__( 'Roman Numerals (I., II., III.)', 'advanced-elementor-toc' ),
					'custom'       => esc_html__( 'Custom Prefix (Section 1, Part A)', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_control(
			'numbering_prefix',
			[
				'label'       => esc_html__( 'Custom Prefix', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Section',
				'placeholder' => 'Section, Chapter, Part',
				'condition'   => [
					'numbering_style' => 'custom',
				],
			]
		);

		$this->add_control(
			'numbering_separator',
			[
				'label'     => esc_html__( 'Hierarchical Separator', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '.',
				'condition' => [
					'numbering_style' => 'hierarchical',
				],
			]
		);

		$this->add_control(
			'max_depth',
			[
				'label'   => esc_html__( 'Maximum Heading Depth', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '6',
				'options' => [
					'2' => esc_html__( 'H2 only', 'advanced-elementor-toc' ),
					'3' => esc_html__( 'Up to H3 (H2-H3)', 'advanced-elementor-toc' ),
					'4' => esc_html__( 'Up to H4 (H2-H4)', 'advanced-elementor-toc' ),
					'5' => esc_html__( 'Up to H5 (H2-H5)', 'advanced-elementor-toc' ),
					'6' => esc_html__( 'All Depth (H1-H6)', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_control(
			'max_items',
			[
				'label'       => esc_html__( 'Max Visible Items', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 100,
				'default'     => 0,
				'description' => esc_html__( 'Set to 0 for unlimited. When set, excess items will be hidden with a "View More" button.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'view_more_text',
			[
				'label'     => esc_html__( 'View More Button Text', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'View More', 'advanced-elementor-toc' ),
				'condition' => [
					'max_items!' => 0,
				],
			]
		);

		$this->add_control(
			'view_less_text',
			[
				'label'     => esc_html__( 'View Less Button Text', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'View Less', 'advanced-elementor-toc' ),
				'condition' => [
					'max_items!' => 0,
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content -> Title & Header Controls.
	 */
	protected function register_content_title_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'TOC Title & Header', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'   => esc_html__( 'Show Title', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'title_text',
			[
				'label'       => esc_html__( 'Title Text', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => esc_html__( 'Table of Contents', 'advanced-elementor-toc' ),
				'placeholder' => esc_html__( 'Table of Contents', 'advanced-elementor-toc' ),
				'condition'   => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'     => esc_html__( 'Title HTML Tag', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => [
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_icon',
			[
				'label'     => esc_html__( 'Title Icon', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-list-ul',
					'library' => 'fa-solid',
				],
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_icon_position',
			[
				'label'     => esc_html__( 'Icon Position', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => [
					'before' => esc_html__( 'Before Title', 'advanced-elementor-toc' ),
					'after'  => esc_html__( 'After Title', 'advanced-elementor-toc' ),
				],
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_item_count',
			[
				'label'       => esc_html__( 'Show Item Count Badge', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'no',
				'condition'   => [
					'show_title' => 'yes',
				],
				'description' => esc_html__( 'Display the total number of detected sections.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'item_count_format_plural',
			[
				'label'       => esc_html__( 'Count Badge Format (Plural)', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( '{count} Sections', 'advanced-elementor-toc' ),
				'condition'   => [
					'show_title'      => 'yes',
					'show_item_count' => 'yes',
				],
			]
		);

		$this->add_control(
			'item_count_format_singular',
			[
				'label'       => esc_html__( 'Count Badge Format (Singular)', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( '{count} Section', 'advanced-elementor-toc' ),
				'condition'   => [
					'show_title'      => 'yes',
					'show_item_count' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content -> Collapsible & Hierarchy Controls.
	 */
	protected function register_content_collapse_controls() {
		$this->start_controls_section(
			'section_collapsible',
			[
				'label' => esc_html__( 'Collapsible & Sub-Levels', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'collapsible',
			[
				'label'   => esc_html__( 'Collapsible TOC Box', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'initially_collapsed',
			[
				'label'     => esc_html__( 'Initially Collapsed', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => [
					'collapsible' => 'yes',
				],
			]
		);

		$this->add_control(
			'collapsible_subitems',
			[
				'label'       => esc_html__( 'Collapsible Sub-Headings', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'no',
				'description' => esc_html__( 'Allow users to expand/collapse nested sub-levels individually.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'subitems_initially_collapsed',
			[
				'label'     => esc_html__( 'Sub-levels Initially Collapsed', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => [
					'collapsible_subitems' => 'yes',
				],
			]
		);

		$this->add_control(
			'toggle_icon_open',
			[
				'label'     => esc_html__( 'Expand Icon (+)', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
				'condition' => [
					'collapsible' => 'yes',
				],
			]
		);

		$this->add_control(
			'toggle_icon_close',
			[
				'label'     => esc_html__( 'Collapse Icon (−)', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-chevron-up',
					'library' => 'fa-solid',
				],
				'condition' => [
					'collapsible' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label'       => esc_html__( 'Animation Duration (ms)', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 100,
						'max'  => 1000,
						'step' => 50,
					],
				],
				'default'     => [
					'unit' => 'px',
					'size' => 300,
				],
				'condition'   => [
					'collapsible' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content -> Search & Reading Progress Controls.
	 */
	protected function register_content_search_progress_controls() {
		$this->start_controls_section(
			'section_search_progress',
			[
				'label' => esc_html__( 'Search & Reading Progress', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_search',
			[
				'label'       => esc_html__( 'Enable Heading Search Box', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'no',
				'description' => esc_html__( 'Instant client-side filter for large tables of contents without page reload.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'search_placeholder',
			[
				'label'     => esc_html__( 'Search Placeholder Text', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Search headings...', 'advanced-elementor-toc' ),
				'condition' => [
					'enable_search' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_progress',
			[
				'label'       => esc_html__( 'Reading Progress Indicator', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'no',
				'description' => esc_html__( 'Display real-time reading progress bar or percentage.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'progress_mode',
			[
				'label'     => esc_html__( 'Progress Mode', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'horizontal',
				'options'   => [
					'horizontal' => esc_html__( 'Horizontal Bar', 'advanced-elementor-toc' ),
					'circular'   => esc_html__( 'Circular Ring with Percentage', 'advanced-elementor-toc' ),
					'percentage' => esc_html__( 'Percentage Text (e.g. 65%)', 'advanced-elementor-toc' ),
				],
				'condition' => [
					'show_progress' => 'yes',
				],
			]
		);

		$this->add_control(
			'progress_position',
			[
				'label'     => esc_html__( 'Progress Position', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'below_title',
				'options'   => [
					'above_title' => esc_html__( 'Above Title', 'advanced-elementor-toc' ),
					'below_title' => esc_html__( 'Below Title', 'advanced-elementor-toc' ),
					'bottom_box'  => esc_html__( 'Bottom of Card', 'advanced-elementor-toc' ),
					'fixed_top'   => esc_html__( 'Fixed Window Top (Global Scroll)', 'advanced-elementor-toc' ),
				],
				'condition' => [
					'show_progress' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content -> Sticky, Floating & Sidebar Controls.
	 */
	protected function register_content_sticky_floating_controls() {
		$this->start_controls_section(
			'section_sticky_floating',
			[
				'label' => esc_html__( 'Sticky, Floating & Sidebar Layouts', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'sticky_mode',
			[
				'label'   => esc_html__( 'Sticky Behavior', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'disabled',
				'options' => [
					'disabled' => esc_html__( 'Disabled', 'advanced-elementor-toc' ),
					'top'      => esc_html__( 'Sticky Top', 'advanced-elementor-toc' ),
					'bottom'   => esc_html__( 'Sticky Bottom', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_responsive_control(
			'sticky_offset',
			[
				'label'      => esc_html__( 'Sticky Offset (px)', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 100,
				],
				'condition'  => [
					'sticky_mode!' => 'disabled',
				],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-sticky-top'    => 'top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .aetoc-sticky-bottom' => 'bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'floating_mode',
			[
				'label'       => esc_html__( 'Floating Navigation Card', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'no',
				'description' => esc_html__( 'Detaches the TOC into a fixed floating quick-nav widget on the screen.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'floating_position',
			[
				'label'     => esc_html__( 'Floating Position', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bottom_right',
				'options'   => [
					'top_left'      => esc_html__( 'Top Left', 'advanced-elementor-toc' ),
					'top_right'     => esc_html__( 'Top Right', 'advanced-elementor-toc' ),
					'middle_left'   => esc_html__( 'Middle Left', 'advanced-elementor-toc' ),
					'middle_right'  => esc_html__( 'Middle Right', 'advanced-elementor-toc' ),
					'bottom_left'   => esc_html__( 'Bottom Left', 'advanced-elementor-toc' ),
					'bottom_right'  => esc_html__( 'Bottom Right', 'advanced-elementor-toc' ),
				],
				'condition' => [
					'floating_mode' => 'yes',
				],
			]
		);

		$this->add_control(
			'floating_close_button',
			[
				'label'     => esc_html__( 'Allow Minimizing Floating Card', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'floating_mode' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Content -> Back to Top Controls.
	 */
	protected function register_content_back_to_top_controls() {
		$this->start_controls_section(
			'section_back_to_top',
			[
				'label' => esc_html__( 'Back to Top Button', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_back_to_top',
			[
				'label'   => esc_html__( 'Show Back to Top Link', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$this->add_control(
			'back_to_top_text',
			[
				'label'     => esc_html__( 'Back to Top Text', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Back to Top', 'advanced-elementor-toc' ),
				'condition' => [
					'enable_back_to_top' => 'yes',
				],
			]
		);

		$this->add_control(
			'back_to_top_icon',
			[
				'label'     => esc_html__( 'Back to Top Icon', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-arrow-up',
					'library' => 'fa-solid',
				],
				'condition' => [
					'enable_back_to_top' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style -> Design Preset Controls.
	 */
	protected function register_style_preset_controls() {
		$this->start_controls_section(
			'section_style_presets',
			[
				'label' => esc_html__( 'Design Presets', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'preset_theme',
			[
				'label'       => esc_html__( 'Preset Theme', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'modern',
				'options'     => [
					'classic'       => esc_html__( 'Classic (Bordered Box)', 'advanced-elementor-toc' ),
					'minimal'       => esc_html__( 'Minimal (Clean Flat Typography)', 'advanced-elementor-toc' ),
					'modern'        => esc_html__( 'Modern (Rounded Card with Indicators)', 'advanced-elementor-toc' ),
					'sidebar'       => esc_html__( 'Sidebar Navigation', 'advanced-elementor-toc' ),
					'floating'      => esc_html__( 'Floating Quick Card', 'advanced-elementor-toc' ),
					'compact'       => esc_html__( 'Compact Dropdown Bar', 'advanced-elementor-toc' ),
					'documentation' => esc_html__( 'Documentation Handbook Style', 'advanced-elementor-toc' ),
					'glass'         => esc_html__( 'Glassmorphism Card', 'advanced-elementor-toc' ),
				],
				'description' => esc_html__( 'Choose a visual starting point. All controls below remain fully customizable.', 'advanced-elementor-toc' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style -> Container Box Controls.
	 */
	protected function register_style_container_controls() {
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container Box', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label'      => esc_html__( 'Width', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 200, 'max' => 1200 ],
					'%'  => [ 'min' => 10, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-wrapper' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 200, 'max' => 1400 ],
					'%'  => [ 'min' => 10, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_max_height',
			[
				'label'       => esc_html__( 'Max Height (Scrollable Overflow)', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => [ 'px', 'vh' ],
				'range'       => [
					'px' => [ 'min' => 150, 'max' => 1000 ],
				],
				'selectors'   => [
					'{{WRAPPER}} .aetoc-body' => 'max-height: {{SIZE}}{{UNIT}}; overflow-y: auto;',
				],
				'description' => esc_html__( 'Adds a sleek internal scrollbar when headings exceed this height.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_responsive_control(
			'toc_columns',
			[
				'label'     => esc_html__( 'Columns (Multi-column TOC)', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '1',
				'options'   => [
					'1' => esc_html__( '1 Column', 'advanced-elementor-toc' ),
					'2' => esc_html__( '2 Columns', 'advanced-elementor-toc' ),
					'3' => esc_html__( '3 Columns', 'advanced-elementor-toc' ),
					'4' => esc_html__( '4 Columns', 'advanced-elementor-toc' ),
				],
				'selectors' => [
					'{{WRAPPER}} .aetoc-list-root' => 'columns: {{VALUE}}; column-gap: 24px;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_background',
				'label'    => esc_html__( 'Background', 'advanced-elementor-toc' ),
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .aetoc-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'label'    => esc_html__( 'Border', 'advanced-elementor-toc' ),
				'selector' => '{{WRAPPER}} .aetoc-wrapper',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'advanced-elementor-toc' ),
				'selector' => '{{WRAPPER}} .aetoc-wrapper',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Padding', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'      => '20',
					'right'    => '24',
					'bottom'   => '20',
					'left'     => '24',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label'      => esc_html__( 'Margin', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style -> Title & Header Controls.
	 */
	protected function register_style_title_controls() {
		$this->start_controls_section(
			'section_style_title',
			[
				'label'     => esc_html__( 'Title & Header', 'advanced-elementor-toc' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .aetoc-title-text, {{WRAPPER}} .aetoc-header-title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-title-text, {{WRAPPER}} .aetoc-header-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .aetoc-title-icon'                                  => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_icon_color',
			[
				'label'     => esc_html__( 'Icon Specific Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-title-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-title-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .aetoc-title-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_icon_spacing',
			[
				'label'      => esc_html__( 'Icon Spacing', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-icon-before' => 'margin-inline-end: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .aetoc-icon-after'  => 'margin-inline-start: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_toggle_color',
			[
				'label'     => esc_html__( 'Collapse Toggle Button Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-toggle-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__( 'Header Padding', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'title_bottom_border',
				'label'    => esc_html__( 'Header Separator Line', 'advanced-elementor-toc' ),
				'selector' => '{{WRAPPER}} .aetoc-header',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style -> Items & Links Controls.
	 */
	protected function register_style_items_controls() {
		$this->start_controls_section(
			'section_style_items',
			[
				'label' => esc_html__( 'TOC Items & Links', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'item_typography',
				'selector' => '{{WRAPPER}} .aetoc-link',
			]
		);

		$this->start_controls_tabs( 'tabs_item_style' );

		// Normal State.
		$this->start_controls_tab(
			'tab_item_normal',
			[
				'label' => esc_html__( 'Normal', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'item_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_number_color',
			[
				'label'     => esc_html__( 'Number / Prefix Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-item > .aetoc-link-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State.
		$this->start_controls_tab(
			'tab_item_hover',
			[
				'label' => esc_html__( 'Hover', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'item_text_color_hover',
			[
				'label'     => esc_html__( 'Text Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-link:hover, {{WRAPPER}} .aetoc-link:focus' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_number_color_hover',
			[
				'label'     => esc_html__( 'Number Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-link:hover .aetoc-number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-item > .aetoc-link-wrap:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Item Padding', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .aetoc-link-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_margin',
			[
				'label'      => esc_html__( 'Item Spacing (Gap)', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'unit' => 'px',
					'size' => 6,
				],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_indentation_title',
			[
				'label'     => esc_html__( 'Nested Indentation', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'indent_h3',
			[
				'label'      => esc_html__( 'H3 Indentation', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'unit' => 'px', 'size' => 16 ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-level-3' => 'margin-inline-start: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'indent_h4',
			[
				'label'      => esc_html__( 'H4 Indentation', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'unit' => 'px', 'size' => 32 ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-level-4' => 'margin-inline-start: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'indent_h5',
			[
				'label'      => esc_html__( 'H5 Indentation', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'unit' => 'px', 'size' => 48 ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-level-5' => 'margin-inline-start: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'indent_h6',
			[
				'label'      => esc_html__( 'H6 Indentation', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'unit' => 'px', 'size' => 64 ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-level-6' => 'margin-inline-start: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style -> Active Item Design Controls.
	 */
	protected function register_style_active_item_controls() {
		$this->start_controls_section(
			'section_style_active_item',
			[
				'label' => esc_html__( 'Active Item Highlight', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'active_indicator_type',
			[
				'label'   => esc_html__( 'Active Indicator Style', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'border_left',
				'options' => [
					'border_left'  => esc_html__( 'Left Border Bar', 'advanced-elementor-toc' ),
					'border_right' => esc_html__( 'Right Border Bar', 'advanced-elementor-toc' ),
					'dot'          => esc_html__( 'Dot Indicator', 'advanced-elementor-toc' ),
					'background'   => esc_html__( 'Full Background Pill', 'advanced-elementor-toc' ),
					'underline'    => esc_html__( 'Underline Accent', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_control(
			'active_text_color',
			[
				'label'     => esc_html__( 'Active Text Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2271b1',
				'selectors' => [
					'{{WRAPPER}} .aetoc-item.aetoc-active > .aetoc-link-wrap .aetoc-link'   => 'color: {{VALUE}}; font-weight: 600;',
					'{{WRAPPER}} .aetoc-item.aetoc-active > .aetoc-link-wrap .aetoc-number' => 'color: {{VALUE}}; font-weight: 600;',
				],
			]
		);

		$this->add_control(
			'active_bg_color',
			[
				'label'     => esc_html__( 'Active Background Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(34, 113, 177, 0.08)',
				'selectors' => [
					'{{WRAPPER}} .aetoc-item.aetoc-active > .aetoc-link-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'active_indicator_color',
			[
				'label'     => esc_html__( 'Active Indicator Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2271b1',
				'selectors' => [
					'{{WRAPPER}} .aetoc-indicator-border_left .aetoc-item.aetoc-active > .aetoc-link-wrap' => 'border-inline-start-color: {{VALUE}} !important;',
					'{{WRAPPER}} .aetoc-indicator-border_right .aetoc-item.aetoc-active > .aetoc-link-wrap' => 'border-inline-end-color: {{VALUE}} !important;',
					'{{WRAPPER}} .aetoc-indicator-dot .aetoc-item.aetoc-active > .aetoc-link-wrap::before' => 'background-color: {{VALUE}} !important;',
					'{{WRAPPER}} .aetoc-indicator-underline .aetoc-item.aetoc-active > .aetoc-link-wrap .aetoc-link' => 'text-decoration-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'active_indicator_width',
			[
				'label'      => esc_html__( 'Indicator Bar Width', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 1, 'max' => 10 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 3 ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-indicator-border_left .aetoc-item.aetoc-active > .aetoc-link-wrap' => 'border-inline-start-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .aetoc-indicator-border_right .aetoc-item.aetoc-active > .aetoc-link-wrap' => 'border-inline-end-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style -> Search Box & Progress Indicator Controls.
	 */
	protected function register_style_search_progress_controls() {
		$this->start_controls_section(
			'section_style_search_progress',
			[
				'label' => esc_html__( 'Search Box & Reading Progress', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_search_style',
			[
				'label'     => esc_html__( 'Search Input Box', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_input_bg',
			[
				'label'     => esc_html__( 'Search Background', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-search-input' => 'background-color: {{VALUE}};',
				],
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'search_input_color',
			[
				'label'     => esc_html__( 'Search Text Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .aetoc-search-input' => 'color: {{VALUE}};',
				],
				'condition' => [ 'enable_search' => 'yes' ],
			]
		);

		$this->add_control(
			'heading_progress_style',
			[
				'label'     => esc_html__( 'Reading Progress Bar', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'show_progress' => 'yes' ],
			]
		);

		$this->add_control(
			'progress_bar_color',
			[
				'label'     => esc_html__( 'Progress Fill Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2271b1',
				'selectors' => [
					'{{WRAPPER}} .aetoc-progress-bar-fill' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .aetoc-progress-circle-fill' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .aetoc-progress-text' => 'color: {{VALUE}};',
				],
				'condition' => [ 'show_progress' => 'yes' ],
			]
		);

		$this->add_control(
			'progress_track_color',
			[
				'label'     => esc_html__( 'Progress Track Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0, 0, 0, 0.08)',
				'selectors' => [
					'{{WRAPPER}} .aetoc-progress-bar-track' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .aetoc-progress-circle-track' => 'stroke: {{VALUE}};',
				],
				'condition' => [ 'show_progress' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'progress_bar_height',
			[
				'label'      => esc_html__( 'Bar Height (px)', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [ 'px' => [ 'min' => 2, 'max' => 20 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 4 ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-progress-bar-track' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'show_progress' => 'yes',
					'progress_mode' => 'horizontal',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Style -> Back to Top Controls.
	 */
	protected function register_style_back_to_top_controls() {
		$this->start_controls_section(
			'section_style_back_to_top',
			[
				'label'     => esc_html__( 'Back to Top Button', 'advanced-elementor-toc' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_back_to_top' => 'yes',
				],
			]
		);

		$this->add_control(
			'btt_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2271b1',
				'selectors' => [
					'{{WRAPPER}} .aetoc-back-to-top' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btt_text_color',
			[
				'label'     => esc_html__( 'Icon / Text Color', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .aetoc-back-to-top' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'btt_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'advanced-elementor-toc' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [ 'top' => '50', 'right' => '50', 'bottom' => '50', 'left' => '50', 'unit' => '%' ],
				'selectors'  => [
					'{{WRAPPER}} .aetoc-back-to-top' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Advanced JS & Scroll Spy Controls.
	 */
	protected function register_advanced_js_controls() {
		$this->start_controls_section(
			'section_advanced_js',
			[
				'label' => esc_html__( 'Scroll Spy, Offset & URL Hash', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'scroll_spy_mode',
			[
				'label'   => esc_html__( 'Scroll Tracking Engine', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'intersection_observer',
				'options' => [
					'intersection_observer' => esc_html__( 'IntersectionObserver (High Performance)', 'advanced-elementor-toc' ),
					'scroll_spy'            => esc_html__( 'Scroll Event Listener', 'advanced-elementor-toc' ),
					'disabled'              => esc_html__( 'Disabled', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_control(
			'smooth_scroll',
			[
				'label'   => esc_html__( 'Smooth Scroll Animation', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_responsive_control(
			'scroll_offset',
			[
				'label'       => esc_html__( 'Scroll Offset (Header Clearance px)', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [ 'px' => [ 'min' => 0, 'max' => 300 ] ],
				'default'     => [ 'unit' => 'px', 'size' => 90 ],
				'description' => esc_html__( 'Offset to prevent fixed theme headers from overlapping the target heading.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'update_url_hash',
			[
				'label'       => esc_html__( 'Update URL Hash on Click', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Updates the browser address bar with the clicked heading anchor (e.g. #section-name).', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'history_mode',
			[
				'label'     => esc_html__( 'Browser History Mode', 'advanced-elementor-toc' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'replace_state',
				'options'   => [
					'replace_state' => esc_html__( 'Replace State (Recommended - No History Spam)', 'advanced-elementor-toc' ),
					'push_state'    => esc_html__( 'Push State (Creates New History Entry)', 'advanced-elementor-toc' ),
					'none'          => esc_html__( 'None (Do Not Touch History)', 'advanced-elementor-toc' ),
				],
				'condition' => [
					'update_url_hash' => 'yes',
				],
			]
		);

		$this->add_control(
			'scroll_to_hash_on_load',
			[
				'label'       => esc_html__( 'Scroll to Hash on Page Load', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Automatically scrolls smoothly to the anchor if present in URL on initial page visit.', 'advanced-elementor-toc' ),
			]
		);

		$this->add_control(
			'observer_root_margin',
			[
				'label'       => esc_html__( 'Observer Root Margin', 'advanced-elementor-toc' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '-100px 0px -65% 0px',
				'placeholder' => '-100px 0px -65% 0px',
				'condition'   => [
					'scroll_spy_mode' => 'intersection_observer',
				],
				'description' => esc_html__( 'Fine-tune the viewport trigger boundary for active heading detection.', 'advanced-elementor-toc' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Advanced -> Mobile Experience Controls.
	 */
	protected function register_mobile_experience_controls() {
		$this->start_controls_section(
			'section_mobile_experience',
			[
				'label' => esc_html__( 'Mobile Experience & Drawer', 'advanced-elementor-toc' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'mobile_display_mode',
			[
				'label'   => esc_html__( 'Mobile Display Format', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'accordion',
				'options' => [
					'normal'        => esc_html__( 'Standard (In-line Content)', 'advanced-elementor-toc' ),
					'accordion'     => esc_html__( 'Collapsible Accordion', 'advanced-elementor-toc' ),
					'dropdown'      => esc_html__( 'Dropdown Selector', 'advanced-elementor-toc' ),
					'bottom_drawer' => esc_html__( 'Slide-up Bottom Drawer', 'advanced-elementor-toc' ),
					'floating_bar'  => esc_html__( 'Sticky Floating Pill', 'advanced-elementor-toc' ),
				],
			]
		);

		$this->add_control(
			'hide_on_mobile',
			[
				'label'   => esc_html__( 'Hide Entirely on Mobile Devices', 'advanced-elementor-toc' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend and Elementor editor.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Check if plugin is enabled globally.
		$global_plugin = Plugin::instance();
		if ( 'no' === $global_plugin->get_setting( 'enable_plugin', 'yes' ) ) {
			return;
		}

		// Gather configuration attributes for JavaScript engine.
		$config = [
			'headings'             => ! empty( $settings['headings_included'] ) ? (array) $settings['headings_included'] : [ 'h2', 'h3', 'h4' ],
			'source'               => $settings['heading_source'],
			'customSelector'       => ! empty( $settings['custom_source_selector'] ) ? $settings['custom_source_selector'] : '',
			'excludeText'          => ! empty( $settings['exclude_text'] ) ? $settings['exclude_text'] : '',
			'excludeClasses'       => ! empty( $settings['exclude_classes'] ) ? $settings['exclude_classes'] : '',
			'excludeSelectors'     => ! empty( $settings['exclude_selectors'] ) ? $settings['exclude_selectors'] : '',
			'excludeFirstN'        => (int) $settings['exclude_first_n'],
			'excludeLastN'         => (int) $settings['exclude_last_n'],
			'forceRegenerateIds'   => 'yes' === $settings['force_regenerate_ids'],
			'layout'               => $settings['hierarchy_layout'],
			'numberingStyle'       => $settings['numbering_style'],
			'numberingPrefix'      => ! empty( $settings['numbering_prefix'] ) ? $settings['numbering_prefix'] : '',
			'numberingSeparator'   => ! empty( $settings['numbering_separator'] ) ? $settings['numbering_separator'] : '.',
			'maxDepth'             => (int) $settings['max_depth'],
			'maxItems'             => (int) $settings['max_items'],
			'viewMoreText'         => ! empty( $settings['view_more_text'] ) ? $settings['view_more_text'] : esc_html__( 'View More', 'advanced-elementor-toc' ),
			'viewLessText'         => ! empty( $settings['view_less_text'] ) ? $settings['view_less_text'] : esc_html__( 'View Less', 'advanced-elementor-toc' ),
			'collapsible'          => 'yes' === $settings['collapsible'],
			'initiallyCollapsed'   => 'yes' === $settings['initially_collapsed'],
			'collapsibleSubitems'  => 'yes' === $settings['collapsible_subitems'],
			'subitemsInitCollapsed'=> 'yes' === $settings['subitems_initially_collapsed'],
			'animationSpeed'       => ! empty( $settings['animation_speed']['size'] ) ? (int) $settings['animation_speed']['size'] : 300,
			'enableSearch'         => 'yes' === $settings['enable_search'],
			'showProgress'         => 'yes' === $settings['show_progress'],
			'progressMode'         => $settings['progress_mode'],
			'progressPosition'     => $settings['progress_position'],
			'scrollSpyMode'        => $settings['scroll_spy_mode'],
			'smoothScroll'         => 'yes' === $settings['smooth_scroll'],
			'scrollOffset'         => ! empty( $settings['scroll_offset']['size'] ) ? (int) $settings['scroll_offset']['size'] : 90,
			'updateUrlHash'        => 'yes' === $settings['update_url_hash'],
			'historyMode'          => $settings['history_mode'],
			'scrollToHashOnLoad'   => 'yes' === $settings['scroll_to_hash_on_load'],
			'observerRootMargin'   => ! empty( $settings['observer_root_margin'] ) ? $settings['observer_root_margin'] : '-100px 0px -65% 0px',
			'mobileDisplayMode'    => $settings['mobile_display_mode'],
			'hideOnMobile'         => 'yes' === $settings['hide_on_mobile'],
			'countFormatPlural'    => ! empty( $settings['item_count_format_plural'] ) ? $settings['item_count_format_plural'] : esc_html__( '{count} Sections', 'advanced-elementor-toc' ),
			'countFormatSingular'  => ! empty( $settings['item_count_format_singular'] ) ? $settings['item_count_format_singular'] : esc_html__( '{count} Section', 'advanced-elementor-toc' ),
			'isEditor'             => \Elementor\Plugin::$instance->editor->is_edit_mode(),
		];

		// Wrapper CSS classes.
		$preset_class    = 'aetoc-preset-' . sanitize_html_class( $settings['preset_theme'] );
		$indicator_class = 'aetoc-indicator-' . sanitize_html_class( $settings['active_indicator_type'] );
		$sticky_class    = 'disabled' !== $settings['sticky_mode'] ? 'aetoc-sticky-' . sanitize_html_class( $settings['sticky_mode'] ) : '';
		$floating_class  = 'yes' === $settings['floating_mode'] ? 'aetoc-floating aetoc-pos-' . sanitize_html_class( $settings['floating_position'] ) : '';
		$mobile_class    = 'aetoc-mobile-' . sanitize_html_class( $settings['mobile_display_mode'] );
		$hide_mob_class  = 'yes' === $settings['hide_on_mobile'] ? 'aetoc-hide-mobile' : '';

		$wrapper_classes = array_filter( [
			'aetoc-wrapper',
			$preset_class,
			$indicator_class,
			$sticky_class,
			$floating_class,
			$mobile_class,
			$hide_mob_class,
		] );

		$widget_id = $this->get_id();
		$title_tag = ! empty( $settings['title_tag'] ) ? tag_escape( $settings['title_tag'] ) : 'h3';
		?>
		<nav
			class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
			id="aetoc-<?php echo esc_attr( $widget_id ); ?>"
			aria-label="<?php esc_attr_e( 'Table of Contents', 'advanced-elementor-toc' ); ?>"
			data-aetoc-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>"
		>
			<?php if ( 'yes' === $settings['show_progress'] && 'above_title' === $settings['progress_position'] ) : ?>
				<div class="aetoc-progress aetoc-progress-above">
					<div class="aetoc-progress-bar-track" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
						<div class="aetoc-progress-bar-fill"></div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_title'] ) : ?>
				<div class="aetoc-header">
					<<?php echo esc_html( $title_tag ); ?> class="aetoc-header-title">
						<?php if ( ! empty( $settings['title_icon']['value'] ) && 'before' === $settings['title_icon_position'] ) : ?>
							<span class="aetoc-title-icon aetoc-icon-before" aria-hidden="true">
								<?php Icons_Manager::render_icon( $settings['title_icon'], [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>

						<span class="aetoc-title-text"><?php echo esc_html( $settings['title_text'] ); ?></span>

						<?php if ( ! empty( $settings['title_icon']['value'] ) && 'after' === $settings['title_icon_position'] ) : ?>
							<span class="aetoc-title-icon aetoc-icon-after" aria-hidden="true">
								<?php Icons_Manager::render_icon( $settings['title_icon'], [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>

						<?php if ( 'yes' === $settings['show_item_count'] ) : ?>
							<span class="aetoc-count-badge" aria-label="<?php esc_attr_e( 'Number of items', 'advanced-elementor-toc' ); ?>">0</span>
						<?php endif; ?>
					</<?php echo esc_html( $title_tag ); ?>>

					<div class="aetoc-header-actions">
						<?php if ( 'yes' === $settings['show_progress'] && 'circular' === $settings['progress_mode'] ) : ?>
							<div class="aetoc-progress-circular" title="<?php esc_attr_e( 'Reading Progress', 'advanced-elementor-toc' ); ?>">
								<svg viewBox="0 0 36 36" class="aetoc-circular-chart">
									<path class="aetoc-progress-circle-track" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
									<path class="aetoc-progress-circle-fill" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
								</svg>
								<span class="aetoc-circular-percent">0%</span>
							</div>
						<?php elseif ( 'yes' === $settings['show_progress'] && 'percentage' === $settings['progress_mode'] ) : ?>
							<span class="aetoc-progress-text">0%</span>
						<?php endif; ?>

						<?php if ( 'yes' === $settings['collapsible'] ) : ?>
							<button
								type="button"
								class="aetoc-toggle-btn"
								aria-expanded="<?php echo 'yes' === $settings['initially_collapsed'] ? 'false' : 'true'; ?>"
								aria-controls="aetoc-body-<?php echo esc_attr( $widget_id ); ?>"
								aria-label="<?php esc_attr_e( 'Toggle Table of Contents', 'advanced-elementor-toc' ); ?>"
							>
								<span class="aetoc-toggle-icon aetoc-icon-expand" style="<?php echo 'yes' === $settings['initially_collapsed'] ? '' : 'display:none;'; ?>" aria-hidden="true">
									<?php Icons_Manager::render_icon( $settings['toggle_icon_open'], [ 'aria-hidden' => 'true' ] ); ?>
								</span>
								<span class="aetoc-toggle-icon aetoc-icon-collapse" style="<?php echo 'yes' === $settings['initially_collapsed'] ? 'display:none;' : ''; ?>" aria-hidden="true">
									<?php Icons_Manager::render_icon( $settings['toggle_icon_close'], [ 'aria-hidden' => 'true' ] ); ?>
								</span>
							</button>
						<?php endif; ?>

						<?php if ( 'yes' === $settings['floating_mode'] && 'yes' === $settings['floating_close_button'] ) : ?>
							<button
								type="button"
								class="aetoc-floating-close-btn"
								aria-label="<?php esc_attr_e( 'Close Table of Contents', 'advanced-elementor-toc' ); ?>"
							>&times;</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_progress'] && 'below_title' === $settings['progress_position'] ) : ?>
				<div class="aetoc-progress aetoc-progress-below">
					<div class="aetoc-progress-bar-track" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
						<div class="aetoc-progress-bar-fill"></div>
					</div>
				</div>
			<?php endif; ?>

			<div
				class="aetoc-body"
				id="aetoc-body-<?php echo esc_attr( $widget_id ); ?>"
				<?php if ( 'yes' === $settings['collapsible'] && 'yes' === $settings['initially_collapsed'] ) : ?>
					style="display: none;"
				<?php endif; ?>
			>
				<?php if ( 'yes' === $settings['enable_search'] ) : ?>
					<div class="aetoc-search-wrap">
						<input
							type="search"
							class="aetoc-search-input"
							placeholder="<?php echo esc_attr( $settings['search_placeholder'] ); ?>"
							aria-label="<?php echo esc_attr( $settings['search_placeholder'] ); ?>"
						/>
						<span class="aetoc-search-icon" aria-hidden="true">🔍</span>
					</div>
				<?php endif; ?>

				<!-- Dynamic Container Hydrated by JavaScript -->
				<div class="aetoc-content-container">
					<div class="aetoc-loading-state" aria-live="polite">
						<span class="aetoc-spinner" aria-hidden="true"></span>
						<span class="aetoc-loading-text"><?php esc_html_e( 'Building Table of Contents...', 'advanced-elementor-toc' ); ?></span>
					</div>
				</div>

				<?php if ( 'yes' === $settings['enable_back_to_top'] ) : ?>
					<div class="aetoc-footer-actions">
						<a href="#aetoc-<?php echo esc_attr( $widget_id ); ?>" class="aetoc-back-to-top" aria-label="<?php esc_attr_e( 'Scroll to top', 'advanced-elementor-toc' ); ?>">
							<?php if ( ! empty( $settings['back_to_top_icon']['value'] ) ) : ?>
								<span class="aetoc-btt-icon" aria-hidden="true">
									<?php Icons_Manager::render_icon( $settings['back_to_top_icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</span>
							<?php endif; ?>
							<span class="aetoc-btt-text"><?php echo esc_html( $settings['back_to_top_text'] ); ?></span>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( 'yes' === $settings['show_progress'] && 'bottom_box' === $settings['progress_position'] ) : ?>
				<div class="aetoc-progress aetoc-progress-bottom">
					<div class="aetoc-progress-bar-track" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
						<div class="aetoc-progress-bar-fill"></div>
					</div>
				</div>
			<?php endif; ?>
		</nav>
		<?php
	}
}
