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

use WeCodeArt\Singleton;
use WeCodeArt\Support\Styles\Property\Focal;
use WeCodeArt\Support\Styles\Property\Background;
use WeCodeArt\Gutenberg\Modules\Styles\Processor;
use function WeCodeArt\Functions\get_prop;
use function WeCodeArt\Functions\get_placeholder_source;

/**
 * Block CSS Processor
 */
class Media extends Processor {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	void
	 */
	protected function process_extra(): void {
		$selector 		= $this->get_selector( '> .wp-block-media-text__media' );
		$declarations 	= [];
		
		// Handle width
		if ( $value = get_prop( $this->attrs, 'mediaWidth' ) ) {
			$this->add_declarations( [
				'grid-template-columns' => "{$value}% auto",
			] );
		}

		// Handle media image if is used as column filler
		if( get_prop( $this->attrs, 'mediaType' ) === 'image' ) {
			if( get_prop( $this->attrs, 'imageFill' ) ) {
				if ( $value = get_prop( $this->attrs, 'mediaId' ) ) {
					if( $media = wp_get_attachment_image_url( $value, get_prop( $this->attrs, 'mediaSizeSlug', 'full' ) ) ) {
						$declarations['background-image'] = ( new Background( $value ) )->get_value();
						// Fallback to WP.org patterns (however some of them have wp.org page url instead of a media file)
					} elseif ( $value = get_prop( $this->attrs, 'mediaLink' ) ) {
						$declarations['background-image'] = ( new Background( $value ) )->get_value();
					}
				} elseif ( $value = get_prop( $this->attrs, 'mediaLink' ) ) {
					$declarations['background-image'] = ( new Background( $value ) )->get_value();
				}
		
				if ( $value = get_prop( $this->attrs, 'focalPoint' ) ) {
					$declarations['background-position'] = ( new Focal( $value ) )->get_value();
				}
			}
		}
		
		if( ! get_prop( $this->attrs, 'mediaLink' ) ) {
			$declarations['background-image'] = ( new Background( get_placeholder_source( false ) ) )->get_value();
		}

		if( ! empty( $declarations ) ) {
			$this->add_declarations( $declarations, $selector );
		}
	}
}