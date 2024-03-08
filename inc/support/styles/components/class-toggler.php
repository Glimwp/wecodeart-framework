<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Styles\Components
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		6.1.7
 * @version		6.2.5
 */

namespace WeCodeArt\Support\Styles\Components;

defined( 'ABSPATH' ) || exit();

use function WeCodeArt\Functions\encode_svg_data;

/**
 * Toggler Styles
 */
class Toggler extends Base {
    /**
	 * Component styles.
	 *
	 * @return 	string
	 */
	public static function styles(): string {
		$symbol = <<<HTML
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
				<path stroke="black" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2" d="M4 7h22M4 15h22M4 23h22"/>
			</svg>
		HTML;

		$symbol	= encode_svg_data( $symbol );

		return "
			/* Toggler */
			.navbar-toggler {
				--wp--navbar-toggler-padding-y: 0;
				--wp--navbar-toggler-padding-x: 0;
				--wp--navbar-toggler-font-size: 1.25rem;
				--wp--navbar-toggler-icon-bg: url('$symbol');
				--wp--navbar-toggler-border-color: rgba(var(--wp--emphasis-color-rgb),0);
				--wp--navbar-toggler-border-radius: .375rem;
				--wp--navbar-toggler-focus-width: .25rem;
				--wp--navbar-toggler-transition: box-shadow 0.15s ease-in-out;
				padding: var(--wp--navbar-toggler-padding-y) var(--wp--navbar-toggler-padding-x);
				background-color: transparent;
				color: var(--wp--navbar-color);
				font-size: var(--wp--navbar-toggler-font-size, 1.25rem);
				line-height: 1;
				border: 1px solid var(--wp--navbar-toggler-border-color, transparent);
				border-radius: var(--wp--navbar-toggler-border-radius, .25rem);
				transition: var(--wp--navbar-toggler-transition, box-shadow 0.15s ease-in-out);
			}
			.navbar-toggler:hover {
				text-decoration: none;
			}
			.navbar-toggler:focus {
				text-decoration: none;
				outline: 0;
				box-shadow: 0 0 0 var(--wp--navbar-toggler-focus-width);
			} 
			.navbar-toggler-icon {
				display: inline-block;
				width: 1.5em;
				height: 1.5em;
				vertical-align: middle;
				background-image: var(--wp--navbar-toggler-icon-bg);
				background-repeat: no-repeat;
				background-position: center;
				background-size: 100%;
			}
			:where(.theme-is-dark,.navbar-dark) .navbar-toggler-icon {
				filter: invert(1) grayscale(100%) brightness(200%);
			}
        ";
	}
}
