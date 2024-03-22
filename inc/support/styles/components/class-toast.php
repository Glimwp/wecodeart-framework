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
 * @version		6.1.8
 */

namespace WeCodeArt\Support\Styles\Components;

defined( 'ABSPATH' ) || exit;

use function WeCodeArt\Functions\get_prop;

/**
 * Toast Styles
 */
class Toast extends Base {
	/**
     * Component's Style Deps.
     *
     * @var     array
     */
    static $deps = [ 'transition', 'close' ];

    /**
	 * Component styles.
	 *
	 * @return 	string
	 */
	public static function styles(): string {
		$inline = '
			/* Toast */
			.toast {
				--wp--toast-zindex: 1090;
				--wp--toast-padding-x: 0.75rem;
				--wp--toast-padding-y: 0.5rem;
				--wp--toast-spacing: var(--wp--style--block-gap);
				--wp--toast-max-width: 350px;
				--wp--toast-font-size: var(--wp--preset--font-size--small);
				--wp--toast-color: ;
				--wp--toast-bg: rgba(255,255,255,.85);
				--wp--toast-border-width: 1px;
				--wp--toast-border-color: var(--wp--preset--color--accent);
				--wp--toast-border-radius: .25rem;
				--wp--toast-box-shadow: var(--wp--preset--shadow--natural);
				--wp--toast-header-color: var(--wp--preset--color--dark);
				--wp--toast-header-bg: rgba(255,255,255,.85);
				--wp--toast-header-border-color: var(--wp--preset--color--accent);
				width: var(--wp--toast-max-width);
				max-width: 100%;
				font-size: var(--wp--toast-font-size);
				color: var(--wp--toast-color);
				pointer-events: auto;
				background-color: var(--wp--toast-bg);
				background-clip: padding-box;
				border: var(--wp--toast-border-width) solid var(--wp--toast-border-color);
				box-shadow: var(--wp--toast-box-shadow);
				border-radius: var(--wp--toast-border-radius);
			}
			.toast.showing {
				opacity: 0;
			}
			.toast:not(.show) {
				display: none;
			}
			.toast-container {
				--wp--toast-zindex: 1090;
				position: absolute;
				z-index: var(--wp--toast-zindex);
				width: max-content;
				max-width: 100%;
				pointer-events: none;
			}
			.toast-container > :not(:last-child) {
				margin-bottom: var(--wp--toast-spacing);
			}
			.toast-header {
				display: flex;
				align-items: center;
				padding: var(--wp--toast-padding-y) var(--wp--toast-padding-x);
				color: var(--wp--toast-header-color);
				background-color: var(--wp--toast-header-bg);
				background-clip: padding-box;
				border-bottom: var(--wp--toast-border-width) solid var(--wp--toast-header-border-color);
				border-top-left-radius: calc(var(--wp--toast-border-radius) - var(--wp--toast-border-width));
				border-top-right-radius: calc(var(--wp--toast-border-radius) - var(--wp--toast-border-width));
			}
			.toast-header .btn-close {
				margin-right: calc(-.5 * var(--wp--toast-padding-x));
				margin-left: var(--wp--toast-padding-x);
			}
			.toast-body {
				padding: var(--wp--toast-padding-x);
				word-wrap: break-word;
			}
        ';

		return $inline;
	}
}
