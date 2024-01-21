<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		4.0.5
 * @version		6.3.3
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Config\Traits\Asset;

use function WeCodeArt\Functions\get_prop;

/**
 * Handles Gutenberg Theme Custom Classes Functionality.
 */
class Classes implements Integration {

	use Singleton;
	use Asset;

	/**
	 * The blocks classes
	 *
	 * @access 	public
	 * @var 	array
	 */
	public $classes	= [];

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( 'with_block_classes', Classes\Condition::class );

		return [ 'with_block_classes' ];
	}

	/**
	 * Register Hooks - into styles processor action if enabled
	 *
	 * @since 	5.0.0
	 *
	 * @return 	void
	 */
	public function register_hooks() {
		// Admin
		add_action( 'enqueue_block_editor_assets', 					[ $this, 'block_editor_assets'	] );
		add_filter( 'wecodeart/filter/gutenberg/settings', 			[ $this, 'set_suggestions' 	], 10, 2 );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', 	[ $this, 'helpers'		] );
        add_filter( 'wecodeart/filter/gutenberg/settings/classes', 	[ $this, 'suggestions' 	] );
		// Front
		add_filter( 'pre_render_block', 							[ $this, 'pre_render_block' ], 20, 2 );
		add_action( 'wp_enqueue_scripts',							[ $this, 'enqueue_styles'	], 20, 1 );
	}

	/**
	 * Editor only.
	 *
	 * @return  void
	 */
	public function block_editor_assets() {
		wp_enqueue_script( $this->make_handle(), $this->get_asset( 'js', 'gutenberg/ext-classes' ), [
			'wecodeart-gutenberg'
		], wecodeart( 'version' ) );
	}

	/**
	 * Get block classes.
	 *
	 * @param	string 	$content
	 * @param	array 	$block
	 *
	 * @return  void
	 */
	public function pre_render_block( $content, $block ): void {
		$classes = array_filter( explode( ' ', get_prop( $block, [ 'attrs', 'className' ], '' ) ) );
		if( $classes ) {
			$this->classes = array_merge( $this->classes, $classes );
		}
	}

	/**
	 * Output styles.
	 *
	 * @return 	string
	 */
	public function enqueue_styles() {
		global $_wp_current_template_content;

		// Collect template classes.
		$blocks		= parse_blocks( $_wp_current_template_content );
		$classes	= self::collect_classes( _flatten_blocks( $blocks ) );

		$this->classes = array_merge( $this->classes, $classes );

		// Process utilities.
		if( ! empty( $this->classes ) ) {
			wecodeart( 'styles' )->Utilities->load( $this->classes );
		}
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$classes
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function suggestions( $classes ) {
		return array_merge( array_keys( wecodeart( 'styles' )->Utilities->all() ), $classes );
	}

	/**
	 * Get classNames.
	 *
	 * @param	array	$blocks  List with all blocks
	 *
	 * @return 	array
	 */
	public static function collect_classes( array $blocks = [] ): array {
		$return = [];

		foreach( $blocks as $block ) {
			// Dont bother with empty blocks from parse_blocks
			if( ! get_prop( $block, [ 'blockName' ] ) ) continue;

			$classes	= get_prop( $block, [ 'attrs', 'className' ], '' );
			$classes 	= array_filter( explode( ' ', $classes ) );

			if( ! empty( $classes ) ) {
				$return = array_merge( $return, $classes );
			}
		}

		return array_unique( $return );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param array  $settings 	The editor settings.
	 * @param object $post 		The post being edited.
	 *
	 * @return array Returns updated editors classes suggestions.
	 */
	public function set_suggestions( array $settings, $post ): array {
		if ( ! isset( $settings[ 'customClasses' ] ) ) {
			$classes = apply_filters( 'wecodeart/filter/gutenberg/settings/classes', [], $post );
			$classes = array_map( 'sanitize_html_class', array_values( array_unique( $classes ) ) ); // Unique sanitized values.
			$settings['customClasses'] = $classes;
		}

		return $settings;
	}

	/**
	 * Add helper classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function helpers( array $args ): array {
		$breakpoints	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$breakpoints	= array_keys( $breakpoints );
		$displays		= wecodeart_json( [ 'settings', 'custom', 'display' ], [] );

		foreach( range( 1, 12 ) as $number ) {
			$args[] = 'col-' . $number;
			$args[] = 'span-' . $number;
			$args[] = 'start-' . $number;
			$args[] = 'offset-' . $number;
		}

		foreach( $breakpoints as $breakpoint ) {
			$args[] = 'col-' . $breakpoint;
			$args[] = 'span-' . $breakpoint;
			$args[] = 'col-' . $breakpoint . '-auto';
			$args[] = 'sticky-' . $breakpoint . '-top';
			
			foreach( range( 1, 12 ) as $number ) {
				$args[] = 'col-' . $breakpoint . '-' . $number;
				$args[] = 'span-' . $breakpoint . '-' . $number;
				$args[] = 'start-' . $breakpoint . '-' . $number;
				$args[] = 'offset-' . $breakpoint . '-' . $number;
			}
		}

		// Typography extra sizes - to do later.
		// foreach( range( 1, 6 ) as $nr ) {
		// 	$args[] = 'h' . $nr;
		// }
		
		foreach( array_keys( $displays ) as $d ) {
			$args[] = 'display-' . $d;
		}

		return wp_parse_args( [
			'vr',
			'hstack',
			'vstack',
			'small',
			'mark',
			'initialism',
			'disabled',
			'clearfix',
			'text-truncate',
			'list-unstyled',
			'has-underline',
			'visually-hidden',
			'visually-hidden-focusable',
			'screen-reader-text',
			'sticky-top',
			'fixed-top',
			'fixed-bottom',
			'grid',
			'col-auto',
		], $args );
	}
}
