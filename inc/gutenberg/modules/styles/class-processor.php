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
 * @version		6.3.3
 */

namespace WeCodeArt\Gutenberg\Modules\Styles;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Gutenberg\Modules\Styles;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 *
 * This class handles all the Gutenberg Core styles from attributes found under style object or theme customs.
 * Any extends of this class, should use process_extra() method for extending the attributes processor.
 */
class Processor {
	/**
	 * Block Name.
	 *
	 * @var 	string
	 */
	protected 	$name 		= '';
	protected 	$block_id 	= '';

	/**
	 * Block Attrs.
	 *
	 * @var 	array
	 */
	protected 	$attrs = [];

	/**
	 * The class constructor.
	 *
	 * @access 	public
	 * @param 	array 	$args The block args.
	 */
	public function __construct( $args ) {
		$this->name		= get_prop( $args, 'blockName' );
		$this->attrs	= get_prop( $args, 'attrs', [] );
		$this->block_id	= wp_unique_id( 'css-' );
		
		if( method_exists( $this, 'process_extra' ) ) {
			// Process extra attributes
			$this->process_extra();
		}

		// Process Styles
		$this->process_style();
	}

	/**
	 * Get uniqueID.
	 *
	 * @return 	array
	 */
	public function get_id(): string {
		return $this->block_id;
	}

	/**
	 * Get selector.
	 *
	 * @param	string	$prefix		Append to selector
	 * @param	bool	$support	Import block supports selectors
	 *
	 * @return 	string
	 */
	protected function get_selector( string $prefix = '', bool $support = true ): string {
		$excludes = [ 'core/heading', 'core/paragraph' ]; // Exclude this.
		$selector = '';
		
		if( $support === true && ! in_array( $this->name, $excludes ) ) {
			$block_type	= \WP_Block_Type_Registry::get_instance()->get_registered( $this->name );
			$selector 	= $block_type ? get_prop( $block_type->supports, [ '__experimentalSelector' ], '' ) : '';

			if( $selector && strpos( $selector, ',' ) ) {
				$selector 	= join( ',', array_map( function( $item ) use ( $prefix ) {
					return join( '', array_filter( [ $item, '.', $this->get_id(), '.', $this->get_id(), $prefix ] ) );
				}, explode( ',', $selector ) ) );
			} else {
				$selector 	= join( '', array_filter( [ '.', $this->get_id(), '.', $this->get_id(), $selector, $prefix ] ) );
			}
		} else {
			$selector 	= join( '', array_filter( [ '.', $this->get_id(), '.', $this->get_id(), $selector, $prefix ] ) );
		}

		return apply_filters( 'wecodeart/filter/gutenberg/styles/selector', $selector, $this->name );
	}
	
	/**
	 * Add declarations to WP_Style_Engine.
	 *
	 * @param 	array 	$declarations
	 * @param 	string 	$selector
	 *
	 * @return 	void
	 */
	protected function add_declarations( array $declarations, string $selector = '' ): void {
		\WP_Style_Engine::store_css_rule( Styles::CONTEXT, $selector ?: $this->get_selector(), $declarations );
	}

	/**
	 * Load styles.
	 *
	 * @return 	void
	 */
	private function process_style(): void {
		// Process style attribute.
		if( ! empty( $style_attr = get_prop( $this->attrs, [ 'style' ], [] ) ) ) {
			// Process block attributes.
			wp_style_engine_get_styles( $style_attr, [
				'selector' 	=> $this->get_selector(),
				'context'	=> Styles::CONTEXT
			] );
		}

		// Process custom style attribute.
		if ( $style_custom = get_prop( $this->attrs, 'customStyle' ) ) {
			// Remove tags, just in case.
			$custom_style 	= wp_strip_all_tags( $style_custom );
			// Update selector with class id.
			$custom_style 	= str_replace( 'selector', $this->get_selector(), $custom_style );
			// Convert the string to array (no media queries atm).
			$custom_style 	= wecodeart( 'styles' )::string_to_array( $custom_style );

			foreach( $custom_style as $selector => $rules ) {
				$this->add_declarations( $rules, $selector );
			}
		}
	}

	/**
	 * Additional selectors to remove styles from.
	 *
	 * @return 	array
	 */
	public function remove_style(): array {
		return [];
	}
}