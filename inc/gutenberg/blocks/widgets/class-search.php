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
 * @version		6.0.0
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

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
	public function block_type_args(): array {
		return [
			'render_callback' => [ $this, 'render' ]
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
		static $instance_id = 0;

		$attributes = wp_parse_args( $attributes, [
			'label'      => __( 'Search', 'wecodeart' ),
			'buttonText' => __( 'Search', 'wecodeart' ),
		] );

		$input_id 	= 'wp-block-search__input-' . ++$instance_id;
		$form_class = [ 'wp-block-search__form', 'needs-validation' ];
		$query_atts = get_prop( $attributes, [ 'query' ], [] );

		// Width utility.
		if( get_prop( $attributes, 'widthUnit', 'px' ) === '%' ) {
			if( in_array( get_prop( $attributes, 'width' ), [ 25, 50, 75, 100 ] ) ) {
				$utility = 'w-' . get_prop( $attributes, 'width' );
				
				wecodeart( 'styles' )->Utilities->load( $utility );
				
				$form_class[] = $utility;
			}
		}

		$content = wecodeart( 'markup' )::wrap( 'wp-block-search', [
			[
				'tag' 	=> 'div',
				'attrs' => [
					'class' => $this->get_classname( $attributes )
				]
			],
			[
				'tag' 	=> 'form',
				'attrs' => [
					'class'		=> join( ' ', $form_class ),
					'role'		=> 'search',
					'method' 	=> 'get',
					'action' 	=> home_url( '/' ),
					'novalidate'=> 'novalidate',
				]
			],
		], function() use( $attributes, $input_id, $query_atts ) {
			$wrapper   = [ 'wp-block-search__fields' ];

			if( get_prop( $attributes, 'buttonPosition' ) === 'button-inside' ) {
				$wrapper[] = 'input-group';
			}

			// Add Label
			if ( get_prop( $attributes, 'showLabel' ) !== false && ! empty( $label = get_prop( $attributes, 'label' ) ) ) {
			?>
			<label class="form-label" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php
			}
			?>
			<div class="<?php echo esc_attr( implode( ' ', array_filter( $wrapper ) ) ); ?>">
			<?php

			// Add search input
			wecodeart_input( 'search', [
				'attrs' => [
					'id' 			=> $input_id,
					'name'			=> 's',
					'class' 		=> $this->get_classname( $attributes, 'field' ),
					'value'			=> get_search_query(),
					'placeholder' 	=> get_prop( $attributes, 'placeholder' ),
					'required' 		=> true,
				]
			] );

			// Add Query Params
			if ( count( $query_atts ) > 0 ) {
				foreach ( $query_atts as $param => $value ) {
					wecodeart_input( 'hidden', [
						'attrs' => [
							'id'	=> false,
							'name'	=> $param,
							'value'	=> $value,
						]
					] );
				}
			}

			// Maybe add submit button
			if ( get_prop( $attributes, 'buttonPosition', 'button-outside' ) !== 'no-button' ) {
				add_filter( 'should_load_separate_core_block_assets', '__return_true', PHP_INT_MAX );
				wp_enqueue_style( 'wp-block-button' );

				$icon  = get_prop( $attributes, 'buttonUseIcon' );
				$label = $icon ? wecodeart( 'markup' )->SVG::compile( 'search' ) : get_prop( $attributes, 'buttonText' );

				wecodeart_input( 'button', [
					'label' => $label,
					'attrs' => [
						'type'			=> 'submit',
						'class' 		=> $this->get_classname( $attributes, 'button' ),
						'aria-label'	=> wp_strip_all_tags( get_prop( $attributes, 'buttonText' ) ),
					]
				] );
			}
			?>
			</div>
			<?php
		}, [], false );

		return $content;
	}

	/**
	 * Builds the correct top level classnames for the 'core/search' block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The classnames used in the block.
	 */
	public function get_classname( $attributes, $wrapper = 'wrapper' ) {
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

		if( $wrapper === 'button' ) {
			$classnames = [ 'wp-block-search__button', 'wp-element-button' ];
			
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
		
		if( $wrapper === 'field' ) {
			$classnames = [ 'wp-block-search__input', 'form-control' ];
		}
		
		if( $value = get_prop( $attributes, [ 'borderColor' ] ) ) {
			$classnames[] = 'has-border-color';
			$classnames[] = 'has-' . $value . '-border-color';
		}

		return implode( ' ', $classnames );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
		.wp-block-search {
			border-width: 0;
		}
		.wp-block-search--button-outside .wp-element-button {
			margin-left: 1rem;
		}
		.wp-block-search__fields {
			display: flex;
		}
		";
	}
}
