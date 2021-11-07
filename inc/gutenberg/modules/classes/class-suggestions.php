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
 * @version		5.2.2
 */

namespace WeCodeArt\Gutenberg\Modules\Classes;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;

/**
 * Handles Gutenberg Theme Custom Classes Functionality.
 */
class Suggestions {

	use Singleton;

	const BREAKPOINTS 	= [ 'sm', 'md', 'lg', 'xl', 'xxl' ];
	const COLORS 		= [
		'primary',
		'secondary',
		'warning',
		'danger',
		'success',
		'info',
		'white',
		'light',
		'muted',
		'dark',
		'transparent',
		'gradient'
	];

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	public function init() {
		// Editor Class Settings.
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'background'	] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'typography'	] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'display'		] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'flex' 			] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'spacing' 		] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'grid' 			] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'position' 		] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'card' 			] );
		add_filter( 'wecodeart/filter/gutenberg/settings/classes', [ $this, 'extra'			] );
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function background( $args ) {
		foreach( self::COLORS as $color ) {
			$args[] = 'bg-' . $color;
		}

		return $args;
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function typography( $args ) {
		foreach( [ 'italic', 'normal' ] as $type ) {
			$args[] = 'fst-'. $type;
		}

		foreach( [ 'normal', 'lighter', 'light', 'bold', 'bolder' ] as $weight ) {
			$args[] = 'fw-'. $weight;
		}
		
		foreach( [ 'start', 'center', 'end', 'justify', 'truncate' ] as $type ) {
			$args[] = 'text-' . $type;
		}

		foreach( self::BREAKPOINTS as $break ) {
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

		foreach( self::COLORS as $color ) {
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
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function display( $args ) {
		foreach( self::BREAKPOINTS as $break ) {
			foreach( [
				'none', 'block', 'inline-block', 'flex', 'grid', 'inline-flex', 'table', 'table-cell', 'table-row'
			] as $type ) {
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
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function flex( $args ) {
		foreach( self::BREAKPOINTS as $break ) {
			// Flex directions
			foreach( [ 'row', 'row-reverse', 'column', 'column-reverse' ] as $dir ) {
				$args[] = 'flex-' . $break . '-' . $dir ;
			}

			// Flex wraps
			foreach( [ 'nowrap', 'wrap', 'wrap-reverse' ] as $wrap ) {
				$args[] = 'flex-' . $break . '-' . $wrap ;
			}

			// Flex sizes
			foreach( [ 'grow', 'shrink' ] as $grow ) {
				$args[] = 'flex-' . $break . '-' . $grow . '-0';
				$args[] = 'flex-' . $break . '-' . $grow . '-1';
			}

			// Align items/self
			foreach( [ 'items', 'self' ] as $align ) {
				foreach( [ 'start', 'end', 'center', 'baseline', 'stretch' ] as $type ) {
					$args[] = 'align-' . $align . '-' . $break . '-' . $type ;
				}
			}
			
			// Align content
			foreach( [ 'start', 'end', 'center', 'around', 'stretch' ] as $type ) {
				$args[] = 'align-content-' . $break . '-' . $type ;
			}

			// Justify content
			foreach( [ 'start', 'end', 'center', 'between', 'around' ] as $justify ) {
				$args[] = 'justify-content-' . $break . '-' . $justify;
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
			// Justify
			'justify-content-start',
			'justify-content-end',
			'justify-content-center',
			'justify-content-between',
			'justify-content-around',
		], $args );
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function spacing( $args ) {
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
				foreach( self::BREAKPOINTS as $break ) {
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
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function grid( $args ) {
		foreach( range( 1, 12 ) as $number ) {
			$args[] = 'col-' . $number;
			$args[] = 'row-cols-' . $number;
			$args[] = 'offset-' . $number;
			$args[] = 'order-' . $number;
		}

		foreach( self::BREAKPOINTS as $breakpoint ) {
			$args[] = 'col-' . $breakpoint;
			$args[] = 'col-' . $breakpoint . '-auto';
			$args[] = 'row-cols-' . $breakpoint . '-auto';

			foreach( range( 1, 12 ) as $number ) {
				$args[] = 'col-'. $breakpoint . '-' . $number;
				$args[] = 'row-cols-'. $breakpoint . '-' . $number;
				$args[] = 'order-' . $breakpoint . '-first';
				$args[] = 'order-' . $breakpoint . '-last';
				$args[] = 'order-' . $breakpoint . '-' . $number;
				$args[] = 'offset-' . $breakpoint . '-' . $number;
			}
		}

		return wp_parse_args( [
			'col-auto',
			'order-first',
			'order-last'
		], $args );
	}
	
	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function position( $args ) {
		foreach( [ 'static', 'relative', 'absolute', 'fixed', 'sticky' ] as $pos ) {
			$args[] = 'position-' . $pos;

			foreach( self::BREAKPOINTS as $breakpoint ) { 
				$args[] = implode( '-', [ 'position', $breakpoint, $pos ] );
			}
		}

		foreach( [ 'start', 'end', 'top', 'bottom' ] as $value ) {
			foreach( [ 0, 50, 100 ] as $pos ) {
				$args[] = $value . '-' . $pos;

				foreach( self::BREAKPOINTS as $breakpoint ) { 
					$args[] = implode( '-', [ $value, $breakpoint, $pos ] );
				}
			}
		}

		return wp_parse_args( [
			'translate-middle',
			'translate-middle-x',
			'translate-middle-y',
		], $args );
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function card( $args ) {
		return wp_parse_args( [
			'card',
			'card-title',
			'card-subtitle',
			'card-text',
			'card-link',
			'card-img',
			'card-img-top',
			'card-img-bottom',
			'card-img-overlay',
			'card-header',
			'card-header-tabs',
			'card-header-pills',
			'card-body',
		], $args );
	}

	/**
	 * Add new classes.
	 *
	 * @param 	array  	$args
	 *
	 * @return 	array 	Returns updated editors settings.
	 */
	public function extra( $args ) {
		return wp_parse_args( [
			// Interaction
			'user-select-all',
			'user-select-auto',
			'user-select-none',
			'pe-none',
			'pe-auto',
			// Overflow
			'overflow-auto',
			'overflow-hidden',
			'overflow-visible',
			'overflow-scroll',
			// Sizing
			'w-25',
			'w-50',
			'w-75',
			'w-100',
			'w-auto',
			'h-25',
			'h-50',
			'h-75',
			'h-100',
			'h-auto',
			'min-vw-100',
			'min-vh-100',
			'mw-100',
			'mh-100',
			'vh-100',
			'vw-100',
			// Align
			'align-baseline',
			'align-top',
			'align-middle',
			'align-bottom',
			// Visibility
			'visible',
			'invisible',
			'visually-hidden',
			'visually-hidden-focusable',
			// Opacity
			'opacity-0',
			'opacity-25',
			'opacity-50',
			'opacity-75',
			'opacity-100',
			// Ratio
			'ratio',
			'ratio-1x1',
			'ratio-4x3',
			'ratio-16x9',
			'ratio-21x9',
			// Shadow
			'shadow',
			'shadow-none',
			'shadow-sm',
			'shadow-lg',
			// Img
			'img-fluid',
			'img-thumbnail',
			// Misc
			'stretched-link',
			'disabled',
			'badge',
			'hstack',
			'vstack',
			'vr',
		], $args );
	}
}
