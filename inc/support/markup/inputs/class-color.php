<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework 
 * @subpackage 	Markup\Inputs
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		6.0.0
 * @version		6.1.9
 */

namespace WeCodeArt\Support\Markup\Inputs;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Support\Markup\Inputs\Basic;
use function WeCodeArt\Functions\get_prop;

/**
 * Standard Inputs Markup
 */
class Color extends Basic {
    /**
     * Input's Style Deps.
     *
     * @since   6.0.0
     * @var     array
     */
    static $style_deps = [ 'basic' ];

    /**
	 * Constructor 
	 */
	public function __construct( string $type = 'color', array $args = [] ) {
        parent::__construct( $type, $args );
    }

    /**
	 * Input styles.
	 *
	 * @return 	string
	 */
	public static function styles(): string {
		return '
			::-webkit-color-swatch-wrapper {
				padding: 0;
			}
            .form-control[type="color"] {
                width: 3rem;
                height: calc(1.5em + var(--wp--input--padding-x) + 5px);
                padding: var(--wp--input--padding-y);
            }
            .form-control[type="color"]:not(:disabled):not([readonly]) {
                cursor: pointer;
            }
            .form-control[type="color"]::-moz-color-swatch {
                border: 0 !important;
                border-radius: var(--wp--input--border-radius);
            }
            .form-control[type="color"]::-webkit-color-swatch {
                border-radius: var(--wp--input--border-radius);
            }
            .form-control[type="color"].form-control-sm {
                height: calc(1.5em + 0.5rem + 5px);
            }
            .form-control[type="color"].form-control-lg {
                height: calc(1.5em + 1rem + 5px);
            }

            .was-validated .form-control[type="color"]:is(:valid,:invalid),
            .form-control[type="color"]:is(.is-valid,.is-invalid) {
                width: calc(3rem + calc(1.5em + var(--wp--input--padding-x)));
            }
        ';
	}
}