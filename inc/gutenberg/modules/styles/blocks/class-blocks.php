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
 * @since		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Gutenberg\Modules\Styles;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Support\Styles\Processor;
use function WeCodeArt\Functions\get_prop;

/**
 * Block CSS Processor
 *
 * This class handles all the Gutenberg Core styles from attributes found under style object or theme customs.
 * Any extends of this class, should use process_extra() method for extending the attributes processor.
 */
class Blocks extends Processor {

	/**
	 * Block Name.
	 *
	 * @var 	string
	 */
	protected 	$name 		= '';
	protected 	$element 	= '';

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

		// Set unique class
		$this->set_element();

		if( empty( $this->element ) ) return;

		// Process CSS
		$this->process_attributes();
		if( method_exists( $this, 'process_extra' ) ) {
			$this->process_extra();
		}
		$this->parse_output();
		$this->parse_custom();
	}

	/**
	 * Setup element unique class.
	 *
	 * @return 	mixed
	 */
	private function set_element() {
		$block_class 	= explode( ' ', get_prop( $this->attrs, 'className' ) );
		$block_class 	= array_filter( $block_class, function( $key ) {
			return strpos( $key, 'css-' ) === 0;
		} );
		
		// If not unique classname, try again
		if( count( $block_class ) === 0 ) {
			return $this;
		};

		$this->element = '.' . sanitize_html_class( end( $block_class ) );
	}

	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @return 	void
	 */
	protected function process_attributes() {
		$this->output = [];

		$output 			= [];
		$output['element'] 	= $this->element;

		// Background
		if( get_prop( $this->attrs, 'backgroundType' ) === 'image' ) {
			if ( $value = get_prop( $this->attrs, 'backgroundUrl', false ) ) {
				$this->output[] = wp_parse_args( [
					'property' 	=> 'background',
					'value'	  	=> $value
				], $output );
			}
	
			// Background Position
			if ( $value = get_prop( $this->attrs, 'focalPoint', false ) ) {
				$this->output[] = wp_parse_args( [
					'property' 	=> 'background-position',
					'value'	  	=> $value
				], $output );
			}
			
			// Background Size
			if ( $value = get_prop( $this->attrs, 'backgroundSize', false ) ) {
				$this->output[] = wp_parse_args( [
					'property' 	=> 'background-size',
					'value'	  	=> $value
				], $output );
			}
	
			// Background Repeat
			if ( $value = get_prop( $this->attrs, 'backgroundRepeat', false ) ) {
				$this->output[] = wp_parse_args( [
					'property' 	=> 'background-repeat',
					'value'	  	=> $value
				], $output );
			}
		}
		
		// Inline Style
		if( $css_style = get_prop( $this->attrs, 'style', false ) ) {
			// Typography
			if ( $typography = get_prop( $css_style, 'typography', false ) ) {
				if ( $value = get_prop( $typography, 'fontSize', false ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'font-size',
						'value'	  	=> $value,
						'units'	  	=> 'px'
					], $output );
				}
				if ( $value = get_prop( $typography, 'lineHeight', false ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'line-height',
						'value'	  	=> $value,
					], $output );
				}
			}
			// Colors
			if ( $color = get_prop( $css_style, 'color', false ) ) {
				// Text
				if ( $value = get_prop( $color, 'text', false ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'color',
						'value'	  	=> $value
					], $output );
				}
				// Background
				if ( $value = get_prop( $color, 'background', false ) ) {
					$this->output[] = wp_parse_args( [
						'property' 	=> 'background-color',
						'value'	  	=> $value
					], $output );
				}
				// Gradient
				if ( $value = get_prop( $color, 'gradient', false ) ) {
					$this->output[] = wp_parse_args( [
						'element'	=> implode( '>', [ $this->element, '.wp-block__gradient-background' ] ),
						'property' 	=> 'background-image',
						'value'	  	=> $value
					], $output );
				}
			}
			// Spacing
			if( $spacing = get_prop( $css_style, 'spacing', false ) ) {
				if ( $padding = get_prop( $spacing, 'padding', [] ) ) {
					if( ! empty( $padding ) ) {
						foreach( $padding as $dir => $val ) {
							$this->output[] = wp_parse_args( [
								'property' 	=> 'padding-' . $dir,
								'value'	  	=> $val
							], $output );
						}
					}
				}
				if ( $margin = get_prop( $spacing, 'margin', [] ) ) {
					if( ! empty( $margin ) ) {
						foreach( $margin as $dir => $val ) {
							$this->output[] = wp_parse_args( [
								'property' 	=> 'margin-' . $dir,
								'value'	  	=> $val
							], $output );
						}
					}
				}
			}
		}
	}
	
	/**
	 * Parses and attach Custom CSS
	 *
	 * @return 	void
	 */
	private function parse_custom() {
		if ( $css_custom = get_prop( $this->attrs, 'customCSS', false ) ) {
			$custom_style 	= wp_strip_all_tags( $css_custom );
			$custom_style 	= wecodeart( 'integrations' )->get( 'styles' )::break_queries( $custom_style );
			$this->styles 	= array_replace_recursive( $this->styles, $custom_style );
		}
	}
}