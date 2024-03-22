<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Support\Styles
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since 		5.0.0
 * @version		6.3.7
 */

namespace WeCodeArt\Support;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Config\Interfaces\Integration;
use WeCodeArt\Config\Traits\{ Singleton, No_Conditionals };
use function WeCodeArt\Functions\get_prop;

/**
 * The Fonts object.
 */
final class Styles implements Integration {

	use Singleton;
	use No_Conditionals;

	/**
	 * Sanitize
	 *
	 * @since  	5.0.0
	 * @var 	object
	 */
	public 	$Sanitize	= null;
	
	/**
	 * Utilities
	 *
	 * @since  	5.0.0
	 * @var 	object
	 */
	public 	$Utilities	= null;
	
	/**
	 * Components
	 *
	 * @since  	6.1.5
	 * @var 	object
	 */
	public 	$Components	= null;

	/**
	 * Send to Constructor
	 */
	public function init() {
		$this->Sanitize 	= Styles\Sanitize::get_instance();
		$this->Utilities 	= Styles\Utilities::get_instance();
		$this->Components 	= Styles\Components::get_instance();
	}

	/**
	 * Register hooks
	 */
	public function register_hooks() {}

	/**
	 * Generate breakpoint
	 *
	 * @param  string   $query
	 *
	 * @return string
	 */
	public static function generate_breakpoint( string $query = '' ): string {
		return "@media (min-width:{$query})";
	}

	/**
	 * Generate breakpoint class
	 *
	 * @param  string   $class
	 * @param  string   $value
	 * @param  bool     $break
	 *
	 * @return string
	 */
	public static function generate_class( string $class = '', $value = '', $break = '' ): string {
		$mode = apply_filters( 'wecodeart/filter/styles/utilities/class', 'bootstrap', $class, $value, $break );

		switch( $mode ) :
			case 'tailwind':
				$return = ( $break ? $break . ':' : '' ) . join( '-', array_filter( [ $class, $value ], function( $i ) {
					return $i !== '';
				} ) );
			break;

			default:
				$return = join( '-', array_filter( [ $class, $break, $value ], function( $i ) {
					return $i !== '';
				} ) );
			break;
		endswitch;


		return sanitize_text_field( $return );
	}

	/**
	 * Register CSS utility to be parsed by CSS module.
	 *
	 * @param  array  $args
	 *
	 * @return void
	 */
	public static function register_utility( $args = [] ) {
		$defaults = [
			'property' 	=> '',
			'class'		=> '',
			'values'	=> [],
			'responsive'=> false,
		];

		$args 	= wp_parse_args( $args, $defaults );

		$values = (array) get_prop( $args, 'values', [] );
		
		// Bail if no values.
		if( empty( $values ) ) return;
		
		$properties	= (array) get_prop( $args, 'property' );
		$class 		= get_prop( $args, 'class', current( $properties ) );

		// Bail if no class.
		if( ! $class ) return;

		$media		= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		$responsive = get_prop( $args, 'responsive' ) && ! empty( $media );
		$container  = static::get_instance()->Utilities;

		foreach( $values as $key => $value ) {
			$_class	= static::generate_class( $class, $key );
			$value  = (string) $value;

			$output = [];
			foreach( $properties as $property ) {
				$output['.' . str_replace( ':', '\:', $_class )][$property] = "$value!important";
			}
		
			$container->set( $_class, [
				'global' => $output
			] );
			
			// Move on if not responsive
			if( ! $responsive ) continue;

			foreach( $media as $break => $query ) {
				$_break_ = static::generate_breakpoint( $query );
				$_class_ = static::generate_class( $class, $key, $break );
				
				$output = [];
				foreach( $properties as $property ) {
					$output['.' . str_replace( ':', '\:', $_class_ )][$property] = "$value!important";
				}

				$container->set( $_class_, [
					"{$_break_}" => $output
				] );
			}
		}
	}

    /**
     * Trim CSS
     *
     * @since 	3.7.7
     * @version 6.3.7
	 * 
     * @param 	string $css CSS content to trim.
     *
     * @return 	string
     */
    public static function compress( string $css = '', $options = [] ): string {
        // Return if no CSS
        if ( ! $css ) {
            return '';
        }

		$options = wp_parse_args( $options, [
			'comments' => false,
			'zeroUnit' => false,
		] );

        // Normalize whitespace
        $css = preg_replace( '/\s+/', ' ', $css );

        // Remove ; before }
        $css = preg_replace( '/;(?=\s*})/', '', $css );

        // Remove space after , : ; { } * > ~
        $css = preg_replace( '/(,|:|;|\{|}|\*\/|>|~) /', '$1', $css );

        // Remove space before , ; { } > ~
        $css = preg_replace( '/ (,|;|\{|}|>|~)/', '$1', $css );

        // Strips leading 0 on decimal values (converts 0.5px into .5px)
        $css = preg_replace( '/(:| )0\.([0-9]+)(%|em|rem|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );

		// Converts all zeros value into shorthand.
		// $css = preg_replace( '/0 0 0 0/', '0', $css ); // Invalid on data urls

		// Shorten 6-character hex color codes to 3-character where possible.
		$css = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $css );

		// Strips units if value is 0 (converts 0px to 0)
		if( get_prop( $options, 'zeroUnit' ) ) {
			$css = preg_replace( '/(:| )(\.?)0(%|em|rem|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );
		}

		// Remove comments
		if( get_prop( $options, 'comments' ) ) {
			$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		}

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
     * @since   5.0.0
     * @param   string $css
     *
     * @return  string
     */
    public static function clean_empty( string $css ): string {
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
	public static function parse( array $css = [], $context = '' ): string {
		// Pass our styles from filter.
		$css = apply_filters( 'wecodeart/filter/styles/array', $css, $context );

		// Process the array of CSS properties and produce the final CSS.
		$final_css = '';

		if ( ! is_array( $css ) || empty( $css ) ) {
			return $final_css;
		}

		foreach( $css as $query => $selectors ) {
			$final_css .= ( 'global' !== $query ) ? $query . '{' : '';
			$final_css .= self::array_to_string( $selectors );
			$final_css .= ( 'global' !== $query ) ? '}' : '';
		}

		return $final_css;
	}

	/**
	 * Utility method to convert associative array to css rules.
	 *
	 * @param 	array	$rules The associative rules array.
	 * @param 	int		$indent The indent to be used per rule.
	 *
	 * @return string
	 */
	public static function array_to_string( array $selectors = [], $indent = 0 ): string {
		$css    = '';
		$prefix = str_repeat( '  ', $indent );

		// foreach( $selectors as $selector => $properties ) {
		// 	$css_for_style = '';
		// 	foreach( $properties as $property => $value ) {
		// 		if ( is_string( $value ) && '' !== $value ) {
		// 			$css_for_style .= $property . ':' . $value . ';';
		// 		} elseif( is_array( $value ) ) {
		// 			foreach ( $value as $subvalue ) {
		// 				if ( is_string( $subvalue ) && '' !== $subvalue ) {
		// 					$css_for_style .= $property . ':' . $subvalue . ';';
		// 				}
		// 			}
		// 		}
		// 	}
			
		// 	if ( '' !== $css_for_style ) {
		// 		$css .= $selector . '{' . $css_for_style . '}';
		// 	}
		// }

		foreach ( $selectors as $key => $value ) {
			if ( is_array( $value ) ) {
				$selector   = $key;
				$properties = $value;

				$css .= $prefix . "$selector {\n";
				$css .= $prefix . self::array_to_string( $properties, $indent + 1 );
				$css .= $prefix . "}\n";
				continue;
			}

			$property 	= $key;
			$css	.= $prefix . "$property: $value;\n";
		}

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
	 * @return 	array	The generated CSS array.
	 */
	public static function string_to_array( string $css = '' ): array {
		$results = [];
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
	 * @return 	array	The generated CSS array.
	 */
	public static function string_to_array_query( string $css = '' ): array {
		$queries 	= [];
		$start 		= 0;
		$global 	= preg_replace( '/@media[^{]+\{([\s\S]+?\})\s*\}/i', '', $css );
		$queries['global'] = self::string_to_array( $global );

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
				preg_match( '/(\@media[^\{]+)\{(.*)\s+/ims', substr( $css, $start, ( $i + 1 ) - $start ), $block );
				list( , $media, $style ) = $block;
				$query 	= trim( $media );
				$styles	= self::string_to_array( $style );
				$queries[$query] = isset( $queries[$query] ) ? $queries[$query] + $styles : $styles;
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
	public static function add_prefixes( array $css ): array {
		if( is_array( $css ) ) {
			foreach( $css as $media_query => $elements ) {
				if( empty( $elements ) ) continue;
				foreach( $elements as $element => $properties ) {
					if( empty( $properties ) ) continue;
					foreach( $properties as $property => $value ) {
						// Missing then skip.
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
	 * Convert Color to RGBA.
	 *
	 * @since   5.0.0
	 * @param 	string 	$color   Color data.
	 * @param 	bool 	$alpha   Either return alpha or not
	 * @param 	bool 	$array   Either return as array or string
	 *
	 * @return 	mixed
	 */
	public static function color_to_rgba( string $color, bool $alpha = false, bool $array = false ) {
		_deprecated_function( __FUNCTION__, '6.3.1', "wecodeart( 'styles' )::hex_to_rgb" );

		return self::hex_to_rgb( $color, $alpha ? (int) $alpha : 1 , $array );
	}

	/**
	 * Add a tint to an RGB color and make it lighter.
	 *
	 * @param 	float[] $rgb_array An array representing an RGB color.
	 * @param 	float   $tint      How much of a tint to apply; a number between 0 and 1.
	 *
	 * @return 	float[] The new color as an RGB array.
	 */
	public static function rgb_tint( array $rgb_array = [], float $tint = 0 ): array {
		return [
			'r' => $rgb_array['r'] + ( 255 - $rgb_array['r'] ) * $tint,
			'g' => $rgb_array['g'] + ( 255 - $rgb_array['g'] ) * $tint,
			'b' => $rgb_array['b'] + ( 255 - $rgb_array['b'] ) * $tint,
		];
	}

	/**
	 * Get the relative luminance of a color.
	 *
	 * @link 	https://en.wikipedia.org/wiki/Relative_luminance
	 *
	 * @param 	array
	 *
	 * @return 	float	A value between 0 and 100 representing the luminance of a color.
	 */
	public static function rgb_luminance( array $rgb_array = [] ): float {
		return 0.2126 * ( $rgb_array['r'] / 255 ) + 0.7152 * ( $rgb_array['g'] / 255 ) + 0.0722 * ( $rgb_array['b'] / 255 );
	}

	/**
	 * Convert an RGB array to hexadecimal representation.
	 *
	 * @param 	array 	$rgb_array The RGB array to convert.
	 * @return 	string 	A hexadecimal representation.
	 */
	public static function rgb_to_hex( array $rgb_array = [] ): string {
		return sprintf(
			'#%02X%02X%02X',
			$rgb_array['r'],
			$rgb_array['g'],
			$rgb_array['b']
		);
	}
	
	/**
	 * Convert an RGB array to hexadecimal representation.
	 *
	 * @param 	string 	$hex
	 * @param 	integer	$alpha
	 * @param 	boolean	$array
	 *
	 * @return 	mixed
	 */
	public static function hex_to_rgb( string $hex = '', int $alpha = 1, bool $array = false ) {
		$pattern = '/rgba?\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})(?:,\s*(0|1|0?\.\d+))?\)/';
    
		// Check if rgba
		if ( preg_match( $pattern, $hex, $matches ) ) {
			$r = $matches[1];
			$r = $matches[2];
			$r = $matches[3];
			$a = isset( $matches[4] ) ? (float) $matches[4] : 1;

			return $array ? [
				'r' => $r,
				'g' => $g,
				'b' => $b,
				'a' => $a
			] : "rgba($r, $g, $b, $a)";
		}

		// Remove '#' if present
		$hex = ltrim( $hex, '#' );

		// If shorthand format, need to expand it
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if ( strlen( $hex ) !== 6 ) {
			return false;
		}
	
		// Convert hex to decimal
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		$a = max( 0, min( 1, $alpha ) ); // Ensure alpha is between 0 and 1

		return $array ? [
			'r' => $r, 
			'g' => $g, 
			'b' => $b, 
			'a' => $a
		] : "rgba($r, $g, $b, $a)";
	}

	/**
	 * Adjust Color Brightness
	 *
	 * @since   5.0.0
	 * @param 	string	$color   Color data.
	 * @param 	integer	$opacity Opacity status.
	 *
	 * @return 	string
	 */
	public static function hex_brightness( string $hex, int $steps ): string {
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
	 * Sort by breakpoints.
	 *
     * @param   array $order
     *
	 * @return  array
	 */
	public static function sort_breakpoints( array $order ): array {
		$ordered	= [];
        $breaks		= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		natsort( $breaks );

        $sortArray  = array_merge( [ 'global' ], array_map( [ __CLASS__, 'generate_breakpoint' ], array_values( $breaks ) ) );

        foreach ( $sortArray as $key ) {
            if ( array_key_exists( $key, $order ) ) {
                $ordered[$key] = $order[$key];
                unset( $order[$key] );
            }
        }

        return $ordered + $order;
	}

	/**
	 * Formats custom properties for unsupported blocks.
	 *
	 * @param 	string 	$property Custom property value to format.
	 *
	 * @return 	string
	 */
	public static function format_variable( string $property ): string {
		if ( ! str_contains( $property, 'var:' ) ) {
			return $property;
		}

		return str_replace( [ 'var:', '|' ], [ 'var(--wp--', '--' ], $property . ')' );
	}

	/**
	 * Adds shorthand CSS properties.
	 *
	 * @param array  $styles   Existing CSS array.
	 *
	 * @return array
	 */
	public static function shorthand_properties( array $styles = [] ): array {
		if ( empty( $styles ) ) {
			return $styles;
		}

		if( ! isset( $styles['spacing'] ) ) {
			return $styles;
		}

		foreach( [ 'margin', 'padding' ] as $property ) {
			$has_top    = isset( $styles['spacing'][$property]['top'] );
			$has_right  = isset( $styles['spacing'][$property]['right'] );
			$has_bottom = isset( $styles['spacing'][$property]['bottom'] );
			$has_left   = isset( $styles['spacing'][$property]['left'] );
	
			if ( ! $has_top && ! $has_right && ! $has_bottom && ! $has_left ) {
				continue;
			}
	
			$values = array_filter( [
				'top' 		=> self::format_variable( $styles['spacing'][$property]['top'] ?? 0 ),
				'right' 	=> self::format_variable( $styles['spacing'][$property]['right'] ?? 0 ),
				'bottom' 	=> self::format_variable( $styles['spacing'][$property]['bottom'] ?? 0 ),
				'left' 		=> self::format_variable( $styles['spacing'][$property]['left'] ?? 0 ),
			] );
			
			if( empty( $values ) ) {
				continue;
			}
		}

		return $styles;
	}
}