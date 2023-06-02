<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.1.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Navigation;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Config\Traits\Asset;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use WeCodeArt\Gutenberg\Blocks\Navigation;
use WeCodeArt\Gutenberg\Blocks\Navigation\Link;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_lightness_limit;

/**
 * Gutenberg Navigation Submenu block.
 */
class Menu extends Dynamic {

	use Singleton;
	use Asset;

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'navigation-submenu';

	/**
	 * Init.
	 */
	public function init() {
		\add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
	}

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args(): array {
		return [
			'render_callback' => [ Link::get_instance(), 'render' ],
		];
	}

	/**
	 * Renders dropdown
	 *
	 * @param 	object	$block
	 * @param 	array 	$extras
	 *
	 * @return 	void
	 */
	public static function render_dropdown( object $block, array $extras = [], $echo = true ) {
		if( count( $block->inner_blocks ) === 0 ) return;

		// Styles
		if( ! wp_style_is( 'wp-block-navigation-submenu' ) ) {
			wp_enqueue_style( 'wp-block-navigation-submenu' );
		}

		// Scripts
		if( get_prop( $block->context, [ 'openSubmenusOnClick' ] ) && ! wp_script_is( 'wp-block-navigation-submenu' ) ) {
			wp_enqueue_script( 'wp-block-navigation-submenu' );
		}
		
		// Use overlay first, fallback to nav background (or body).
		$color_type = get_prop( $block->context, 'overlayBackgroundColor' );
		$key_name 	= $color_type ? 'overlay-background' : 'background';
		$background = Navigation::get_class_color( $block->context, $key_name );
		$background = wecodeart( 'styles' )::color_to_rgba( $background, false, true );
		$luminance 	= wecodeart( 'styles' )::rgb_luminance( $background );
		
		$classes 	= [ 'wp-block-navigation-link__dropdown', 'dropdown-menu' ];

		if( $extras = get_prop( $extras, [ 'menu' ], [] ) ) {
			$classes = array_merge( $classes, $extras );
		}

		if( ! in_array( 'dropdown-menu-dark', $classes, true ) && $luminance < get_lightness_limit() ) {
			$classes[] = 'dropdown-menu-dark';
		}

		return wecodeart( 'markup' )::wrap( 'nav-dropdown', [ [
			'tag' 	=> 'ul',
			'attrs'	=> [
				'class' => join( ' ', $classes ),
			]
		] ], function( $block ) {
			$inner_html = '';

			foreach ( $block->inner_blocks as $inner_block ) {
				if( property_exists( $block, 'attributes' ) ) {
					// For some reason, WP does not pass this attribute to 2nd level dropdowns.
					$inner_block->attributes['isTopLevelLink'] = false;
				}
				$inner_html .= $inner_block->render();
			}

			echo $inner_html; // WPCS ok - Gutenberg blocks here.
		}, [ $block ], $echo );
	}

	/**
	 * Register assets
	 *
	 * @return 	void
	 */
	public function register_assets(): void {
		\wp_register_script( 'wp-block-navigation-submenu', $this->get_asset( 'js', 'modules/dropdown' ), [], wecodeart( 'version' ), true );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles(): string {
		$symbol = 'data:image/svg+xml;charset=utf8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"%3E%3Cpath d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/%3E%3C/svg%3E';

		$inline = "
			/* Dropdowns */
			.dropup,
			.dropend,
			.dropdown,
			.dropstart {
				--wp--dropdown-icon: url('$symbol');
				position: relative;
			}

			:is(.dropup,.dropend,.dropstart) .dropdown-menu[data-bs-popper] {
				top: 0;
				margin-top: 0;
			}
			.dropstart .dropdown-menu[data-bs-popper] {
				right: 100%;
				left: auto;
				margin-right: var(--wp--dropdown-spacer);
			}
			.dropend .dropdown-menu[data-bs-popper] {
				right: auto;
				left: 100%;
				margin-left: var(--wp--dropdown-spacer);
			}
			.dropup .dropdown-menu[data-bs-popper] {
				top: auto;
				bottom: 100%;
				margin-bottom: var(--wp--dropdown-spacer);
			}

			/* Toggle */
			.dropdown-toggle {
				white-space: nowrap;
			}
			.dropstart>.dropdown-toggle::before,
			:is(.dropup,.dropend,.dropdown)>.dropdown-toggle::after {
				content: '';
				display: inline-block;
				vertical-align: -.15em;
				margin-left: .5em;
				width: .75em;
				height: 1em;
				-webkit-mask-repeat: no-repeat;
				-webkit-mask-position: center;
				-webkit-mask-size: 100%;
				-webkit-mask-image: var(--wp--dropdown-icon);
				background: currentColor;
			}
			.dropstart .dropdown-toggle::before {
				margin-left: 0;
				margin-right: .55em;
				transform: rotate(90deg);
			}
			.dropend .dropdown-toggle::after {
				transform: rotate(-90deg);
			}
			.dropup .dropdown-toggle::after {
				transform: rotate(-180deg);
			}

			/* Dropdown Menu */
			.dropdown-menu {
				--wp--dropdown-zindex: 1000;
				--wp--dropdown-min-width: 10rem;
				--wp--dropdown-padding-x: 0;
				--wp--dropdown-padding-y: .5rem;
				--wp--dropdown-spacer: .125rem;
				--wp--dropdown-color: var(--wp--preset--color--dark);
				--wp--dropdown-bg: var(--wp--white);
				--wp--dropdown-border-width: 1px;
				--wp--dropdown-border-color: var(--wp--gray-200);
				--wp--dropdown-border-radius: .375rem;
				--wp--dropdown-divider-bg: rgba(0,0,0, .05);
				--wp--dropdown-divider-margin-y: .5rem;
				--wp--dropdown-box-shadow: 0 .5rem 1rem rgba(0,0,0, 0.1);
				--wp--dropdown-link-color: var(--wp--preset--color--dark);
				--wp--dropdown-link-hover-color: var(--wp--preset--color--dark);
				--wp--dropdown-link-hover-bg: var(--wp--preset--color--light);
				--wp--dropdown-link-active-color: var(--wp--white);
				--wp--dropdown-link-active-bg: var(--wp--preset--color--primary);
				--wp--dropdown-link-disabled-color: var(--wp--gray-400);
				--wp--dropdown-item-padding-x: 1rem;
				--wp--dropdown-item-padding-y: .25rem;
				--wp--dropdown-header-color: var(--wp--gray-600);
				--wp--dropdown-header-padding-x: 1rem;
				--wp--dropdown-header-padding-y: .5rem;
				position: absolute;
				display: none;
				z-index: var(--wp--dropdown-zindex);
				min-width: var(--wp--dropdown-min-width);
    			padding: var(--wp--dropdown-padding-y) var(--wp--dropdown-padding-x);
				margin: 0;
				text-align: left;
				list-style: none;
				color: var(--wp--dropdown-color);
				background-color: var(--wp--dropdown-bg);
				border: var(--wp--dropdown-border-width) solid var(--wp--dropdown-border-color);
    			border-radius: var(--wp--dropdown-border-radius);
				box-shadow: var(--wp--dropdown-box-shadow);
			}
			.dropdown-menu[data-bs-popper] {
				top: 100%;
				left: 0;
				margin-top: var(--wp--dropdown-spacer);
			}
			.dropdown-menu-dark {
				--wp--dropdown-color: var(--wp--gray-500);
				--wp--dropdown-bg: var(--wp--gray-900);
				--wp--dropdown-border-color: rgba(var(--wp--emphasis-color-rgb), .15);
				--wp--dropdown-divider-bg: rgba(255,255,255,.15);
				--wp--dropdown-link-color: var(--wp--gray-500);
				--wp--dropdown-link-hover-color: var(--wp--white);
				--wp--dropdown-link-hover-bg: rgba(var(--wp--emphasis-color-rgb), .15);
				--wp--dropdown-link-active-color: var(--wp--white);
				--wp--dropdown-link-active-bg: var(--wp--preset--color--primary);
				--wp--dropdown-link-disabled-color: var(--wp--gray-600);
				--wp--dropdown-header-color: var(--wp--gray-500);
			}
			.dropdown-menu-start {
				--wp--position: start;
			}
			.dropdown-menu-start[data-bs-popper] {
				right: auto;
				left: 0;
			}
			.dropdown-menu-end {
				--wp--position: end;
			}
			.dropdown-menu-end[data-bs-popper] {
				right: 0;
				left: auto;
			} 
			
			/* Dropdown Item */
			.dropdown-item {
				display: block;
				width: 100%;
				padding: var(--wp--dropdown-item-padding-y) var(--wp--dropdown-item-padding-x);
				color: var(--wp--dropdown-link-color);
				font-weight: inherit;
				text-align: inherit;
				white-space: nowrap;
				background-color: transparent;
				border: 0;
				border-radius: var(--wp--dropdown-item-border-radius,0);
				clear: both;
			}
			.dropdown-item:is(:hover,:focus) {
				color: var(--wp--dropdown-link-hover-color);
    			background-color: var(--wp--dropdown-link-hover-bg);
			}
			.dropdown-item:is(.active,:active) {
				color: var(--wp--dropdown-link-active-color);
				background-color: var(--wp--dropdown-link-active-bg);
				text-decoration: none;
			}
			.disabled>.dropdown-item,
			.dropdown-item:is(.disabled,:disabled) {
				color: var(--wp--dropdown-link-disabled-color);
				pointer-events: none;
				background-color: transparent;
			}
			.dropdown-menu.show {
				display: block;
			}

			/* Elements */
			.dropdown-header {
				padding: var(--wp--dropdown-header-padding-y) var(--wp--dropdown-header-padding-x);
				margin-bottom: 0;
				color: var(--wp--dropdown-header-color);
				font-size: var(--wp--preset--font-size--small);
				white-space: nowrap;
			}
			.dropdown-text,
			.dropdown-item-text {
				display: block;
				padding: var(--wp--dropdown-header-padding-y) var(--wp--dropdown-header-padding-x);
				color: var(--wp--dropdown-color);
			}
			.dropdown-divider {
				height: 0;
				margin: var(--wp--dropdown-divider-margin-y) 0;
				border-top: 1px solid var(--wp--dropdown-divider-bg);
				overflow: hidden;
			}

			/* Block */
			.wp-block-navigation.hide-toggle .dropdown-toggle::after {
				content: none;
			}
			.wp-block-navigation.with-hover :where(.dropdown,.dropup,.dropstart,.dropend,):where(:hover,:focus,:focus-within) > .dropdown-toggle ~ .dropdown-menu {
				display: block;
				visibility: visible;
				opacity: 1;
			}
			.wp-block-navigation .dropdown-menu .dropdown-toggle::after {
				float: right;
				margin-top: .35em;
			}
		";

		return $inline;
	}
}