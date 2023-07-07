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

namespace WeCodeArt\Gutenberg\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Config\Traits\Asset;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
// use function unset;
// use function isset;
// use function empty;
use function end;
use function join;
use function explode;
use function sprintf;
use function is_null;
use function is_array;
use function current;
use function array_merge;
use function array_filter;
use function array_key_exists;
use function add_filter;
use function parse_blocks;
use function wp_unique_id;
use function wp_style_is;
use function wp_script_is;
use function wp_register_style;
use function wp_enqueue_style;
use function wp_enqueue_script;
use function wp_add_inline_style;
use function wp_parse_args;
use function wp_list_filter;
use function strpos;
use function mb_strpos;
use function preg_match;
use function str_replace;
use function sanitize_html_class;
use function block_core_navigation_filter_out_empty_blocks;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_json_color;
use function WeCodeArt\Functions\get_lightness_limit;

/**
 * Gutenberg Navigation block.
 */
class Navigation extends Dynamic {

	use Singleton;
	use Asset;

	/**
	 * Block static.
	 *
	 * @var mixed
	 */
	protected static $responsive_loaded = null;
	protected static $themes_loaded = [];

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
	protected $block_name = 'navigation';

	/**
	 * Init.
	 */
	public function init() {
		\register_block_style( $this->get_block_type(), [
			'name' 	=> 'tabs',
            'label'	=> __( 'Tabs', 'wecodeart' ),
			'inline_style' => static::get_extra_styles( 'tabs' )
		] );

		\register_block_style( $this->get_block_type(), [
			'name' 	=> 'pills',
            'label'	=> __( 'Pills', 'wecodeart' ),
			'inline_style' => static::get_extra_styles( 'pills' )
		] );

		\add_filter( 'block_core_navigation_render_fallback', [ $this, 'fallback' ] );
	}

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args(): array {
		return [
			'render_callback' 		=> [ $this, 'render' ],
			'view_script_handles'	=> []
		];
	}

	/**
	 * Dynamically renders the `core/navigation` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', $block = null ): string {
		if ( $color = get_prop( $attributes, 'rgbTextColor' ) ) {
			$attributes['customTextColor'] = $color;
		}
		
		if ( $color = get_prop( $attributes, 'rgbBackgroundColor' ) ) {
			$attributes['customBackgroundColor'] = $color;
		}

		unset( $attributes['rgbTextColor'], $attributes['rgbBackgroundColor'] );

		$inner_blocks = $block->inner_blocks;
		
		// Ensure that blocks saved with the legacy ref attribute name (navigationMenuId) continue to render.
		if ( array_key_exists( 'navigationMenuId', $attributes ) ) {
			$attributes['ref'] = $attributes['navigationMenuId'];
		}

		// Load inner blocks from the navigation post.
		if ( array_key_exists( 'ref', $attributes ) ) {
			$navigation_post = get_post( $attributes['ref'] );
			
			if ( isset( $navigation_post ) ) { 
				$parsed_blocks = parse_blocks( $navigation_post->post_content );
				$parsed_blocks = block_core_navigation_filter_out_empty_blocks( $parsed_blocks );
				
				$inner_blocks = new \WP_Block_List( $parsed_blocks, $attributes );
			}
		}
		
		// If there are no inner blocks then fallback to rendering an appropriate fallback.
		if ( empty( $inner_blocks ) ) {
			$parsed_blocks 	= block_core_navigation_get_fallback_blocks();

			// Fallback might have been filtered so do basic test for validity.
			if ( empty( $parsed_blocks ) || ! is_array( $parsed_blocks ) ) {
				return '';
			}

			$inner_blocks = new \WP_Block_List( $parsed_blocks, $attributes );
		}

		$block_id	= wp_unique_id( 'wp-navigation-' );
		$attrs		= $this->get_wrapper_attributes( $attributes );

		// This CSS holds the block customization.
		if( ! wp_style_is( 'wp-block-' . $this->block_name . '-custom' ) ) {
			wp_register_style( 'wp-block-' . $this->block_name . '-custom', '', [
				'wp-block-' . $this->block_name
			], wecodeart( 'version' ) );
			wp_enqueue_style( 'wp-block-' . $this->block_name . '-custom' );
		}

		// Add Expand CSS.
		$classname = get_prop( $attrs, [ 'class' ], '' );
		if( strpos( $classname, 'navbar-expand' ) !== false ) {
			wp_add_inline_style( 'wp-block-' . $this->block_name . '-custom', $this->get_responsive_styles( 'expand' ) );
		}

		// Add Themes CSS.
		preg_match( '/navbar-(light|dark)/i', $classname, $navbar_theme );
		if( count( $navbar_theme ) && ! in_array( current( $navbar_theme ), self::$themes_loaded ) ) {

			wp_add_inline_style( 'wp-block-' . $this->block_name . '-custom', $this->get_color_styles( current( $navbar_theme ) ) );
			
			self::$themes_loaded[] = current( $navbar_theme );
		}

		return wecodeart( 'markup' )::wrap( 'navbar', [ [
			'tag' 	=> 'nav',
			'attrs'	=> $attrs
		] ], function( $attributes, $inner_blocks ) use ( $block_id ) {

			// Navbar List HTML
			$html = wecodeart( 'markup' )::wrap( 'navbar-nav', [ [
				'tag' 	=> 'ul',
				'attrs' => [
					'class' => 'wp-block-navigation__container nav navbar-nav',
				]
			] ], function( $inner_blocks ) {
				foreach( $inner_blocks as $inner_block ) echo $this->render_menu_block( $inner_block );
			}, [ $inner_blocks ], false );

			// Is responsive? Render in offcanvas container
			if( get_prop( $attributes, 'overlayMenu' ) !== 'never' ) {

				// Styles
				if( is_null( self::$responsive_loaded ) ) {

					wp_add_inline_style( 'wp-block-' . $this->block_name . '-custom', $this->get_responsive_styles() );
					
					self::$responsive_loaded = true;
				}

				// Components
				wecodeart( 'styles' )->Components->load( [ 'offcanvas' ] );

				// Toggler
				wecodeart_template( 'general/toggler', [
					'id'		=> $block_id,
					'toggle' 	=> 'offcanvas',
					'icon'		=> get_prop( $attributes, 'hasIcon' )
				] );

				// OffCanvas
				wecodeart_template( 'general/offcanvas', [
					'id'		=> $block_id,
					'classes'	=> [ 'offcanvas-start' ],
					'content' 	=> $html,
				] );

				return;
			}

			// Is ok, we use the WP block rendering
			echo $html;

		}, [ $attributes, $inner_blocks ], false );
	}

	/**
	 * Renders menu item wrapper.
	 *
	 * @param 	object 	$block.
	 *
	 * @return 	string 	Rendered block with wrapper block
	 */
	public function render_menu_block( $block ) {
		$html = $block->render();
		
		// For this specific blocks, please wrap them in a <li> for valid markup
		if( in_array( get_prop( $block->parsed_block, 'blockName', '' ), [
			'core/spacer',
			'core/search',
			'core/social-links',
			'core/site-title',
			'core/site-logo',
		] ) ) {
			$classes	= [ 'wp-block-navigation-item', 'nav-item' ];
			$classes[]  = 'nav-item--' . join( '-', explode( '/', get_prop( $block->parsed_block, 'blockName' ) ) );

			return wecodeart( 'markup' )::wrap( 'nav-item', [ [
				'tag' 	=> 'li',
				'attrs'	=> [
					'class'	=> join( ' ', $classes ),
				]
			] ], $html, [], false );
		}

		return $html;
	}

	/**
	 * Get Class Color.
	 *
	 * @param 	array 	$attributes	Attributes
	 * @param 	string 	$key		Attribute name
	 * 
	 * @return 	string 	HEX color code for pallete class
	 */
	public static function get_class_color( $attributes, $key = 'background' ) {
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette' ], [] );
				
		$_keys 		= [
			'overlay-background' 	=> 'overlayBackgroundColor',
			'overlay-text' 			=> 'overlayTextColor',
			'custom-background'		=> 'customBackgroundColor',
			'background' 			=> 'backgroundColor',
			'text'		 			=> 'textColor',
		];

		// Real attribute name.
		$attribute 	= isset( $_keys[$key] ) ? $_keys[$key] : $key;

		// Get custom
		$color 		= get_prop( $attributes, [ 'style', 'color', $attribute ] );
		
		// If not custom, get named.
		if( $color === null ) {
			$color 	= get_prop( $attributes, $attribute, $color );
			$color 	= get_prop( current( wp_list_filter( $palette, [ 'slug' => $color ] ) ), 'color', false );
		}

		// If named not found, fallback to body.
		if( $color === false ) {
			$color 	= get_json_color( [ 'styles', 'color', $key ] );
		}

		return $color;
	}

	/**
	 * Build an array with CSS classes and inline styles defining the colors
	 * which will be applied to the navigation markup in the front-end.
	 *
	 * @param  array $attributes 	Navigation block context.
	 * @return array  	 			CSS classes and inline styles.
	 */
	public function get_colors( $attributes ) {
		$colors = [
			'classes'   => [],
			'styles' 	=> '',
		];
	
		// Text color.
		$has_named_text_color  = get_prop( $attributes, 'textColor', false );
		$has_custom_text_color = get_prop( $attributes, 'customTextColor', false );

		// Overlays - added via CSS because of extra specificity
		
		// Background color.
		$has_named_background_color  = get_prop( $attributes, 'backgroundColor', false );
		$has_custom_background_color = get_prop( $attributes, 'customBackgroundColor', false );
	
		// If has text color.
		if ( $has_custom_text_color || $has_named_text_color ) {
			// Add has-text-color class.
			$colors['classes'][] = 'has-text-color';
		}
	
		if ( $has_named_text_color ) {
			// Add the color class.
			$colors['classes'][] = sprintf( 'has-%s-color', $has_named_text_color );
		} elseif ( $has_custom_text_color ) {
			// Add the custom color inline style.
			$colors['styles'] .= sprintf( 'color: %s;', $has_custom_text_color );
			$colors['styles'] .= sprintf( '--wp--custom--color: %s;', $has_custom_text_color );
		}
	
		// If has background color.
		if ( $has_custom_background_color || $has_named_background_color ) {
			// Add has-background class.
			$colors['classes'][] = 'has-background';
		}
	
		if ( $has_named_background_color ) {
			// Add the background-color class.
			$colors['classes'][] = sprintf( 'has-%s-background-color', $has_named_background_color );
		} elseif ( $has_custom_background_color ) {
			// Add the custom background-color inline style.
			$colors['styles'] .= sprintf( 'background-color: %s;', $has_custom_background_color );
			$colors['styles'] .= sprintf( '--wp--custom--background-color: %s;', $has_custom_background_color );
		}
	
		return $colors;
	}

	/**
	 * Build an array with CSS classes and inline styles defining the font sizes
	 * which will be applied to the navigation markup in the front-end.
	 *
	 * @param  array $attributes 	Navigation block context.
	 * @return array 				Font size CSS classes and inline styles.
	 */
	public function get_typography( $attributes ) {
		// CSS classes.
		$typography = [
			'classes'   => [],
			'styles' 	=> '',
		];

		if ( $value = get_prop( $attributes, 'fontSize' ) ) {
			// Add the font size class.
			$typography['classes'][] = sprintf( 'has-%s-font-size', $value );
		} elseif ( $value = get_prop( $attributes, 'customFontSize' ) ) {
			// Add the custom font size inline style.
			$typography['styles'] = sprintf( 'font-size: %spx;--wp--custom--font-size: %spx;', $value, $value );
		}

		return $typography;
	}

	/**
	 * Return an array of wrapper attributes.
	 * 
	 * @return 	array
	 */
	public function get_wrapper_attributes( $attributes ) {
		$colors     = $this->get_colors( $attributes );
		$typography = $this->get_typography( $attributes );
		$background = get_prop( $attributes, 'customBackgroundColor', self::get_class_color( $attributes ) );
		$background = wecodeart( 'styles' )::color_to_rgba( $background, false, true );
		$luminance 	= wecodeart( 'styles' )::rgb_luminance( $background );
		$classnames = explode( ' ',  get_prop( $attributes, 'className', '' ) );

		$classes 	= [ 'wp-block-navigation', 'navbar' ];
		$excludes 	= [ 'navbar-light', 'navbar-dark' ];

		// Detect navbar theme
		if( count( array_intersect( $classnames, $excludes ) ) === 0 ) {
			$classes[] = ( $luminance < get_lightness_limit() ) ? 'navbar-dark' : 'navbar-light';
		}

		// Navbar orientation
		if( get_prop( $attributes, [ 'layout', 'orientation' ] ) === 'horizontal' ) {
			$classes[] = 'navbar-expand';
		}
		
		// Offcanvas
		if( $value = get_prop( $attributes, 'overlayMenu' ) ) {
			if( $value !== 'never' ) {
				$classes 	= array_diff( $classes, [ 'navbar-expand' ] );
			}

			if( $value === 'mobile' ) {
				$classes[] 	= $this->get_mobile_breakpoint();
			}
		}
		
		// Hover or click
		if( get_prop( $attributes, 'openSubmenusOnClick', false ) === false ) {
			$classes[] 	= 'with-hover';
		}

		// Hide dropdown toggle
		if( get_prop( $attributes, 'showSubmenuIcon', false ) === false ) {
			$classes[] 	= 'hide-toggle';
		}

		$classes    	= array_merge( $classes, $classnames, $colors['classes'], $typography['classes'] );
		$block_styles 	= get_prop( $attributes, 'styles', '' );

		return [
			'class'	=> join( ' ', array_filter( $classes ) ),
			'style'	=> $block_styles . $colors['styles'] . $typography['styles'],
		];
	}

	/**
	 * Return filter mobile breakpoint class.
	 * 
	 * @return 	array
	 */
	public function get_mobile_breakpoint() {
		return sanitize_html_class( 'navbar-expand-' . wecodeart_json( [ 'settings', 'custom', 'mobileBreakpoint' ], 'lg' ) );
	}

	/**
	 * Fallback arguments.
	 *
	 * @return array
	 */
	public function fallback( $blocks ) {
		$blocks[0]['attrs']['__unstableMaxPages'] = 3;

		return $blocks;
	}

	/**
	 * Generate Styles.
	 *
	 * @return string
	 */
	public function get_responsive_styles( $type = 'default' ) {
		$breaks 	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$filter		= explode( '-', Navigation::get_instance()->get_mobile_breakpoint() );
		$filter		= end( $filter );
		$breakpoint	= get_prop( $breaks, $filter, '992px' );

		$inline = '';

		switch( $type ) :
			case 'expand':
				$inline .= "
					.navbar-expand {
						flex-wrap: nowrap;
						justify-content: flex-start;
					}
					.navbar-expand .navbar-nav {
						flex-direction: row;
					}
					.navbar-expand .navbar-nav .dropdown-menu {
						position: absolute;
					}
					.navbar-expand .navbar-nav .nav-link {
						padding-right: 0.5rem;
						padding-left: 0.5rem;
					}
					.navbar-expand .navbar-nav-scroll {
						overflow: visible;
					}
					.navbar-expand .navbar-collapse {
						display: flex !important;
						flex-basis: auto;
					}
					.navbar-expand .navbar-toggler {
						display: none;
					}
					.navbar-expand .offcanvas-header {
						display: none;
					}
					.navbar-expand .offcanvas {
						position: inherit;
						bottom: 0;
						z-index: 1000;
						flex-grow: 1;
						visibility: visible !important;
						background-color: transparent;
						border-right: 0;
						border-left: 0;
						transition: none;
						transform: none;
					}
					.navbar-expand .offcanvas-top,
					.navbar-expand .offcanvas-bottom {
						height: auto;
						border-top: 0;
						border-bottom: 0;
					}
					.navbar-expand .offcanvas-body {
						display: flex;
						flex-grow: 0;
						padding: 0;
						overflow-y: visible;
					}
				";
			break;
			default:
				$inline .= "
					/* Toggler */
					.navbar-toggler {
						padding: var(--wp--navbar-toggler-padding-y) var(--wp--navbar-toggler-padding-x);
						background-color: transparent;
						color: var(--wp--navbar-color);
						font-size: var(--wp--navbar-toggler-font-size, 1.25rem);
						line-height: 1;
						border: 1px solid var(--wp--navbar-toggler-border-color, transparent);
						border-radius: var(--wp--navbar-toggler-border-radius, 0.25rem);
						transition: var(--wp--navbar-toggler-transition, box-shadow 0.15s ease-in-out);
					}
					.navbar-toggler:hover {
						text-decoration: none;
					}
					.navbar-toggler:focus {
						text-decoration: none;
						outline: 0;
						box-shadow: 0 0 0 var(--wp--navbar-toggler-focus-width);
					} 
					.navbar-toggler-icon {
						display: inline-block;
						width: 1.5em;
						height: 1.5em;
						vertical-align: middle;
						background-image: var(--wp--navbar-toggler-icon-bg);
						background-repeat: no-repeat;
						background-position: center;
						background-size: 100%;
					}
				";
				
				foreach( $breaks as $key => $value ) {
					// Skip what we dont need!
					if( $key !== $filter ) continue;

					$inline .= "
					@media (min-width: {$value}) {
						/* Navbar */
						.navbar-expand-{$key} {
							flex-wrap: nowrap;
							justify-content: flex-start;
						}
						.navbar-expand-{$key} .navbar-nav {
							flex-direction: row;
						}
						.navbar-expand-{$key} .navbar-nav .dropdown-menu {
							position: absolute;
						}
						.navbar-expand-{$key} .navbar-nav-scroll {
							overflow: visible;
						}
						.navbar-expand-{$key} .navbar-collapse {
							display: flex !important;
							flex-basis: auto;
						}
						.navbar-expand-{$key} .navbar-toggler {
							display: none;
						}
						.navbar-expand-{$key} .offcanvas-header {
							display: none;
						}
						.navbar-expand-{$key} .offcanvas {
							position: inherit;
							bottom: 0;
							z-index: 1000;
							flex-grow: 1;
							visibility: visible !important;
							background-color: transparent;
							border-right: 0;
							border-left: 0;
							transition: none;
							transform: none;
						}
						.navbar-expand-{$key} .offcanvas-start,
						.navbar-expand-{$key} .offcanvas-end {
							width: auto;
						}
						.navbar-expand-{$key} .offcanvas-top,
						.navbar-expand-{$key} .offcanvas-bottom {
							height: auto;
							border-top: 0;
							border-bottom: 0;
						}
						.navbar-expand-{$key} .offcanvas-body {
							display: flex;
							flex-grow: 0;
							padding: 0;
							overflow-y: visible;
						}

						/* Dropdowns */
						.dropdown-menu-{$key}-start {
							--wp--position: start;
						}
						.dropdown-menu-{$key}-start[data-bs-popper] {
							right: auto;
							left: 0;
						}
						.dropdown-menu-{$key}-end {
							--wp--position: end;
						}
						.dropdown-menu-{$key}-end[data-bs-popper] {
							right: 0;
							left: auto;
						}

						/* Block */
						.wp-block-navigation.navbar-expand-{$key} :where(.offcanvas,.offcanvas-body,.navbar-nav) {
							flex-direction: inherit;
							justify-content: inherit;
							align-items: inherit;
						}
						.wp-block-navigation.navbar-expand-{$key} .wp-block-spacer {
							width: var(--wp--spacer-width);
							vertical-align: -.125em;
							height: 1em;
						}
					}
					";
				}

				$inline .= "
					/* Offcanvas */
					.wp-block-navigation :where(.offcanvas,.offcanvas-body,.navbar-nav) {
						flex: 1 1 100%;
						gap: inherit;
					}
					.wp-block-navigation .offcanvas-start .btn-close {
						margin-left: auto;
					}

					/* No Motion */
					@media (prefers-reduced-motion: reduce) {
						.wp-block-navigation :where(.navbar-toggler,.offcanvas) {
							transition: none;
						}
					}
				";
			break;
		endswitch;

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Color Shade Styles.
	 *
	 * @return string
	 */
	public function get_color_styles( string $color = 'navbar-light' ) {
		$inline = '';
		
		switch( $color ) :
			case 'navbar-dark':
				$inline .= "
					.navbar-dark {
						--wp--emphasis-color-rgb: 255, 255, 255;/* white */
					}
					.navbar-dark .navbar-toggler-icon {
						filter: invert(1) grayscale(100%) brightness(200%);
					}
				";
			break;
			case 'navbar-light':
				$inline .= "
					.navbar-light {
						--wp--emphasis-color-rgb: 0, 0, 0;/* black */
					}
				";
			break;
		endswitch;

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Extra Styles.
	 *
	 * @param	string $variation
	 *
	 * @return 	string
	 */
	public function get_extra_styles( string $variation = 'tabs' ): string {
		$inline = '';

		switch( $variation ) :
			case 'tabs':
				$inline .= "
					.wp-block-navigation.is-style-tabs {
						--wp--nav-tabs-border-width: 1px;
						--wp--nav-tabs-border-color: var(--wp--gray-200);
						--wp--nav-tabs-border-radius: .25rem;
						--wp--nav-tabs-link-hover-border-color: var(--wp--nav-tabs-border-color) var(--wp--nav-tabs-border-color) var(--wp--nav-tabs-border-color);
						--wp--nav-tabs-link-active-color: var(--wp--preset--color--dark);
						--wp--nav-tabs-link-active-bg: var(--wp--white);
						--wp--nav-tabs-link-active-border-color: var(--wp--nav-tabs-border-color) var(--wp--nav-tabs-border-color) var(--wp--white);
						border-bottom: var(--wp--nav-tabs-border-width) solid var(--wp--nav-tabs-border-color);
					}
					.wp-block-navigation.is-style-tabs .navbar-nav {
						--wp--nav-link-padding-x: 1rem;
    					--wp--nav-link-padding-y: .5rem;
					}
					.wp-block-navigation.is-style-tabs .navbar-nav .nav-link {
						margin-bottom: calc(-1 * var(--wp--nav-tabs-border-width));
						background: 0 0;
						border: var(--wp--nav-tabs-border-width) solid transparent;
						border-top-left-radius: var(--wp--nav-tabs-border-radius);
						border-top-right-radius: var(--wp--nav-tabs-border-radius);
					}
					.wp-block-navigation.is-style-tabs .navbar-nav .nav-link:is(:hover,:focus) {
						isolation: isolate;
						border-color: var(--wp--nav-tabs-link-hover-border-color);
					}
					.wp-block-navigation.is-style-tabs .navbar-nav .nav-link.active,
					.wp-block-navigation.is-style-tabs .navbar-nav .nav-item.show .nav-link {
						background-color: var(--wp--nav-tabs-link-active-bg);
						border-color: var(--wp--nav-tabs-link-active-border-color);
						color: var(--wp--nav-tabs-link-active-color);
					}
					.wp-block-navigation.is-style-tabs .navbar-nav .nav-link.disabled {
						background-color: transparent;
						border-color: transparent;
						color: var(--wp--nav-link-disabled-color);
					}
					.wp-block-navigation.is-style-tabs .navbar-nav .dropdown-menu {
						margin-top: calc(-1 * var(--wp--nav-tabs-border-width));
						border-top-left-radius: 0;
						border-top-right-radius: 0;
					}
					.wp-block-navigation.is-style-tabs .tab-content > .tab-pane {
						display: none;
					}
					.wp-block-navigation.is-style-tabs .tab-content > .active {
						display: block;
					}
				";
			break;
			case 'pills':
				$inline .= "
					.wp-block-navigation.is-style-pills {
						--wp--nav-pills-border-radius: .375rem;
						--wp--nav-pills-link-active-color: var(--wp--white);
						--wp--nav-pills-link-active-bg: var(--wp--preset--color--primary);
					}
					.wp-block-navigation.is-style-pills .navbar-nav {
						--wp--nav-link-padding-x: 1rem;
    					--wp--nav-link-padding-y: .5rem;
					}
					.wp-block-navigation.is-style-pills .navbar-nav .nav-link {
						background: none;
						border: 0;
						border-radius: var(--wp--nav-pills-border-radius);
					}
					.wp-block-navigation.is-style-pills .navbar-nav .nav-link.active,
					.wp-block-navigation.is-style-pills .navbar-nav .show > .nav-link {
						color: var(--wp--nav-pills-link-active-color);
    					background-color: var(--wp--nav-pills-link-active-bg);
					}	
				";
			break;
		endswitch;

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles(): string {
		$toggler 	= 'data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"%3e%3cpath stroke="rgba%280, 0, 0, 0.55%29" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2" d="M4 7h22M4 15h22M4 23h22"/%3e%3c/svg%3e';

		return "
			.wp-block-navigation {
				display: flex;
				align-items: center;
			}
			.nav {
				--wp--nav-link-padding-x: 0.5rem;
				--wp--nav-link-padding-y: 0.5rem;
				--wp--nav-link-color: rgba(var(--wp--emphasis-color-rgb), 0.55);
				--wp--nav-link-hover-color: rgba(var(--wp--emphasis-color-rgb), 0.55);
				--wp--nav-link-disabled-color: rgba(var(--wp--emphasis-color-rgb), 0.55);

				display: flex;
				flex-wrap: wrap;
				list-style: none;
				padding-left: 0;
				margin: 0;
			}
			.nav-fill :where(.nav-link,.nav-item) {
				flex: 1 1 auto;
				text-align: center;
			}
			.nav-justified :where(.nav-link,.nav-item) {
				flex-basis: 0;
				flex-grow: 1;
				text-align: center;
			}
			:where(.nav-fill,.nav-justified) .nav-item .nav-link {
				width: 100%;
			}
			
			.navbar {
				--wp--navbar-padding-x: 0;
				--wp--navbar-padding-y: 0.5rem;
				--wp--navbar-color: rgba(var(--wp--emphasis-color-rgb), 0.65);
				--wp--navbar-hover-color: rgba(var(--wp--emphasis-color-rgb), 0.8);
				--wp--navbar-active-color: rgba(var(--wp--emphasis-color-rgb), 1);
				--wp--navbar-disabled-color: rgba(var(--wp--emphasis-color-rgb), 0.3);

				--wp--navbar-toggler-padding-y: 0.25rem;
				--wp--navbar-toggler-padding-x: 0.75rem;
				--wp--navbar-toggler-font-size: 1.25rem;
				--wp--navbar-toggler-icon-bg: url('$toggler');
				--wp--navbar-toggler-border-color: rgba(var(--wp--emphasis-color-rgb),.15);
				--wp--navbar-toggler-border-radius: 0.375rem;
				--wp--navbar-toggler-focus-width: 0.25rem;
				--wp--navbar-toggler-transition: box-shadow 0.15s ease-in-out;

				position: relative;
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				justify-content: space-between;
				padding-top: 0.5rem;
				padding-bottom: 0.5rem;
			}
			.navbar-nav {
				--wp--nav-link-padding-x: 0;
				--wp--nav-link-padding-y: 0;
				--wp--nav-link-color: var(--wp--navbar-color);
				--wp--nav-link-hover-color: var(--wp--navbar-hover-color);
				--wp--nav-link-disabled-color: var(--wp--navbar-disabled-color);
				display: flex;
				flex-direction: column;
				padding-left: 0;
				margin-bottom: 0;
				list-style: none;
			}
			.navbar-nav .dropdown-menu {
				position: static;
			}
			.navbar-text {
				padding-top: 0.5rem;
				padding-bottom: 0.5rem;
			}
			.navbar-collapse {
				flex-basis: 100%;
				flex-grow: 1;
				align-items: center;
			}
			.navbar-nav-scroll {
				max-height: var(--wp--scroll-height, 75vh);
				overflow-y: auto;
			}

			@media (prefers-reduced-motion: reduce) {
				.nav-link {
					transition: none;
				}
			}

			:is(.wp-site-header,.wp-site-footer) .wp-block-navigation {
				padding-top: 0;
				padding-bottom: 0;
			}
		";
	}
}
