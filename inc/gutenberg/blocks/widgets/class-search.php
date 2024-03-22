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
 * @version		6.3.3
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Search block.
 */
class Search extends Dynamic {

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
	protected $block_name = 'search';

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args( $current ): array {
		$supports 	= get_prop( $current, [ 'supports' ], [] );

		return [
			'render_callback' 		=> [ $this, 'render' ],
			'view_script_handles'	=> [],
		];
	}

	/**
	 * Dynamically renders the `core/search` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '' ): string {
		$instance_id = wp_unique_id( 'wp-search-' );

		$attributes = wp_parse_args( $attributes, [
			'label'      => __( 'Search', 'wecodeart' ),
			'buttonText' => __( 'Search', 'wecodeart' ),
		] );

		// Load a fake input.
		wecodeart_input( 'hidden', [], false );
		
		if( get_prop( $attributes, 'buttonPosition', 'button-outside' ) === 'button-only' ) {
			// Modal
			$modal = self::get_modal( $attributes, $instance_id );
			add_action( 'wp_footer', static fn() => printf( $modal ) );

			// Button
			$content = self::get_button( $attributes, $instance_id );
		} else {
			$content = wecodeart( 'markup' )::wrap( 'wp-block-search__form', [
				[
					'tag' 	=> 'form',
					'attrs' => [
						'class'		=> self::get_classname( $attributes, 'form' ),
						'role'		=> 'search',
						'method' 	=> 'get',
						'action' 	=> home_url( '/' ),
						'novalidate'=> 'novalidate',
					]
				],
			], function( $attributes, $instance_id ) {
				$wrapper   = [ 'wp-block-search__fields' ];
	
				if( get_prop( $attributes, 'buttonPosition' ) === 'button-inside' ) {
					$wrapper[] = 'input-group';
					wecodeart_input( 'group', [], false ); // Fake input.
				}
	
				// Add Label
				if ( get_prop( $attributes, 'showLabel' ) !== false && ! empty( $label = get_prop( $attributes, 'label' ) ) ) {
				?>
				<label class="wp-block-search__label form-label" for="wp-block-search-<?php echo esc_attr( $instance_id ); ?>"><?php echo esc_html( $label ); ?></label>
				<?php
				}
				?>
				<div class="<?php echo esc_attr( implode( ' ', array_filter( $wrapper ) ) ); ?>">
				<?php
	
				// Add search input
				echo self::get_input( $attributes, $instance_id );
	
				// Maybe add submit button
				echo self::get_button( $attributes, $instance_id );
	
				?>
				</div>
				<?php
			}, [ $attributes, $instance_id ], false );
		}

		return wecodeart( 'markup' )::wrap( 'wp-block-search', [
			[
				'tag' 	=> 'div',
				'attrs' => [
					'class' => self::get_classname( $attributes )
				]
			],
		], $content, [], false );
	}

	/**
	 * Render Fields
	 *
	 * @param 	array 	$attributes 	The block attributes.
	 */
	private static function get_input( $attributes, $instance_id ) {
		$markup = '';

		// Add search input
		$markup .= wecodeart_input( 'search', [
			'attrs' => [
				'id' 			=> $instance_id . '-field',
				'name'			=> 's',
				'class' 		=> self::get_classname( $attributes, 'field' ),
				'value'			=> get_search_query(),
				'placeholder' 	=> get_prop( $attributes, 'placeholder' ),
				'required' 		=> true,
			]
		], false );

		// Add hidden fields
		$hidden = get_prop( $attributes, [ 'query' ], [] );

		if ( count( $hidden ) === 0 ) {
			return $markup;
		}

		foreach ( $hidden as $param => $value ) {
			$markup .= wecodeart_input( 'hidden', [
				'attrs' => [
					'id'	=> false,
					'name'	=> $param,
					'value'	=> $value,
				]
			], false );
		}

		return $markup;
	}

	/**
	 * Render Button
	 *
	 * @param 	array 	$attributes 	The block attributes.
	 */
	private static function get_button( $attributes, $instance_id ) {
		$type = get_prop( $attributes, [ 'buttonPosition' ], 'button-outside' );

		if ( $type === 'no-button' ) return '';

		wp_enqueue_style( 'wp-block-button' );

		$is_btn = $type === 'button-only';
		$icon  	= get_prop( $attributes, 'buttonUseIcon' );
		$label 	= $icon ? wecodeart( 'markup' )->SVG::compile( 'search' ) : get_prop( $attributes, 'buttonText' );
		$aria	= $is_btn ? __( 'Open search modal', 'wecodeart' ) : wp_strip_all_tags( get_prop( $attributes, 'buttonText' ) );

		return wecodeart_input( 'button', [
			'label' => $label,
			'attrs' => [
				'type'			=> $is_btn ? 'button' : 'submit',
				'class' 		=> self::get_classname( $attributes, 'button' ),
				'aria-label'	=> $aria,
				'data-bs-toggle'=> $is_btn ? 'modal' : null,
				'data-bs-target'=> $is_btn ? '#' . $instance_id . '-modal' : null,
				'aria-controls'	=> $is_btn ? '#' . $instance_id . '-modal' : null,
			]
		], false ); 
	}

	/**
	 * Render Modal
	 *
	 * @param 	array 	$attributes 	The block attributes.
	 */
	private static function get_modal( $attributes, $instance_id ) {
		$attributes = wp_parse_args( [
			'showLabel'			=> false,
			'buttonPosition' 	=> 'button-outside'
		], $attributes );

		return wecodeart_template( 'general/modal', [
			'id'		=> (string) $instance_id . '-modal',
			'classes'	=> [ 'modal--search', 'modal--fullscreen:sm', 'fade' ],
			'title'		=> get_prop( $attributes, 'label' ),
			'content' 	=> render_block( [
				'blockName' => 'core/search',
				'attrs'		=> $attributes
			] ),
		], false );
	}

	/**
	 * Builds the correct top level classnames for the 'core/search' block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The classnames used in the block.
	 */
	private static function get_classname( $attributes, $wrapper = 'wrapper' ) {
		$classnames = [];

		if( $wrapper === 'wrapper' ) {
			$classnames[] = 'wp-block-search';
			$position = get_prop( $attributes, 'buttonPosition', 'button-outside' );
			$classnames[] = 'wp-block-search--' . $position;
	
			if ( get_prop( $attributes, 'buttonUseIcon', false ) ) {
				if ( 'no-button' !== $position ) {
					$classnames[] = 'wp-block-search--with-icon';
				}
			}
		}
		
		if( $wrapper === 'form' ) {
			$classnames = [ 'wp-block-search__form', 'needs-validation' ];
			// Width utility.
			if( get_prop( $attributes, 'widthUnit', 'px' ) === '%' ) {
				if( in_array( get_prop( $attributes, 'width' ), [ 25, 50, 75, 100 ] ) ) {
					$utility = 'w-' . get_prop( $attributes, 'width' );
					
					wecodeart( 'styles' )->Utilities->load( $utility );
					
					$classnames[] = $utility;
				}
			}
		}

		if( $wrapper === 'field' ) {
			$classnames = [ 'wp-block-search__input', 'form-control' ];
		}

		if( $wrapper === 'button' ) {
			$classnames = [ 'wp-block-search__button', wp_theme_get_element_class_name( 'button' ) ];
			
			if( $value = get_prop( $attributes, [ 'gradient' ] ) ) {
				$classnames[] = 'has-' . $value . '-gradient-background';
			}

			if( $value = get_prop( $attributes, [ 'backgroundColor' ] ) ) {
				$classnames[] = 'has-' . $value . '-background-color';
			}

			if( $value = get_prop( $attributes, [ 'textColor' ] ) ) {
				$classnames[] = 'has-text-color';
				$classnames[] = 'has-' . $value . '-color';
			}
		}

		if( in_array( $wrapper, [ 'field', 'button' ], true ) ) {
			if( $value = get_prop( $attributes, [ 'borderColor' ] ) ) {
				$classnames[] = 'has-border-color';
				$classnames[] = 'has-' . $value . '-border-color';
			}
		}

		return implode( ' ', $classnames );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return <<<CSS
			.wp-block-search {
				border-width: 0;
			}
			.wp-block-search--button-outside .wp-element-button {
				margin-left: 1rem;
			}
			.wp-block-search__fields {
				display: flex;
			}
		CSS;
	}
}
