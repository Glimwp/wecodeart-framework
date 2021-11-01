<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.1.9
 */

namespace WeCodeArt\Gutenberg\Blocks\Design;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Markup\SVG;
use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Button blocks.
 */
class Button extends Dynamic {

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
	protected $block_name = 'button';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'render_block_core/button', [ $this, 'render' ], 10, 2 );
	}

	/**
	 * Dynamically renders the `core/button` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		$doc = $this->load_html( $content );
		$attributes = get_prop( $block, 'attrs', [] );
		
		// Button Changes
		$link	= $doc->getElementsByTagName( 'a' )->item(0);
		
		if( $link ) {
			$classes 	= explode( ' ', get_prop( $attributes, 'className', '' ) );
			$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'theme' ], [] );
			$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'user' ], $palette );
			$palette 	= wp_list_pluck( $palette, 'slug' );
			
			$color_cls	= [ 'has-background' ];
			foreach( $palette as $slug ) $color_cls[] = 'has-' . $slug . '-background-color';

			$color 		= get_prop( $attributes, 'backgroundColor', false );
			$classname  = array_merge( explode( ' ', $link->getAttribute( 'class' ) ), [ 'btn' ] );
	
			if( in_array( 'is-style-outline', $classes ) ) {
				$color_cls[] = 'has-text-color';
				$color 	= get_prop( $attributes, 'textColor', false );
				if( $color ) {
					foreach( $palette as $slug ) $color_cls[] = 'has-' . $slug . '-color';
					$classname[] = 'btn-outline-' . $color;
				}
			} elseif( $color ) {
				$classname[] = 'btn-' . $color;
			}
			
			$classname	= array_diff( $classname, $color_cls );
			$link->setAttribute( 'class', join( ' ', $classname ) );
		}

		return $this->save_html( $doc->saveHTML() );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
		.wp-block-button.has-custom-width {
			max-width: none;
		}
		.wp-block-button.has-custom-width .wp-block-button__link {
			width: 100%;
		} 
		.wp-block-button.has-custom-font-size .wp-block-button__link {
			font-size: inherit;
		}
		.wp-block-button.wp-block-button__width-25 {
			width: calc(25% - (var(--wp--style--block-gap, .5em) * 0.75));
		}
		.wp-block-button.wp-block-button__width-50 {
			width: calc(50% - (var(--wp--style--block-gap, .5em) * 0.5));
		}
		.wp-block-button.wp-block-button__width-75 {
			width: calc(75% - (var(--wp--style--block-gap, .5em) * 0.25));
		}
		.wp-block-button.wp-block-button__width-100 {
			width: 100%;
		}
		.wp-block-buttons.is-vertical > .wp-block-button.wp-block-button__width-25 {
			width: 25%;
		}
		.wp-block-buttons.is-vertical > .wp-block-button.wp-block-button__width-50 {
			width: 50%;
		}
		.wp-block-buttons.is-vertical > .wp-block-button.wp-block-button__width-75 {
			width: 75%;
		}
		.wp-block-button.aligncenter {
			text-align: center;
		}
		.wp-block-button.alignright {
			text-align: right;
		}
		.wp-block-button__link {
			display: block;
		}
		.wp-block-button__link.no-border-radius {
			border-radius: 0;
		}
		";
	}
}
