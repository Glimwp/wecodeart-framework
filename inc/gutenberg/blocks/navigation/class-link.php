<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.3.1
 */

namespace WeCodeArt\Gutenberg\Blocks\Navigation;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Config\Traits\Asset;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use WeCodeArt\Gutenberg\Blocks\Navigation;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_lightness_limit;

/**
 * Gutenberg Navigation Link block.
 */
class Link extends Dynamic {

	use Asset;
	use Singleton;

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
	protected $block_name = 'navigation-link';

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args(): array {
		return [
			'render_callback' => [ $this, 'render' ]
		];
	}

	/**
	 * Dynamically renders the `core/navigation-link` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', $block = null ): string {
		$link_has_id 	= isset( $attributes['id'] ) && is_numeric( $attributes['id'] );
		$is_post_type	= isset( $attributes['kind'] ) && 'post-type' === $attributes['kind'];
		$is_post_type	= $is_post_type || isset( $attributes['type'] ) && ( 'post' === $attributes['type'] || 'page' === $attributes['type'] );
		
		// Don't render the block's subtree if it is a draft.
		if ( $is_post_type && $link_has_id ) {
			$post = get_post( $attributes['id'] );
			if ( 'publish' !== $post->post_status ) {
				return '';
			}
		}

		// Don't render the block's subtree if it has no label.
		if ( empty( $attributes['label'] ) ) {
			return '';
		}

		// Extra classes
		$extras = [
			'mod' 	=> [],
			'icon' 	=> [],
			'menu'	=> []
		];

		$attrs 	= $this->get_wrapper_attributes( $attributes, $block, $extras );

		return wecodeart( 'markup' )::wrap( 'nav-item', [ [
			'tag' 	=> 'li',
			'attrs'	=> $attrs,
		] ], function( $attributes, $block, $extras ) {

			// Nav Link
			$this->render_link( $attributes, $block, $extras );

			// Nav Submenu
			Navigation\Menu::render_dropdown( $block, $extras );

		}, [ $attributes, $block, $extras ], false );
	}

	/**
	 * Renders the link
	 *
	 * @param	array 	$attributes
	 * @param	array 	$extras
	 *
	 * @return	string 	HTML
	 */
	public function render_link( $attributes, $block, $extras ): void {
		wecodeart( 'markup' )::wrap( 'nav-link', [ [
			'tag' 	=> $this->get_link_tag( get_prop( $extras, 'mod', [] ) ),
			'attrs'	=> $this->get_link_attributes( $attributes, $block, $extras ),
		] ], function( $attributes, $extras ) {
			// Divider
			if( in_array( 'dropdown-divider', get_prop( $extras, 'mod', [] ) ) ) {
				echo '&nbsp';
				return;
			}
	
			// Icon
			if( ! empty( $icon = get_prop( $extras, 'icon', [] ) ) ) {
				printf( '<i class="wp-block-navigation-item__icon %s"></i>', esc_attr( join( ' ', $icon ) ) );
			}
	
			// Label
			wecodeart( 'markup' )::wrap( 'nav-label', [ [
				'tag' 	=> 'span',
				'attrs'	=> [
					'class' => 'wp-block-navigation-item__label'
				]
			] ], function( $attributes ) { 
					echo wp_kses_post( get_prop( $attributes, [ 'label' ], '' ) );
			}, [ $attributes ] );

			// Description
			wecodeart( 'markup' )::wrap( 'nav-description', [ [
				'tag' 	=> 'span',
				'attrs'	=> [
					'class' => 'wp-block-navigation-item__description'
				]
			] ], function( $attributes ) { 
					echo wp_kses_post( get_prop( $attributes, [ 'description' ], '' ) );
			}, [ $attributes ] );
		}, [ $attributes, $extras ] );
	}

	/**
	 * Return a string containing a linkmod tag.
	 *
	 * @param	array	$classes
	 * 
	 * @return 	string
	 */
	public function get_link_tag( array $classes = [] ): string  {
		$type 	= 'link'; // Default
		$output = '';

		foreach ( $classes as $class ) {
			if ( empty( $class ) ) {
				continue;
			}

			if ( 'dropdown-header' === $class ) {
				$type = 'header';
			} elseif ( 'dropdown-divider' === $class ) {
				$type = 'divider';
			} elseif ( in_array( $class, [ 'dropdown-text', 'dropdown-item-text' ], true ) ) {
				$type = 'text';
			}
		}

		switch( $type ) :
			case 'header':
				$output = 'h6';
			break;
			case 'text':
				$output = 'span';
			break;
			case 'divider':
				$output = 'div';
			break;
			default: 
				$output = 'a';
			break;
		endswitch;

		return $output;
	}

	/**
	 * Return an array of link attributes.
	 * 
	 * @return 	array
	 */
	public function get_link_attributes( $attributes, $block, $extras ): array {
		// Default links
		$is_active	= get_queried_object_id() === get_prop( $attributes, [ 'id' ] );
		$classes 	= [ 'wp-block-navigation-item__content', 'nav-link' ];
			
		if( $is_active ) {
			$classes[] = 'active';
		}

		$attrs 			= [
			'href'		=> get_prop( $attributes, 'url', '#' ),
			'target' 	=> get_prop( $attributes, 'opensInNewTab' ) === true ? '_blank' : null,
			'rel'		=> get_prop( $attributes, 'rel' ),
			'title'		=> get_prop( $attributes, 'title' ),
			'aria-current'	=> $is_active ? 'page' : null,
		];

		// Home link
		if( get_prop( $block->parsed_block, [ 'blockName' ], '' ) === 'core/home-link' ) {
			$attrs = wp_parse_args( [
				'href' 			=> esc_url( trailingslashit( home_url() ) ),
				'aria-current'	=> wecodeart_if( 'is_front_page' ) ? 'page' : null,
				'rel'			=> 'home'
			], $attrs );

			if( wecodeart_if( 'is_front_page' ) ) {
				$classes[] = 'active';
			}
		}

		// Submenu
		if( count( $block->inner_blocks ) > 0 ) {
			$attrs = wp_parse_args( [
				'href'				=> '#',
				'data-bs-toggle' 	=> 'dropdown',
				'data-bs-auto-close'=> 'outside',
				'aria-haspopup' 	=> 'true',
				'aria-expanded' 	=> 'false',
			], $attrs );

			$classes[] = 'dropdown-toggle';
		}

		// Add class
		$attrs['class'] = join( ' ', array_filter( $classes ) );

		// Modifiers
		foreach ( get_prop( $extras, [ 'mod' ], [] ) as $class ) {
			if ( empty( $class ) ) continue;

			// Add Disabled
			if ( 'disabled' === $class ) {
				$attrs['href'] = '#';
				$attrs['tabindex'] = '-1';
				$attrs['aria-disabled'] = 'true';
				unset( $attrs['target'] );
			}

			// Submenu Elements
			if( in_array( $class, [ 'dropdown-header', 'dropdown-divider', 'dropdown-text', 'dropdown-item-text' ], true ) ) {
				$attrs['class'] = str_replace( 'nav-link', $class, $attrs['class'] );
				unset( $attrs['href'] );
				unset( $attrs['target'] );
			}
		}

		return $attrs;
	}

	/**
	 * Return an array of wrapper attributes.
	 * 
	 * @return 	array
	 */
	public function get_wrapper_attributes( $attributes, $block, &$extras ): array {
		$classes		= [ 'wp-block-navigation-item', 'wp-block-navigation-link', 'nav-item' ];
		$inline_style 	= ''; // Fallback - to do, if styles extension is disabled.
		
		$class_names	= explode( ' ', get_prop( $attributes, [ 'className' ], '' ) );
		$has_dropdown 	= count( array_intersect( $class_names, [ 'dropup', 'dropend', 'dropdown', 'dropstart' ] ) );

		// If we have custom classes, add them.
		if( count( $class_names ) ) {
			$classes = array_merge( $classes, $class_names );
		}

		// If is submenu and we dont already have that class.
		if( count( $block->inner_blocks ) && ! $has_dropdown ) {
			$classes[] = 'dropdown';
		}
		
		// Filter special classes storing them in $extras
		// Supported Mods: .disabled, .dropdown-header, .dropdown-divider, .dropdown-text
		// Supported Icons: Font Awesome 4/5/6, Glypicons
		foreach( $classes as $key => $class ) {
			if ( preg_match( '/^disabled|^sr-only|^screen-reader-text/i', $class ) ) {
				$extras['mod'][] = $class;
				// unset( $classes[ $key ] ); // This will apply to the link
			} elseif ( preg_match( '/^dropdown-header|^dropdown-divider|^dropdown-text|^dropdown-item-text/i', $class ) ) {
				$extras['mod'][] = $class;
				unset( $classes[ $key ] );
			} elseif ( preg_match( '/^fa-(\S*)?|^fa(s|r|l|b)?(\s?)?$/i', $class ) ) {
				$extras['icon'][] = $class;
				unset( $classes[ $key ] );
			} elseif ( preg_match( '/^glyphicon-(\S*)?|^glyphicon(\s?)$/i', $class ) ) {
				$extras['icon'][] = $class;
				unset( $classes[ $key ] );
			} elseif ( preg_match( '/^dropdown-menu-(\S*)/i', $class ) ) {
				$extras['menu'][] = $class;
				unset( $classes[ $key ] );
			}
		}

		return [
			'class'	=> join( ' ', array_filter( $classes ) ),
			'style'	=> $inline_style,
		];
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles(): string {
		return <<<CSS
			/* Nav Link */
			.nav-link {
				display: inline-block;
				padding: var(--wp--nav-link-padding-y) var(--wp--nav-link-padding-x);
				color: var(--wp--nav-link-color);
				font-size: inherit;
				font-weight: inherit;
				transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
			}
			.nav-link:is(:focus,:hover) {
				outline: none;
			}
			.disabled>.nav-link,
			.nav-link.disabled {
				color: var(--wp--nav-link-disabled-color);
				pointer-events: none;
				cursor: default;
			}
			.navbar-nav .nav-link:is(:hover,:focus) {
				color: var(--wp--nav-link-hover-color);
			}
			.navbar-nav .show > .nav-link,
			.navbar-nav .nav-link.active {
				color: var(--wp--navbar-active-color);
			}

			/* Block */
			.wp-block-navigation .wp-block-navigation-item__icon {
				margin-right: .5rem;
			}
			.wp-block-navigation .wp-block-navigation-item__label {
				word-break: normal;
				overflow-wrap: break-word;
			}
			.wp-block-navigation .wp-block-navigation-item__description {
				display: none;
			}
		CSS;
	}
}