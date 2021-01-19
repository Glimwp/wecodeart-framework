<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Styles\Processor
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Support\Styles;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Support\Styles;
use function WeCodeArt\Functions\get_prop;

/**
 * CSS Processor.
 */
abstract class Processor {

	/**
	 * The field's `output` argument.
	 *
	 * @var 	array
	 */
	protected 	$output = [];

	/**
	 * An array of the generated styles.
	 *
	 * @var 	array
	 */
	protected 	$styles = [];
	
	/**
	 * Processor type.
	 *
	 * @var 	string
	 */
	protected 	$processor = '';

	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   array 	$args Args object.
	 */
	public function __construct( $args ) {
		$this->output 	= get_prop( $args, 'output', [] );

		$this->parse_output();
	}

	/**
	 * If we have a sanitize_callback defined, apply it to the value.
	 *
	 * @param 	array        $output The output args.
	 *
	 * @return 	string|array The output sanitized value
	 */
	protected function apply_sanitize_callback( $output ) {
		if ( isset( $output['sanitize_callback'] ) && null !== $output['sanitize_callback'] ) {
			// If the sanitize_callback is invalid, return the value.
			if ( ! is_callable( $output['sanitize_callback'] ) ) {
				return $output['value'];
			}
			
			return call_user_func( $output['sanitize_callback'], $output['value'] );
		}

		return $output['value'];
	}

	/**
	 * If we have a value_pattern defined, apply it to the value.
	 *
	 * @param 	array        $output 	The output args.
	 *
	 * @return 	string|array The output value after pattern
	 */
	protected function apply_pattern( $output ) {
		$value 			= get_prop( $output, 'value' );
		$replacement 	= get_prop( $output, 'pattern', false );
		
		if ( $replacement ) {
			if( is_callable( $replacement ) ) {
				$replacement = call_user_func( $replacement, $value );
			}

			$replacement = ( false === $replacement ) ? '' : $replacement;

			if( $replacement ) {
				if ( is_string( $value ) ) {
					$value = str_replace( '$', $value, $replacement );
				}
	
				if ( is_array( $value ) ) {
					foreach ( array_keys( $value ) as $value_k ) {
						if ( is_array( $value[ $value_k ] ) ) {
							continue;
						}
						if ( isset( $output['choice'] ) ) {
							if ( $output['choice'] === $value_k ) {
								$value[ $output['choice'] ] = str_replace( '$', $value[ $output['choice'] ], $replacement );
							}
							continue;
						}
						$value[ $value_k ] = str_replace( '$', $value[ $value_k ], $replacement );
					}
				}
			}
			
			$value = $this->apply_pattern_replace( $value, $output );
		}
		
		return $value;
	}

	/**
	 * If we have a value_pattern defined, apply it to the value.
	 *
	 * @param 	mixed        $value 	The output value.
	 * @param 	array        $output 	The output args.
	 *
	 * @return 	string|array The output value replaced
	 */
	protected function apply_pattern_replace( $value, $output ) {
		$pattern_replace = get_prop( $output, 'pattern_replace', [] );

		if ( ! empty( $pattern_replace ) ) {
			$option_type = $this->processor;
			$option_name = 'wecodeart-settings';
			$options     = [];

			if ( $option_name ) {
				$options = ( 'site_option' === $option_type ) ? get_site_option( $option_name ) : get_option( $option_name );
			}
			
			foreach ( $pattern_replace as $search => $replace ) {
				$replacement = '';
				switch ( $option_type ) {
					case 'option':
						if ( is_array( $options ) ) {
							if ( $option_name ) {
								$subkey      = str_replace( [ $option_name, '[', ']' ], '', $replace );
								$replacement = ( isset( $options[ $subkey ] ) ) ? $options[ $subkey ] : '';
								break;
							}
							$replacement = ( isset( $options[ $replace ] ) ) ? $options[ $replace ] : '';
							break;
						}
						$replacement = get_option( $replace );
						break;
					case 'site_option':
						$replacement = ( is_array( $options ) && isset( $options[ $replace ] ) ) ? $options[ $replace ] : get_site_option( $replace );
						break;
					case 'user_meta':
						$user_id = get_current_user_id();
						if ( $user_id ) {
							$replacement = get_user_meta( $user_id, $replace, true );
						}
						break;
					case 'theme_mod':
						$replacement = get_theme_mod( $replace, $replace ); // Defaults to $replace variable
						break;
					default:
						if( is_callable( $replace ) ) {
							$replacement = call_user_func( $replace, $value );
						}
						if( is_string( $replace ) ) {
							$replacement = $replace;
						}
				}

				$replacement = ( false === $replacement ) ? '' : $replacement;

				if ( is_array( $value ) ) {
					foreach ( $value as $k => $v ) {
						$_val        = ( isset( $value[ $v ] ) ) ? $value[ $v ] : $v;
						$value[ $k ] = str_replace( $search, $replacement, $_val );
					}

					return $value;
				}

				$value = str_replace( $search, $replacement, $value );
			}
		}

		return $value;
	}

	/**
	 * Parses the output arguments.
	 * Calls the process_output method for each of them.
	 */
	protected function parse_output() {
		foreach ( $this->output as $output ) {
			$skip = false;

			// Apply any sanitization callbacks defined.
			$value = $this->apply_sanitize_callback( $output );

			// Skip if value is empty.
			if ( '' === $output['value'] ) {
				$skip = true;
			}

			// No need to proceed this if the current value is the same as in the "exclude" value.
			if ( isset( $output['exclude'] ) && is_array( $output['exclude'] ) ) {
				foreach ( $output['exclude'] as $exclude ) {
					if ( is_array( $value ) ) {
						if ( is_array( $exclude ) ) {
							$diff1 = array_diff( $value, $exclude );
							$diff2 = array_diff( $exclude, $value );

							if ( empty( $diff1 ) && empty( $diff2 ) ) {
								$skip = true;
							}
						}

						if ( isset( $output['choice'] ) && isset( $value[ $output['choice'] ] ) && $exclude == $value[ $output['choice'] ] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
							$skip = true;
						}
					}

					if ( $skip ) {
						continue;
					}

					// Skip if value is defined as excluded.
					if ( $exclude === $value || ( '' === $exclude && empty( $value ) ) ) {
						$skip = true;
					}
				}
			}

			// If excluded, move on
			if ( $skip ) {
				continue;
			}

			// If element is array, combine its selectors
			if ( isset( $output['element'] ) && is_array( $output['element'] ) ) {
				$output['element'] = array_unique( $output['element'] );
				sort( $output['element'] );
				$output['element'] = implode( ',', $output['element'] );
			}

			// Output based on context
			if ( ( is_admin() && ! is_customize_preview() ) || ( isset( $_GET['editor'] ) && '1' === $_GET['editor'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				// Check if this is an admin style.
				if ( ! isset( $output['context'] ) || ! in_array( 'editor', $output['context'], true ) ) {
					continue;
				}
			} elseif ( isset( $output['context'] ) && ! in_array( 'front', $output['context'], true ) ) {
				// Check if this is a frontend style.
				continue;
			}

			// Apply pattern
			$output = wp_parse_args( [
				'value' => $this->apply_pattern( $output )
			], $output );

			// Process value
			$this->process_output( wp_parse_args( [
				'value' => $this->process_value( $output )
			], $output ) );
		}
	}

	/**
	 * Parses an output and creates the styles array for it.
	 *
	 * @param 	array	$output The field output.
	 *
	 * @return 	null
	 */
	protected function process_output( $output ) {
		if ( ! isset( $output['element'] ) || ! isset( $output['property'] ) ) {
			return;
		}

		$output = wp_parse_args( $output, [
			'media_query' 	=> 'global',
			'units'			=> '',
			'prefix'      	=> '',
			'suffix'      	=> '',
		] );

		$value = get_prop( $output, 'value' );

		// Properties that can accept multiple values.
		$accepts_multiple = [ 'background-image', 'background' ];

		if ( in_array( $output['property'], $accepts_multiple, true ) ) {
			if (
				isset( $this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] ) && 
				! is_array( $this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] ) 
			) {
				$value = (array) $this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ];
				$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = $value;
			}
			$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ][] = $output['prefix'] . $value . $output['units'] . $output['suffix'];
			return;
		}

		if ( is_string( $value ) || is_numeric( $value ) ) {
			$value = $output['prefix'] . $value . $output['units'] . $output['suffix'];
			$this->styles[ $output['media_query'] ][ $output['element'] ][ $output['property'] ] = $value;
		}
	}

	/**
	 * Returns the property vvalue.
	 *
	 * @param 	string  		$property 	The CSS Property.
	 * @param 	mixed       	$value 		The Property Value.
	 *
	 * @return 	string|array
	 */
	protected function get_property_value( string $property, $value ) {
		return Styles::get_property_value( $property, $value );
	}

	/**
	 * Returns the value.
	 *
	 * @param 	array        	$output 	The field "output".
	 *
	 * @return 	string|array
	 */
	protected function process_value( $output ) {
		if ( isset( $output['property'] ) ) {
			return $this->get_property_value( $output['property'], $output['value'] );
		}
		
		return $output['value'];
	}

	/**
	 * Exploses the private $styles property to the world
	 *
	 * @return array
	 */
	public function get_styles() {
		return $this->styles;
	}
}