<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Styles
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		4.2.0
 * @version		4.2.0
 */

namespace WeCodeArt\Support;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Integration;
use WeCodeArt\Admin\Request;

/**
 * The Fonts object.
 */
final class Styles implements Integration {

	use Singleton;

	/**
	 * Get Conditionals
	 *
	 * @return void
	 */
	public static function get_conditionals() {
		wecodeart( 'conditionals' )->set( [
			'with_styles' => Styles\Condition::class,
		] );
		
		return [ 'with_styles' ];
	}

	/**
	 * Send to Constructor
	 */
	public function register_hooks() {
		do_action( 'wecodeart/support/styles/init', $this );
	}

    /**
     * Trim CSS
     *
     * @since 	3.7.7
     * @version 4.2.0
     * @param 	string $css CSS content to trim.
     *
     * @return 	string
     */
    public static function compress( string $css = '' ) {
        // Return if no CSS
        if ( ! $css ) {
            return '';
        }

        // remove comments
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

        // Normalize whitespace
        $css = preg_replace( '/\s+/', ' ', $css );

        // Remove ; before }
        $css = preg_replace( '/;(?=\s*})/', '', $css );

        // Remove space after , : ; { } */ >
        $css = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $css );

        // Remove space before , ; { }
        $css = preg_replace( '/ (,|;|\{|})/', '$1', $css );

        // Strips leading 0 on decimal values (converts 0.5px into .5px)
        $css = preg_replace( '/(:| )0\.([0-9]+)(%|em|rem|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );

        // Strips units if value is 0 (converts 0px to 0)
        $css = preg_replace( '/(:| )(\.?)0(%|em|rem|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );

        // Trim
        $css = trim( $css );
        
        // Double call for media queries
        $css = self::clean_empty( $css );
		$css = self::clean_empty( $css );
		
        // Return minified CSS
        return $css;
    }

    /**
     * Get CSS without empty selector
     * Call after minification of CSS
     *
     * @since   4.2.0
     * @param   string $css
     *
     * @return  string
     */
    public static function clean_empty( string $css ) {
        $css_explode        = explode( '}', $css );
        $result             = '';
        $double_braces_open = false;
        foreach ( $css_explode as $index => $item ) {
            $is_double_braces = substr_count( $item, '{' ) > 1;
            if ( $is_double_braces || ( $item != '' && substr( $item, -1 ) != '{' ) ) {
                if ( $is_double_braces ) {
                    $inner_explode = explode( '{', $item );
                    $inner_item    = $inner_explode[0] . '{';
                    if ( isset( $inner_explode[2] ) && $inner_explode[2] != '' ) {
                        $inner_item .= $inner_explode[1] . '{' . $inner_explode[2] . '}';
                    }
                    $result .= $inner_item;
                } else {
                    $result .= $item . '}';
                }

                if ( $is_double_braces ) {
                    $double_braces_open = true;
                }
            }
            if ( $double_braces_open && $item == '' ) {
                $result            .= '}';
                $double_braces_open = false;
            }
        }

        return $result;
    }

    /**
	 * Gets the array of generated styles and creates the minimized, inline CSS.
	 *
	 * @param 	array   $css    The CSS definitions array.
	 *
	 * @return 	string          The generated CSS.
	 */
	public static function parse( array $css = [], $context = '' ) {
		// Pass our styles from filter.
		$css = apply_filters( 'wecodeart/filter/styles/array', $css, $context );

		// Process the array of CSS properties and produce the final CSS.
		$final_css = '';

		if ( ! is_array( $css ) || empty( $css ) ) {
			return $final_css;
		}

		foreach( $css as $query => $selectors ) {
			$final_css .= ( 'global' !== $query ) ? $query . '{' : '';
			$final_css .= self::array_to_css( $selectors );
			$final_css .= ( 'global' !== $query ) ? '}' : '';
		}

		return $final_css;
	}

	/**
	 * Utility method to convert associative array to css rules.
	 *
	 * @param 	array 		$rules The associative rules array.
	 * @param 	int   		$indent The indent to be used per rule.
	 *
	 * @return string
	 */
	public static function array_to_css( array $selectors = [], $indent = 0 ) {
		$css    = '';
		$prefix = str_repeat( '  ', $indent );

		foreach( $selectors as $selector => $properties ) {
			$css_for_style = '';
			foreach( $properties as $property => $value ) {
				if ( is_string( $value ) && '' !== $value ) {
					$css_for_style .= $property . ':' . $value . ';';
				} elseif( is_array( $value ) ) {
					foreach ( $value as $subvalue ) {
						if ( is_string( $subvalue ) && '' !== $subvalue ) {
							$css_for_style .= $property . ':' . $subvalue . ';';
						}
					}
				}
				$value = ( is_string( $value ) ) ? $value : '';
			}
			if ( '' !== $css_for_style ) {
				$css .= $selector . '{' . $css_for_style . '}';
			}
		}

		// foreach ( $selectors as $key => $value ) {
		// 	if ( is_array( $value ) ) {
		// 		$selector   = $key;
		// 		$properties = $value;

		// 		$css .= $prefix . "$selector {\n";
		// 		$css .= $prefix . self::array_to_css( $properties, $indent + 1 );
		// 		$css .= $prefix . "}\n";
		// 		continue;
		// 	}

		// 	$property 	= $key;
		// 	$css	.= $prefix . "$property: $value;\n";
		// }

		if ( preg_match( '#</?\w+#', $css ) ) {
			$css = '';
		}

		return $css;
	}
	
	/**
	 * Gets the array of generated styles from a string.
	 *
	 * @param 	string	$css	The CSS definitions string.
	 * @param 	bool   	$group	If true, it will group CSS into media queries.
	 *
	 * @return 	array          	The generated CSS array.
	 */
	public static function break( string $css = '' ) {
		$results = [];
		// preg_match_all( '/(.+?)\s?\{\s?(.+?)\s?\}/', $css, $arr );
		// preg_match_all( '/([^\{\}]+)\{([^\}]*)\}/ims', $css, $arr );
		
		// Escape base64 images
		$css = preg_replace( '/(data\:[^;]+);/i', '$1~£&#', $css );
		// Split rules
		preg_match_all( '/(?ims)([a-z0-9\s\,\.\:#_\-@]+)\{([^\}]*)\}/', $css, $arr );
		foreach( $arr[0] as $i => $x ) {
			$selector 	= trim( $arr[1][$i] );
			$results[$selector] = [];
			foreach( explode( ';', $arr[2][$i] ) as $attr ) {
				if ( strlen( trim( $attr ) ) > 0) {
					list( $name, $value ) = explode( ':', trim( $attr ), 2 );
					$results[$selector][trim( $name )] = str_replace( '~£&#', ';', trim( $value ) );
				}
			}
		}

		return $results;
	}

	/**
	 * Gets the array of CSS with media queries.
	 *
	 * @param 	string   $css	The CSS definitions string.
	 *
	 * @return 	array          	The generated CSS array.
	 */
	public static function break_queries( string $css = '' ) {
		$queries 	= [];
		$start 		= 0;
		$global 	= preg_replace( '/@media[^{]+\{([\s\S]+?\})\s*\}/i', '', $css );
		$queries['global'] = self::break( $global );

		while( ( $start = strpos( $css, '@media', $start ) ) !== false ) {
			$s = [];
			$i = strpos( $css, '{', $start );
			if ( $i !== false ) {
				array_push( $s, $css[$i] );
				$i++;
				while( ! empty( $s ) ) {
					if ( $css[$i] === '{' ) array_push( $s, '{' ); 
					elseif ( $css[$i] === '}' ) array_pop( $s ); 
					$i++;
				}
				preg_match( '/(\@media[^\{]+)\{(.*)\}\s+/ims', substr( $css, $start, ( $i + 1 ) - $start ), $block );
				list( , $media, $styles ) = $block;
				$styles = self::break( $styles );
				$queries[trim( $media)] = isset( $queries[trim( $media)] ) ? $queries[trim( $media)] + $styles : $styles;
				$start = $i;
			}
		}
		
		return $queries;
	}

	/**
	 * Add prefixes if necessary.
	 *
	 * @param  array $css The CSS definitions array.
	 *
	 * @return array
	 */
	public static function add_prefixes( array $css ) {
		if( is_array( $css ) ) {
			foreach( $css as $media_query => $elements ) {
				if( empty( $elements ) ) continue;
				foreach( $elements as $element => $properties ) {
					if( empty( $properties ) ) continue;
					foreach( $properties as $property => $value ) {
						if( ! $property ) continue;
						// Add -webkit-* and -moz-*.
						if ( is_string( $property ) && in_array( $property, [
							'border-radius',
							'box-shadow',
							'box-sizing',
							'text-shadow',
							'transform',
							'background-size',
							'transition',
							'transition-property',
						], true ) ) {
							unset( $css[ $media_query ][ $element ][ $property ] );
							$css[ $media_query ][ $element ][ '-webkit-' . $property ] = $value;
							$css[ $media_query ][ $element ][ '-moz-' . $property ]    = $value;
							$css[ $media_query ][ $element ][ $property ]              = $value;
						}

						// Add -ms-* and -o-*.
						if ( is_string( $property ) && in_array( $property, [
							'transform',
							'background-size',
							'transition',
							'transition-property',
						], true ) ) {
							unset( $css[ $media_query ][ $element ][ $property ] );
							$css[ $media_query ][ $element ][ '-ms-' . $property ] = $value;
							$css[ $media_query ][ $element ][ '-o-' . $property ]  = $value;
							$css[ $media_query ][ $element ][ $property ]          = $value;
						}
					}
				}
			}
		}

		return $css;
    }
	
	/**
	 * Check if string is empty without accepting zero
	 *
	 * @since   4.2.0
	 * @param 	string $var Var to check.
	 *
	 * @return 	bool
	 */
	public static function is_empty( $var ) {
		return empty( $var ) && 0 !== $var;
	}

	/**
	 * Get block attribute value with default
	 *
	 * @since   4.2.0
	 * @param 	mixed $attr 	Attributes.
	 * @param 	mixed $default 	Default value.
	 *
	 * @return 	mixed
	 */
	public static function get_attr_value( $attr, $default = 'unset' ) {
		if ( ! self::is_empty( $attr ) ) {
			return $attr;
		} else {
			return $default;
		}
	}

	/**
	 * Get CSS value
	 *
	 * @since   4.2.0
	 * @param  	string $property	CSS prop.
	 * @param  	string $value		CSS value.
	 *
	 * @return 	mixed				CSS value depends on $property
	 */
	public static function get_property_value( string $property, $value ) {
		return Styles\Property::get_property_value( $property, $value );
	}

	/**
	 * Convert HEX to RGBA.
	 *
	 * @since   4.2.0
	 * @param 	string $color   Color data.
	 * @param 	bool   $opacity Opacity status.
	 *
	 * @return 	mixed
	 */
	public static function hex_rgba( string $color, $opacity = false ) {
		$default = 'rgb(0,0,0)';

		if ( empty( $color ) ) {
			return $default;
		}

		if ( '#' == $color[0] ) {
			$color = substr( $color, 1 );
		}

		if ( strlen( $color ) == 6 ) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}

		$rgb = array_map( 'hexdec', $hex );

		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		return $output;
	}

	/**
	 * Color lightness
	 *
	 * @since   4.2.0
	 * @param  	string 	$color  Color in HEX/RGB/RGBA format
	 *
	 * @return 	string
	 */
	public static function color_lightness( string $color ) {
		$mode 	= ( false === strpos( $color, 'rgba' ) ) ? 'hex' : 'rgba';
		$color 	= $mode === 'hex' ? self::hex_rgba( $color ) : $color; 
		preg_match_all( "/\(([^\]]*)\)/" , $color, $matches );
		return intval( array_sum( explode( ',', current( $matches[1] ) ) ) );
	}

	/**
	 * Adjust Color Brightness
	 *
	 * @since   4.2.0
	 * @param 	string	$color   Color data.
	 * @param 	integer	$opacity Opacity status.
	 *
	 * @return 	string
	 */
	public static function hex_brightness( string $hex, $steps ) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max( -255, min( 255, $steps ) );
	
		// Normalize into a six character long hex string
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0 , 1 ), 2 );
			$hex .= str_repeat( substr( $hex, 1, 1 ), 2 );
			$hex .= str_repeat( substr( $hex, 2, 1 ), 2 );
		}
	
		// Split into three parts: R, G and B
		$color_parts = str_split( $hex, 2 );
		$return = '#';
	
		foreach ( $color_parts as $color ) {
			$color   = hexdec( $color ); // Convert to decimal
			$color   = max( 0, min( 255, $color + $steps ) ); // Adjust color
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
		}
	
		return $return;
	}

	/**
	 * Sanitize rgba color.
	 *
	 * @since   4.2.0
	 * @param 	string $value Color in rgba format.
	 *
	 * @return 	string
	 */
	public static function sanitize_rgba( string $value = '' ) {
		$red   = 'rgba(0,0,0,0)';
		$green = 'rgba(0,0,0,0)';
		$blue  = 'rgba(0,0,0,0)';
		$alpha = 'rgba(0,0,0,0)'; // If empty or an array return transparent
		
		if ( empty( $value ) || is_array( $value ) ) {
			return '';
		}

		// By now we know the string is formatted as an rgba color so we need to further sanitize it.
		$value = str_replace( ' ', '', $value );
		sscanf( $value, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );

		return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
	}
	
	/**
	 * Sanitize hex color.
	 *
	 * @since   4.2.0
	 * @param 	string $value Color in hex format.
	 *
	 * @return 	string
	 */
	public static function sanitize_hex( string $value = '' ) {
		return sanitize_hex_color( $value );
	}

	/**
	 * Sanitize color.
	 *
	 * @param 	string $value recieved value.
	 *
	 * @return 	string
	 */
	public static function sanitize_color( string $value = '' ) {
		$is_var = ( strpos( $value, 'var' ) !== false );
	
		if ( $is_var ) {
			return sanitize_text_field( $value );
		}
	
		// Is this an rgba color or a hex?
		$mode = ( false === strpos( $value, 'rgba' ) ) ? 'hex' : 'rgba';
	
		if ( 'rgba' === $mode ) {
			return self::sanitize_rgba( $value );
		} else {
			return self::sanitize_hex( $value );
		}
	}
}