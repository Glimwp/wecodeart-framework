<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg\Modules\Styles\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Modules\Styles\Processor;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_lightness_limit;

/**
 * Block CSS Processor
 */
class Button extends Processor {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	void
	 */
	protected function process_extra(): void {
		$is_outline = str_contains( get_prop( $this->attrs, [ 'className' ], '' ), 'is-style-outline' );
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'default' ], [] );
		$palette 	= wecodeart_json( [ 'settings', 'color', 'palette', 'theme' ], $palette );
		$palette 	= array_merge( $palette, wecodeart_json( [ 'settings', 'color', 'palette', 'custom' ], [] ) );

		// Inline Style
		$declarations = [];

		// Autodetect color lightness if no text color but has background.
		if( get_prop( $this->attrs, 'textColor' ) === null && get_prop( $this->attrs, 'backgroundColor' ) ) {
			$selected	= wp_list_filter( $palette, [ 'slug' => get_prop( $this->attrs, 'backgroundColor' ) ] );

			$rgba 		= wecodeart( 'styles' )::color_to_rgba( get_prop( current( $selected ), 'color' ), false, true );
			$luminance	= wecodeart( 'styles' )::rgb_luminance( $rgba );
			$is_dark 	= $luminance < get_lightness_limit();

			$declarations['color'] = $is_dark ? 'var(--wp--preset--color--white)' : 'var(--wp--preset--color--black)';
		}

		// Autodetect color lightness if only text color for outline.
		if( get_prop( $this->attrs, 'textColor' ) && $is_outline ) {
			$selected	= wp_list_filter( $palette, [ 'slug' => get_prop( $this->attrs, 'textColor' ) ] );

			$rgba 		= wecodeart( 'styles' )::color_to_rgba( get_prop( current( $selected ), 'color' ), false, true );
			$luminance	= wecodeart( 'styles' )::rgb_luminance( $rgba );
			$is_dark 	= $luminance < get_lightness_limit();

			$declarations['--wp--color--hover'] = $is_dark ? 'var(--wp--preset--color--white)' : 'var(--wp--preset--color--black)';
		}

		// Autodetect color lightness for custom color.
		if( $css_style = get_prop( $this->attrs, 'style' ) ) {
			// Color
			if( $color = get_prop( $css_style, 'color' ) ) {
				// Text
				if( $text = get_prop( $color, 'text' ) ) {
 					$value	= wecodeart( 'styles' )::color_to_rgba( $text, false, true );

					if( $is_outline ) {
						$luminance	= wecodeart( 'styles' )::rgb_luminance( $value );
						$is_dark 	= $luminance < get_lightness_limit();

						$declarations['--wp--color--hover'] = $is_dark ? 'var(--wp--preset--color--white)' : 'var(--wp--preset--color--black)';
					}

					$declarations['--wp--color--rgb'] 	= join( ', ', [ $value['r'], $value['g'], $value['b'] ] );
					$declarations['color'] 				= $text;
				}
				// Background
				if( $background = get_prop( $color, 'background' ) ) {
 					$value	= wecodeart( 'styles' )::color_to_rgba( $background, false, true );

					$declarations['color']	= $is_dark ? 'var(--wp--preset--color--white)' : 'var(--wp--preset--color--black)';
				}
			}
		}

		if( ! empty( $declarations ) ) {
			$this->add_declarations( $declarations );
		}
	}
}