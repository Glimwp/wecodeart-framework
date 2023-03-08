<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Functions
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		5.0.0
 * @version     5.7.2
 */

namespace WeCodeArt\Functions;

defined( 'ABSPATH' ) || exit();

use function defined;
use function is_a;
use function libxml_clear_errors;
use function libxml_use_internal_errors;
use function mb_convert_encoding;
use DOMDocument;
use DOMElement;

/**
 * Helper function to encode an array to an excaped json string
 * Useful to use it for placing encoded object in html attrs
 *
 * @since	3.5
 * @version 5.7.2
 * @param 	array   $json
 *
 * @return 	string 
 */
function toJSON( $json = array() ) {
    if( ! is_array( $json ) ) {
        return null;
    }

    return htmlspecialchars( str_replace( '"', "'", json_encode( $json ) ), ENT_QUOTES, get_option( 'blog_charset' ) );
}

/**
 * Get a specific property of an array
 *
 * @since  	3.8.1
 * @version 5.0.0
 *
 * @return  null|string|mixed The value
 */
function get_prop( $array, $prop, $default = null ) {
    return _wp_array_get( $array, (array) $prop, $default );
}
    
/**
 * Kses SVG
 *
 * @param 	string 	$data
 *
 * @return 	string
 */
function kses_svg( string $data ) {
    return wp_kses( $data, [ 
        'svg' => [
            'class'  		=> true,
            'aria-hidden'  	=> true,
            'role' 			=> true,
            'focusable'    	=> true,
            'xmlns'    		=> true,
            'viewbox' 		=> true,
        ],
        'g' 	=> [],
        'path' 	=> [
            'd' 	=> true,
            'class' => true,
            'fill'	=> true
        ]
    ] );
}

/**
 * WP_Parse_Args Reccursive
 *
 * @param 	array 	$a
 * @param 	array 	$b
 *
 * @return 	array
 */
function wp_parse_args_r( &$a, $b ) {
    $a = (array) $a;
    $b = (array) $b;
    $r = $b;

    foreach ( $a as $k => &$v ) {
        if ( is_array( $v ) && isset( $r[ $k ] ) ) {
            $r[ $k ] = wp_parse_args_r( $v, $r[ $k ] );
        } else {
            $r[ $k ] = $v;
        }
    }

    return $r;
}

/**
 * Calc FN
 *
 * @param 	int 	$number1
 * @param 	string 	$action
 * @param 	int 	$number2
 * @param 	bool 	$round
 * @param 	int 	$decimals
 * @param 	int 	$precision
 *
 * @return 	array
 */
function calc( $number1, $action, $number2, $round = false, $decimals = 0, $precision = 10 ) {
    static $bc;

    if ( ! is_scalar( $number1 ) || ! is_scalar( $number2 ) ) {
        return false;
    }

    if ( ! isset( $bc ) ) {
        $bc = extension_loaded( 'bcmath' );
    }

    if ( $bc ) {
        $number1 = number_format( $number1, 10, '.', '' );
        $number2 = number_format( $number2, 10, '.', '' );
    }

    $result  = null;
    $compare = false;

    switch ( $action ) {
    case '+':
    case 'add':
    case 'addition':
        $result = ( $bc ) ? bcadd( $number1, $number2, $precision ) /* string */ : ( $number1 + $number2 );
        break;

    case '-':
    case 'sub':
    case 'subtract':
        $result = ( $bc ) ? bcsub( $number1, $number2, $precision ) /* string */ : ( $number1 - $number2 );
        break;

    case '*':
    case 'mul':
    case 'multiply':
        $result = ( $bc ) ? bcmul( $number1, $number2, $precision ) /* string */ : ( $number1 * $number2 );
        break;

    case '/':
    case 'div':
    case 'divide':
        if ( $bc ) {
            $result = bcdiv( $number1, $number2, $precision ); // String, or NULL if right_operand is 0.
        } elseif ( $number2 != 0 ) {
            $result = ( $number1 / $number2 );
        }

        if ( ! isset( $result ) ) {
            $result = 0;
        }
        break;

    case '%':
    case 'mod':
    case 'modulus':
        if ( $bc ) {
            $result = bcmod( $number1, $number2 ); // String, or NULL if modulus is 0.
        } elseif ( $number2 != 0 ) {
            $result = ( $number1 % $number2 );
        }

        if ( ! isset( $result ) ) {
            $result = 0;
        }
        break;

    case '=':
    case 'comp':
    case 'compare':
        $compare = true;
        if ( $bc ) {
            $result = bccomp( $number1, $number2, $precision ); // Returns int 0, 1 or -1.
        } else {
            $result = ( $number1 == $number2 ) ? 0 : ( ( $number1 > $number2 ) ? 1 : - 1 );
        }
        break;
    }

    if ( isset( $result ) ) {
        if ( $compare === false ) {
            if ( $round === true ) {
                $result = round( floatval( $result ), $decimals );
                if ( $decimals === 0 ) {
                    $result = (int) $result;
                }
            } else {
                $result = ( intval( $result ) == $result ) ? intval( $result ) : floatval( $result );
            }
        }

        return $result;
    }

    return false;
}

/**
 * Set Theme Settings
 *
 * @param 	array 	$target
 * @param 	array 	$array
 * @param 	mixed 	$value
 *
 * @return 	array
 */
function set_settings_array( $target, $array, $value ) {
	$key     = array_shift( $array );
	$current =& $target;
	while ( 0 < sizeof( $array ) ) {
		if ( ! property_exists( $current, $key ) ) {
			$current->{ $key } = (object) array();
		}
		$current =& $current->{ $key };
		$key     = array_shift( $array );
	}
	$current->{ $key } = $value;
	return $target;
}

/**
 * Flattens a multidimensional array to a simple array.
 *
 * @param   array $array a multidimensional array.
 *
 * @return  array a simple array
 */
function flatten( $array ) {
    $result = [];
    
    foreach ( $array as $element ) {
        if( empty( $element ) ) continue;
        if ( is_array( $element ) ) {
            array_push( $result, ...flatten( $element ) );
        } else {
            $result[] = $element;
        }
    }

    return $result;
}

/**
 * Returns a formatted DOMDocument object from a given string.
 *
 * @since 5.7.2
 *
 * @param string $html HTML string to convert to DOM.
 *
 * @return \DOMDocument
 */
function dom( string $html ): DOMDocument {
	$dom = new DOMDocument();

	if ( ! $html ) {
		return $dom;
	}

	$libxml_previous_state = libxml_use_internal_errors( true );

	$dom->preserveWhiteSpace = true;

	if ( defined( 'LIBXML_HTML_NOIMPLIED' ) && defined( 'LIBXML_HTML_NODEFDTD' ) ) {
		$options = LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;
	} elseif ( defined( 'LIBXML_HTML_NOIMPLIED' ) ) {
		$options = LIBXML_HTML_NOIMPLIED;
	} elseif ( defined( 'LIBXML_HTML_NODEFDTD' ) ) {
		$options = LIBXML_HTML_NODEFDTD;
	} else {
		$options = 0;
	}

	$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', get_option( 'blog_charset' ) ), $options );

	$dom->formatOutput = true;

	libxml_clear_errors();
	libxml_use_internal_errors( $libxml_previous_state );

	return $dom;
}

/**
 * Returns a formatted DOMElement object from a DOMDocument object.
 *
 * @since   5.7.2
 *
 * @param   string $tag            HTML tag.
 * @param   mixed  $dom_or_element DOMDocument or DOMElement.
 * @param   int    $index          Index of element to return.
 *
 * @return  \DOMElement|null
 */
function get_dom_element( string $tag, $dom_or_element, int $index = 0 ) {
	if ( ! is_a( $dom_or_element, DOMDocument::class ) && ! is_a( $dom_or_element, DOMElement::class ) ) {
		return null;
	}

	$element = $dom_or_element->getElementsByTagName( $tag )->item( $index );

	if ( ! $element ) {
		return null;
	}

	return dom_element( $element );
}

/**
 * Casts a DOMNode to a DOMElement.
 *
 * @since   5.7.2
 *
 * @param   mixed $node DOMNode to cast to DOMElement.
 *
 * @return  \DOMElement|null
 */
function dom_element( $node ) {
	if ( $node->nodeType === XML_ELEMENT_NODE ) {
		/* @var \DOMElement $node DOM Element node */
		return $node;
	}

	return null;
}

/**
 * Returns array of dom elements by class name.
 *
 * @since   5.7.2
 *
 * @param   DOMDocument|DOMElement $dom        DOM document or element.
 * @param   string                 $class_name Element class name.
 * @param   string                 $tag        Element tag name (optional).
 *
 * @return  array
 */
function get_elements_by_class_name( $dom, string $class_name, string $tag = '*' ): array {
	$elements = [];

	foreach ( $dom->getElementsByTagName( $tag ) as $element ) {
		if ( $element->hasAttribute( 'class' ) ) {
			$classes = explode( ' ', $element->getAttribute( 'class' ) );

			if ( in_array( $class_name, $classes, true ) ) {
				$elements[] = $element;
			}
		}
	}

	return $elements;
}

/**
 * Returns an HTML element with a replaced tag.
 *
 * @since   5.7.2
 *
 * @param   DOMElement $element DOM Element to change.
 * @param   string     $name    Tag name, e.g: 'div'.
 *
 * @return  DOMElement
 */
function change_tag_name( DOMElement $element, string $name ): DOMElement {
	if ( ! $element->ownerDocument ) {
		return new DOMElement( $name );
	}

	$child_nodes = [];

	foreach ( $element->childNodes as $child ) {
		$child_nodes[] = $child;
	}

	$new_element = $element->ownerDocument->createElement( $name );

	foreach ( $child_nodes as $child ) {
		$child2 = $element->ownerDocument->importNode( $child, true );
		$new_element->appendChild( $child2 );
	}

	foreach ( $element->attributes as $attr_node ) {
		$attr_name  = $attr_node->nodeName;
		$attr_value = $attr_node->nodeValue;

		$new_element->setAttribute( $attr_name, $attr_value );
	}

	if ( $element->parentNode ) {
		$element->parentNode->replaceChild( $new_element, $element );
	}

	return $new_element;
}

/**
 * Returns an Image placeholder source.
 *
 * @since   5.7.2
 *
 * @return  string
 */
function get_placeholder_source(): string {
	return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 500 300' stroke='%23AAA' stroke-width='1' stroke-dasharray='1' preserveAspectRatio='none'%3E%3Crect vector-effect='non-scaling-stroke' fill='%23FEFEFE' width='100%25' height='100%25'/%3E%3Cpath vector-effect='non-scaling-stroke' stroke-width='.5' d='M500 0 0 300'/%3E%3Cpath vector-effect='non-scaling-stroke' stroke-width='.5' d='M500 300 0 0'/%3E%3C/svg%3E";
}

/**
 * Returns lightness compare limit (light/dark).
 *
 * @since   5.7.2
 *
 * @return  float
 */
function get_lightness_limit(): float {
	return 0.6;
}

/**
 * Returns pallete color from theme JSON (including vars).
 *
 * @since   5.7.2
 *
 * @return  string
 */
function get_json_color( array $path = [], $default = false ): string {
	// Theme pallete.
    $palette 	= wecodeart_json( [ 'settings', 'color', 'palette' ], [] );
    $color      = wecodeart_json( $path, $default );

    // Is WP way of saved color.
    if( mb_strpos( $color, 'var:' ) !== false ) {
        $slug = explode( '|', $color );
        $slug = end( $slug );
    }
    
    // Or is a CSS variable.
    if( mb_strpos( $color, '--' ) !== false ) {
        $slug = explode( '--', $color );
        $slug = str_replace( ')', '', end( $slug ) );
    }
    
    // If slug found, get color.
    if ( isset( $slug  ) ) {
        $color	= get_prop( current( wp_list_filter( $palette, [
            'slug' => $slug,
        ] ) ), 'color', '#0088cc' );
    }

    return $color;
}
