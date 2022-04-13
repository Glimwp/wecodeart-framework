<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg CSS Frontend
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.2.8
 * @version		5.5.5
 */

namespace WeCodeArt\Gutenberg\Modules\Styles\Blocks;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Post\Image;
use WeCodeArt\Gutenberg\Modules\Styles\Blocks as Base;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 */
class Featured extends Base {
	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	null
	 */
	protected function process_extra() {
		$output 			= [];
		$output['element'] 	= $this->element;
		
		if( $width = get_prop( $this->attrs, 'width' ) ) {
			$this->output[] = wp_parse_args( [
				'element' 	=> $this->element . ' > *',
				'property' 	=> 'width',
				'value'	  	=> $width
			], $output );
		}
		
		if( $height = get_prop( $this->attrs, 'height' ) ) {
			if( Image::use_ratio() ) {
				// We use aspect ratio istead of specific height
				$custom_sizes 	= [];
				$requested_size	= Image::get_image_sizes( apply_filters( 'post_thumbnail_size', 'post-thumbnail', get_the_ID() ) );

				if( $width = get_prop( $this->attrs, 'width' ) ) {
					if( strpos( $width, 'px' ) ) {
						$custom_sizes['width'] = preg_replace( "/[^0-9.]/", "",  $width );
					}
					if( strpos( $width, 'rem' ) || strpos( $width, 'em' ) ) {
						$custom_sizes['width'] = preg_replace( "/[^0-9.]/", "", $width ) * 16;
					}
				}

				if( strpos( $height, 'px' ) ) {
					$custom_sizes['height'] = preg_replace( "/[^0-9.]/", "", $height );
				}

				if( strpos( $height, 'rem' ) || strpos( $height, 'em' ) ) {
					$custom_sizes['height'] = preg_replace( "/[^0-9.]/", "", $height ) * 16;
				}

				$dummy_sizes	= wp_parse_args( $custom_sizes, $requested_size );

				$this->output[] = wp_parse_args( [
					'property' 	=> '--wp--width',
					'value'	  	=> absint( $dummy_sizes['width'] ),
				], $output );

				$this->output[] = wp_parse_args( [
					'property' 	=> '--wp--height',
					'value'	  	=> absint( $dummy_sizes['height'] ),
				], $output );
			} else {
				$this->output[] = wp_parse_args( [
					'element' 	=> $this->element . ' > *',
					'property' 	=> 'height',
					'value'	  	=> $height
				], $output );	 
			}

			if( $value = get_prop( $this->attrs, 'scale' ) ) {
				$this->output[] = wp_parse_args( [
					'element' 	=> $this->element . ' img',
					'property' 	=> 'object-fit',
					'value'	  	=> $value
				], $output );
			}
		}
	}
}