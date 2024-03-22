<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		5.4.8
 * @version		6.3.7
 */

namespace WeCodeArt\Gutenberg\Styles\Blocks;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Styles;
use WeCodeArt\Gutenberg\Styles\Processor;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 */
class Search extends Processor {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	void
	 */
	protected function process_style(): void {
		// Width
		if( ! in_array( get_prop( $this->attrs, 'width' ), [ 25, 50, 75, 100 ] ) ) {
			$this->add_declarations( [
				'width' => get_prop( $this->attrs, 'width' ) . get_prop( $this->attrs, 'widthUnit' )
			], $this->get_selector( ' .wp-block-search__fields' ) );
		}

		// Apply styles to inner selectors.
		wp_style_engine_get_styles( [
			'typography' 	=> get_prop( $this->attrs, [ 'style', 'typography' ] ),
			'border' 		=> get_prop( $this->attrs, [ 'style', 'border' ] ),
		], [
			'selector' 	=> $this->get_selector( ' :where(.wp-block-search__label,.wp-block-search__input,.wp-block-search__button)' ),
			'context'	=> Styles::CONTEXT
		] );
		
		wp_style_engine_get_styles( [
			'color' 	=> get_prop( $this->attrs, [ 'style', 'color' ] ),
		], [
			'selector' 	=> $this->get_selector( ' .wp-block-search__button' ),
			'context'	=> Styles::CONTEXT
		] );

		// Unset the above so they are not applied to main selector.
		$this->attrs['style']['typography'] = $this->attrs['style']['typography'] ?? null;
		$this->attrs['style']['border'] 	= $this->attrs['style']['border'] ?? null;
		$this->attrs['style']['color'] 		= $this->attrs['style']['color'] ?? null;

		unset( $this->attrs['style']['typography'], $this->attrs['style']['border'], $this->attrs['style']['color'] );

		parent::process_style();
	}
}