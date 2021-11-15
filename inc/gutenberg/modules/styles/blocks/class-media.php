<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.2.2
 */

namespace WeCodeArt\Gutenberg\Modules\Styles\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Modules\Styles\Blocks as Base;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 */
class Media extends Base {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	null
	 */
	protected function process_extra() {
		$output 			= [];
		$output['element'] 	= implode( ' ', [ $this->element, '.wp-block-media-text__media' ] );

		// Handle width
		if ( $value = get_prop( $this->attrs, 'mediaWidth', false ) ) {
			$this->output[] = wp_parse_args( [
				'property' 	=> 'flex-basis',
				'value'	  	=> $value,
				'units'		=> '%'
			], $output );
		}

		// Handle media image if is used as column filler
		if( get_prop( $this->attrs, 'mediaType' ) === 'image' ) {
			if( get_prop( $this->attrs, 'imageFill', false ) ) {
				if ( $value = get_prop( $this->attrs, 'mediaId', false ) ) {
					if( $media = wp_get_attachment_image_url( $value, get_prop( $this->attrs, 'mediaSizeSlug', 'full' ) ) ) {
						$this->output[] = wp_parse_args( [
							'property' 	=> 'background-image',
							'value'	  	=> $media
						], $output );
					// Fallback to WP.org patterns (however some of them have wp.org page url instead of a media file)
					} elseif ( $value = get_prop( $this->attrs, 'mediaLink', false ) ) {
						$this->output[] = wp_parse_args( [
							'property' 	=> 'background-image',
							'value'	  	=> $value
						], $output );
					}
				} elseif ( $value = get_prop( $this->attrs, 'mediaLink', false ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'background-image',
						'value'	  	=> $value
					], $output );
				}
		
				if ( $value = get_prop( $this->attrs, 'focalPoint', false ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'background-position',
						'value'	  	=> $value
					], $output );
				}
			}
		}
	}
}