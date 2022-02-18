<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		4.0.5
 * @version		5.4.9
 */

namespace WeCodeArt\Gutenberg\Modules;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Config\Traits\Asset;
use WeCodeArt\Conditional\Traits\No_Conditionals;

/**
 * Handles Gutenberg Theme Custom Classes Functionality.
 */
class Classes implements Integration {

	use Singleton;
	use No_Conditionals;
	use Asset;

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
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', 	[ $this, 'grid'		] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', 	[ $this, 'helpers'	] );
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
	 * Add new block editor settings for custom classes.
	 *
	 * @param array  $settings 	The editor settings.
	 * @param object $post 		The post being edited.
	 *
	 * @return array Returns updated editors classes suggestions.
	 */
	public function set_suggestions( $settings, $post ) {
		if ( ! isset( $settings[ 'customClasses' ] ) ) {
			$classes = apply_filters( 'wecodeart/filter/gutenberg/settings/classes', [], $post );
			$settings['customClasses'] = array_map( 'sanitize_html_class', array_unique( $classes ) );
		}

		return $settings;
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function grid( $args ) {
		$breakpoints	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$breakpoints	= array_keys( $breakpoints );

		foreach( range( 1, 12 ) as $number ) {
			$args[] = 'col-' . $number;
			$args[] = 'span-' . $number;
			$args[] = 'start-' . $number;
			$args[] = 'row-cols-' . $number;
			$args[] = 'offset-' . $number;
		}

		foreach( $breakpoints as $breakpoint ) {
			$args[] = 'col-' . $breakpoint;
			$args[] = 'span-' . $breakpoint;
			$args[] = 'col-' . $breakpoint . '-auto';
			$args[] = 'row-cols-' . $breakpoint . '-auto';
			
			foreach( range( 1, 12 ) as $number ) {
				$args[] = 'col-' . $breakpoint . '-' . $number;
				$args[] = 'span-' . $breakpoint . '-' . $number;
				$args[] = 'start-' . $breakpoint . '-' . $number;
				$args[] = 'row-cols-'. $breakpoint . '-' . $number;
				$args[] = 'offset-' . $breakpoint . '-' . $number;
			}
		}

		return wp_parse_args( [
			'row',
			'grid',
			'col-auto',
		], $args );
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function helpers( $args ) {
		$breakpoints	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$breakpoints	= array_keys( $breakpoints );

		// Sticky top - currently it must match the ones from CSS
		// In the future, helpers will be moved to dynamic CSS
		foreach( $breakpoints as $breakpoint ) {
			$args[] = 'sticky-' . $breakpoint . '-top';
		}

		// Typography extra sizes
		foreach( range( 1, 6 ) as $nr ) {
			$args[] = 'display-' . $nr;
			$args[] = 'h' . $nr;
		}

		return wp_parse_args( [
			'vr',
			'hstack',
			'vstack',
			'lead',
			'small',
			'mark',
			'disabled',
			'clearfix',
			'stretched-link',
			'text-truncate',
			'visually-hidden',
			'visually-hidden-focusable',
			'fixed-top',
			'fixed-bottom',
			'list-unstyled',
			'list-inline',
			'list-inline-item',
			'ratio',
			'ratio-1x1',
			'ratio-4x3',
			'ratio-16x9',
			'ratio-21x9',
		], $args );
	}
}
