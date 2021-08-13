<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Classes\Suggestions
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		4.0.5
 * @version		5.0.0
 */

namespace WeCodeArt\Gutenberg\Modules\Classes;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;

/**
 * Handles Gutenberg Theme Custom Classes Functionality.
 */
class Suggestions {

	use Singleton;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function init() {
		// Editor Class Settings.
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'font_classes' 			] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'flex_classes' 			] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'display_classes'		] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'spacing_classes' 		] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'grid_classes' 			] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'offset_order_classes' 	] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'position_classes' 		] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'color_classes' 			] );
		add_filter( 'wecodeart/filter/gutenberg/settings/custom_classes', [ $this, 'extra_classes' 			] );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function font_classes( $args ) {
		foreach( [ 'italic', 'normal' ] as $type ) {
			$args[] = 'fst-'. $type;
		}

		foreach( [ 'normal', 'lighter', 'light', 'bold', 'bolder' ] as $weight ) {
			$args[] = 'fw-'. $weight;
		}
		
		foreach( [ 'start', 'center', 'end', 'justify', 'truncate' ] as $type ) {
			$args[] = 'text-' . $type;
		}

		foreach( [ 'sm', 'md', 'lg', 'xl', 'xxl' ] as $break ) {
			foreach( [ 'start', 'center', 'end', 'justify' ] as $align ) {
				$args[] = 'text-' . $break . '-' . $align;
			}
		}

		foreach( [ 'lowercase', 'uppercase', 'capitalize', 'wrap', 'nowrap', 'reset', 'break' ] as $type ) {
			$args[] = 'text-' . $type;
		}
		
		foreach( [ 'decoration-none', 'decoration-underline', 'decoration-line-through' ] as $type ) {
			$args[] = 'text-' . $type;
		}

		foreach( [ 'muted', 'primary', 'secondary', 'dark', 'light', 'warning', 'danger', 'success', 'info' ] as $color ) {
			$args[] = 'text-' . $color;
			$args[] = 'link-' . $color;
		}

		foreach( range( 1, 5 ) as $nr ) {
			$args[] = 'display-'. $nr;
			$args[] = 'fs-'. $nr;
		}
		
		foreach( range( 1, 6 ) as $nr ) {
			$args[] = 'h'. $nr;
		}

		return wp_parse_args( [
			'lead',
			'font-monospace',
			'stretched-link',
			'align-text-top',
			'align-text-bottom',
			'lh-1',
			'lh-sm',
			'lh-base',
			'lh-lg',
		], $args );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function display_classes( $args ) {
		foreach( [ 'sm', 'md', 'lg', 'xl', 'xxl' ] as $break ) {
			foreach( [ 'none', 'block', 'inline-block', 'flex', 'inline-flex', 'table', 'table-cell', 'table-row' ] as $type ) {
				$args[] = 'd-' . $break . '-' . $type;
			}
		}

		return wp_parse_args( [
			'd-none',
			'd-block',
			'd-inline-block',
			'd-flex',
			'd-inline-flex',
			'd-table',
			'd-table-row',
			'd-table-cell',
		], $args );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function spacing_classes( $args ) {
		// Type
		foreach( [ 'm', 'p' ] as $space ) {
			// Direction
			foreach( [ 'x', 'y', 't', 'b', 'e', 's' ] as $dir ) {
				// Sizes
				foreach( [ '0', '1', '2', '3', '4', '5', 'auto' ] as $size ) {
					if( $size !== 'auto' ) {
						$args[] = implode( '-', [ $dir, $space ] );
					}
					if( in_array( $dir, [ 't', 'b', 'y' ] ) && $size === 'auto' ) continue;
					$args[] = implode( '-', [ $space . $dir, $size ] );
				}
				// Sizes for Breakpoints
				foreach( [ 'sm', 'md', 'lg', 'xl', 'xxl' ] as $break ) {
					foreach( [ '0', '1', '2', '3', '4', '5', 'auto' ] as $size ) {
						if( in_array( $dir, [ 't', 'b', 'y' ] ) && $size === 'auto' ) continue;
						$args[] = implode( '-', [ $space . $dir, $break, $size ] );
					}
				}
			}
		}

		return $args;
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function grid_classes( $args ) {
		$args[] = 'col-auto';

		foreach( range( 1, 12 ) as $number ) {
			$args[] = 'col-' . $number;
			$args[] = 'row-cols-' . $number;
		}

		foreach( [ 'sm', 'md', 'lg', 'xl', 'xxl' ] as $breakpoint ) {
			$args[] = 'col-' . $breakpoint;
			$args[] = 'col-' . $breakpoint . '-auto';
			$args[] = 'row-cols-' . $breakpoint . '-auto';

			foreach( range( 1, 12 ) as $number ) {
				$args[] = 'col-'. $breakpoint . '-' . $number;
				$args[] = 'row-cols-'. $breakpoint . '-' . $number;
			}
		}

		return $args;
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function offset_order_classes( $args ) {
		foreach( range( 1, 12 ) as $column ) {
			$args[] = 'offset-' . $column;
			$args[] = 'order-' . $column;
		}
		
		foreach( [ 'sm', 'md', 'lg', 'xl', 'xxl' ] as $break ) {
			foreach( range( 1, 12 ) as $column ) {
				$args[] = 'order-' . $break . '-first';
				$args[] = 'order-' . $break . '-last';
				$args[] = 'order-' . $break . '-' . $column;
				$args[] = 'offset-' . $break . '-' . $column;
			}
		}

		return wp_parse_args( [
			'order-first',
			'order-last'
		], $args );
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function flex_classes( $args ) {
		foreach( [ 'sm', 'md', 'lg', 'xl', 'xxl' ] as $break ) {

			foreach( [ 'row', 'row-reverse', 'column', 'column-reverse' ] as $dir ) {
				$args[] = 'flex-' . $break . '-' . $dir ;
			}

			foreach( [ 'nowrap', 'wrap', 'wrap-reverse' ] as $wrap ) {
				$args[] = 'flex-' . $break . '-' . $wrap ;
			}

			foreach( [ 'grow', 'shrink' ] as $grow ) {
				$args[] = 'flex-' . $break . '-' . $grow . '-0';
				$args[] = 'flex-' . $break . '-' . $grow . '-1';
			}

			foreach( [ 'start', 'end', 'center', 'between', 'around' ] as $justify ) {
				$args[] = 'justify-content-' . $break . '-' . $justify;
			}

			foreach( [ 'items', 'self' ] as $align ) {
				foreach( [ 'start', 'end', 'center', 'baseline', 'stretch' ] as $type ) {
					$args[] = 'align-' . $align . '-' . $break . '-' . $type ;
				}
			}
			
			foreach( [ 'start', 'end', 'center', 'around', 'stretch' ] as $type ) {
				$args[] = 'align-content-' . $break . '-' . $type ;
			}
		}

		return wp_parse_args( [
			// Direction
			'flex-row',
			'flex-row-reverse',
			'flex-column',
			'flex-column-reverse',
			// Size
			'flex-fill',
			'flex-grow-0',
			'flex-grow-1',
			'flex-shrink-0',
			'flex-shrink-1',
			// Wrap
			'flex-nowrap',
			'flex-wrap',
			'flex-wrap-reverse',
			// Justify
			'justify-content-start',
			'justify-content-end',
			'justify-content-center',
			'justify-content-between',
			'justify-content-around',
			// Align - Items
			'align-items-start',
			'align-items-end',
			'align-items-center',
			'align-items-baseline',
			'align-items-stretch',
			// Align - Self
			'align-self-start',
			'align-self-end',
			'align-self-center',
			'align-self-baseline',
			'align-self-stretch',
			// Align - Content
			'align-content-start',
			'align-content-end',
			'align-content-center',
			'align-content-around',
			'align-content-stretch',
		], $args );
	}
	
	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function position_classes( $args ) {
		foreach( [ 'static', 'relative', 'absolute', 'fixed', 'sticky' ] as $pos ) {
			$args[] = 'position-' . $pos;
		}

		foreach( [ 'start', 'end', 'top', 'bottom' ] as $value ) {
			foreach( [ 0, 50, 100 ] as $pos ) {
				$args[] = $value . '-' . $pos;
			}
		}

		return wp_parse_args( [
			'translate-middle',
			'translate-middle-x',
			'translate-middle-y',
		], $args );
	}
	
	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function color_classes( $args ) {
		foreach( [ 'primary', 'secondary', 'dark', 'light', 'warning', 'danger', 'success', 'info' ] as $color ) {
			$args[] = 'bg-' . $color;
		}

		return $args;
	}

	/**
	 * Add new block editor settings for custom classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function extra_classes( $args ) {
		return wp_parse_args( [
			'user-select-all',
			'user-select-auto',
			'user-select-none',
			'overflow-auto',
			'overflow-hidden',
			'overflow-visible',
			'overflow-scroll',
			'align-baseline',
			'align-top',
			'align-middle',
			'align-bottom',
			'visible',
			'invisible',
			'visually-hidden',
			'pe-none',
			'pe-auto',
			'hstack',
			'vstack',
			'vr'
		], $args );
	}
}
