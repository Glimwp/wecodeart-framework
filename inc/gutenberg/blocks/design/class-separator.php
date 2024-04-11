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
 * @version		6.4.1
 */

namespace WeCodeArt\Gutenberg\Blocks\Design;

defined( 'ABSPATH' ) || exit;

use str_replace;
use rawurldecode;
use preg_replace;
use str_starts_with;
use file_get_contents;
use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\{ get_prop, encode_svg_data };

/**
 * Gutenberg Separator block.
 */
class Separator extends Dynamic {

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
	protected $block_name = 'separator';
	
	/**
	 * Block SVG styles.
	 *
	 * @var string
	 */
	protected static $styles = [];

	/**
	 * Init.
	 */
	public function init() {
		// Collect SVG files.
		$this->collect_svg_styles();
		
		// We only load them in admin for preview, in frontend we load conditionaly.
		if( ! is_admin() ) {
			return;
		}

		\register_block_style( $this->get_block_type(), [
			'name' 			=> 'dots',
            'label'			=> esc_html__( 'Dots', 'wecodeart' ),
			'inline_style' 	=> self::get_style( 'is-style-dots' )
		] );
		
		\register_block_style( $this->get_block_type(), [
			'name' 			=> 'faded',
            'label'			=> esc_html__( 'Faded', 'wecodeart' ),
			'inline_style' 	=> self::get_style( 'is-style-faded' )
		] );

		// Register collected SVG styles.
		foreach( self::$styles as $data ) {
			$name	= get_prop( $data, [ 'name' ] );
			$label	= ucwords( join( ' ', explode( '-', $name ) ) );

			\register_block_style( $this->get_block_type(), [
				'name' 			=> 'svg-' . $name,
				'label'			=> sprintf( esc_html__( '%s', 'wecodeart' ), $label ),
				'inline_style' 	=> self::get_style( $data )
			] );
		}
	}

	/**
	 * Block args.
	 *
	 * @param	array $current	Existing register args
	 *
	 * @return 	array
	 */
	public function block_type_args( $current ): array {
		$supports 	= get_prop( $current, [ 'supports' ], [] );

		return [
			'render_callback' 	=> [ $this, 'render' ],
			'supports'			=> wp_parse_args( [
				'dimensions'	=> [
					'minHeight'	=> true,
				],
			], $supports )
		];
	}

    /**
	 * Dynamically renders the `core/separator` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '' ): string {
		$handle 	= $this->get_asset_handle();
		$classes 	= array_filter( explode( ' ', get_prop( $attributes, [ 'className' ], '' ) ) );
		$is_svg 	= false;

		static $styles = [];

		// Load specific style if not already loaded.
		foreach( $classes as $class ) {
			// Default styles.
			if( in_array( $class, [ 'is-style-dots', 'is-style-faded' ] ) && ! in_array( $class, $styles ) ) {
				$style 	= self::get_style( $class );
	
				\wp_add_inline_style( $handle, $style );
	
				$styles[] = $class;
			}

			// This is SVG, handled it bellow.
			if( str_starts_with( $class, 'is-style-svg-' ) ) {
				$is_svg = $class;
				
				continue;
			}
		}

		if( $is_svg ) {			
			$style	= current( wp_list_filter( self::$styles, [
				'class' => $is_svg
			] ) );
				
			if( $style ) {
				$dom	= wecodeart( 'dom' )::create( $content );
				$hr		= wecodeart( 'dom' )::get_element( 'hr', $dom );
				$div 	= wecodeart( 'dom' )::change_tag( $hr, 'div' );

				$file	= file_get_contents( get_prop( $style, [ 'file' ] ) );
				$svg    = $dom->importNode( wecodeart( 'dom' )::create( $file )->documentElement, true );
				$svg->setAttribute( 'role', 'presentation' );
				$svg->setAttribute( 'aria-hidden', 'true' );
				$div->appendChild( $svg );

				$content = $dom->saveHtml();
			}
		}

		return str_replace( 'has-alpha-channel-opacity ', '', $content );
	}

	/**
	 * Collects SVG for creating styles
	 *
	 * @return 	void.
	 */
	private function collect_svg_styles(): void {
		$themes     = [];
		$stylesheet = get_stylesheet();
		$template   = get_template();

		if ( $stylesheet !== $template ) {
			$themes[] = wp_get_theme( $stylesheet );
		}

		$themes[] = wp_get_theme( $template );

		foreach ( $themes as $theme ) {
			$dirpath = $theme->get_stylesheet_directory() . '/views/blocks/separator/';
			if ( ! is_dir( $dirpath ) || ! is_readable( $dirpath ) ) {
				continue;
			}
			if ( file_exists( $dirpath ) ) {
				$files = glob( $dirpath . '*.svg' );
				if ( $files ) {
					foreach ( $files as $file ) {
						$pathinfo 	= pathinfo( $file );
						self::$styles[] = [
							'name'	=> get_prop( $pathinfo, [ 'filename' ], '' ),
							'class'	=> 'is-style-svg-' . get_prop( $pathinfo, [ 'filename' ], '' ),
							'file'	=> $file,
						];
					}
				}
			}
		}
	}

	/**
	 * Get Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function get_style( $data = null ): string {
		$inline = '';

		if( is_array( $data ) ) {
			$class	= get_prop( $data, [ 'class' ] );

			// Bail early if no class.
			if( ! $class ) {
				return $inline;
			}

			$svg_string	= \file_get_contents( get_prop( $data, [ 'file' ] ) );
			// Clean SVG and covert to data URL.
			$encodedSVG = encode_svg_data( $svg_string );

			$inline = "
				.wp-block-separator.{$class}::before {
					-webkit-mask-image: url('{$encodedSVG}');
				}
			";
		}

		if( is_string( $data ) ) {
			switch( $data ) :
				case 'is-style-dots' :
					$inline = '
						.wp-block-separator.is-style-dots {
							--wp--separator-gap: 2em;
							--wp--separator-icon: "\00b7 \00b7 \00b7";
							background: none !important;
							height: auto;
							line-height: 1;
							text-align: center;
						}
						.wp-block-separator.is-style-dots::before {
							content: var(--wp--separator-icon);
							color: currentColor;
							font-size: 1.5rem;
							letter-spacing: var(--wp--separator-gap);
							padding-left: var(--wp--separator-gap);
							font-family: serif;
						}
					';
					break;
				case 'is-style-faded' :
					$inline = "
						.wp-block-separator.wp-block-separator.is-style-faded {
							background-color: transparent!important;
							background-image: linear-gradient(to right, rgba(255,255,255,0), currentColor, rgba(255,255,255,0))!important;
							width: initial!important;
						}
					";
					break;
				default :
					break;
			endswitch;
		}

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		$mobile = wecodeart_json( [ 'settings', 'custom', 'mobileBreakpoint' ], 'lg' );
		$breakpoint = wecodeart_json( [ 'settings', 'custom', 'breakpoints', $mobile ], '992px' );

		return <<<CSS
			/* Separator */
			hr {
				height: 1px;
				border: 0;
				opacity: 1;
			}
			.wp-block-separator {
				margin-left: auto;
				margin-right: auto;
			}
			.wp-block-separator.is-style-default,
			.wp-block-separator:not([class*='is-style-']) {
				max-width: 100px;
			}
			.wp-block-separator.wp-block-separator[class*='is-style-svg-'] {
				position: relative;
				background-color: transparent!important;
				height: calc(var(--wp--separator-height) * var(--wp--separator-ratio, .7));
				min-height: initial;
			}
			.wp-block-separator[class*='is-style-svg-'] svg {
				display: block;
				position: absolute;
				fill: currentColor;
				width: 100%;
				height: calc(100% + 1px);
			}
			@media (min-width: {$breakpoint}) {
				.wp-block-separator.wp-block-separator[class*='is-style-svg-'] {
					height: var(--wp--separator-height);
				}
			}
			/* Admin */
			.editor-styles-wrapper .wp-block-separator[class*='is-style-svg-']::before {
				content: '';
				display: block;
				position: absolute;
				background: currentColor;
				width: 100%;
				height: calc(100% + 1px);
				-webkit-mask-repeat: no-repeat;
				-webkit-mask-position: center;
				-webkit-mask-size: 100%;
			}
		CSS;
	}
}
