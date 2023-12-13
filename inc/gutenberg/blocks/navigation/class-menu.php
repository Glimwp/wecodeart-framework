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
 * @version		6.3.1
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
		if( count( $block->inner_blocks ) === 0 ) {
			return '';
		}

		// Styles
		wecodeart( 'styles' )->Components->load( [ 'dropdown' ] );

		// Scripts
		if( get_prop( $block->context, [ 'openSubmenusOnClick' ] ) && ! wp_script_is( 'wecodeart-support-assets-dropdown' ) ) {
			\wp_enqueue_script( 'wecodeart-support-assets-dropdown' );
		}
		
		// Use overlay first, fallback to nav background (or body).
		$color_type = get_prop( $block->context, 'overlayBackgroundColor' );
		$key_name 	= $color_type ? 'overlay-background' : 'background';
		$background = Navigation::get_class_color( $block->context, $key_name );
		$background = wecodeart( 'styles' )::hex_to_rgb( $background, 1, true );
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
				$inner_html .= $inner_block->render();
			}

			$tag_processor = new \WP_HTML_Tag_Processor( $inner_html );
			while ( $tag_processor->next_tag( [
				'class_name' => 'nav-link'
			] ) ) {
				$tag_processor->remove_class( 'nav-link' );
				$tag_processor->add_class( 'dropdown-item' );
			}
			$inner_html = $tag_processor->get_updated_html();

			echo $inner_html; // WPCS ok - Gutenberg blocks here.
		}, [ $block ], $echo );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles(): string {
		$inline = "
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