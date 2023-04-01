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
 * @version		6.0.0
 */

namespace WeCodeArt\Gutenberg\Modules\Styles\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Support\Styles\Property\Focal;
use WeCodeArt\Support\Styles\Property\Background;
use WeCodeArt\Gutenberg\Modules\Styles;
use WeCodeArt\Gutenberg\Modules\Styles\Processor;
use function WeCodeArt\Functions\get_prop;
// use function WeCodeArt\Functions\get_placeholder_source;

/**
 * Block CSS Processor
 */
class Cover extends Processor {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	void
	 */
	protected function process_extra(): void {		
		// Block Attributes
		$declarations	= [];

		if ( $value = get_prop( $this->attrs, 'minHeight' ) ) {
			$declarations['min-height'] = $value . get_prop( $this->attrs, 'minHeightUnit', 'px' );
		}

		if ( $value = get_prop( $this->attrs, 'url' ) ) {
			$declarations['background-image'] = ( new Background( $value ) )->get_value();
		}

		if ( $value = get_prop( $this->attrs, 'customOverlayColor' ) ) {
			$declarations['background-color'] = $value;
		}
		
		if ( $value = get_prop( $this->attrs, 'hasParallax' ) ) {
			$declarations['background-attachment'] = 'fixed';
		}

		if( get_prop( $this->attrs, 'useFeaturedImage' ) ) {
			$placeholder	= get_prop( wecodeart_config( 'placeholder', [] ), [ 'src' ], '' );
			$background		= ( new Background( get_post_thumbnail_id() ?: $placeholder ) )->get_value();

			// $declarations['background-image'] = $background;

			// Temporary as safecss_filter_attr does not allow data urls.
			$handle = 'wp-style-engine-' . Styles::CONTEXT;
			$style 	= $this->get_selector() . '{background-image:' . $background . '}';
			add_action( 'wp_enqueue_scripts', static fn() => wp_add_inline_style( $handle, $style ) );
		}
		
		$this->add_declarations( $declarations );

		// Gradient Selector
		if ( $value = get_prop( $this->attrs, 'customGradient' ) ) {
			$this->add_declarations( [
				'background-image' => $value
			], $this->get_selector( '>.has-background-gradient' ) );
		}

		// Focal Selector
		if ( $value = get_prop( $this->attrs, 'focalPoint' ) ) {
			$this->add_declarations( [
				'object-position' => ( new Focal( $value ) )->get_value()
			], join( ', ', [
				$this->get_selector( '>.wp-block-cover__image-background' ),
				$this->get_selector( '>.wp-block-cover__video-background' )
			] ) );
		}
	}
}