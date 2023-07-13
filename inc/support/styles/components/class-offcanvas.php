<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Styles\Components
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		6.1.5
 * @version		6.1.5
 */

namespace WeCodeArt\Support\Styles\Components;

defined( 'ABSPATH' ) || exit();

use function WeCodeArt\Functions\get_prop;

/**
 * Offcanvas Styles
 */
class OffCanvas extends Base {
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
		// $breaks	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		
		$inline = '
			/* Offcanvas */
			.offcanvas {
				position: fixed;
				bottom: 0;
				display: flex;
				flex-direction: column;
				max-width: 100%;
				visibility: hidden;
				background-color: rgb(var(--wp--background--rgb, 255,255,255));
				background-clip: padding-box;
				outline: 0;
				transition: transform 0.3s ease-in-out;
				z-index: 1045;
			}
			.offcanvas-backdrop {
				position: fixed;
				top: 0;
				left: 0;
				width: 100vw;
				height: 100vh;
				background-color: #000;
				z-index: 1040;
			}
			.offcanvas-backdrop.fade {
				opacity: 0;
			}
			.offcanvas-backdrop.show {
				opacity: 0.5;
			}
			.offcanvas-header {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 1rem 1rem;
			}
			.offcanvas-header .btn-close {
				padding: 0.5rem 0.5rem;
				margin-top: -0.5rem;
				margin-right: -0.5rem;
				margin-bottom: -0.5rem;
			}
			.offcanvas-title {
				margin-bottom: 0;
				line-height: 1.5;
			} 
			.offcanvas-body {
				flex-grow: 1;
				padding: 1rem 1rem;
				overflow-y: auto;
			}
			.offcanvas-start {
				top: 0;
				left: 0;
				width: 400px;
				border-right: 1px solid rgba(0, 0, 0, 0.2);
				transform: translateX(-100%);
			}
			.offcanvas-end {
				top: 0;
				right: 0;
				width: 400px;
				border-left: 1px solid rgba(0, 0, 0, 0.2);
				transform: translateX(100%);
			}
			.offcanvas-top {
				top: 0;
				right: 0;
				left: 0;
				height: 30vh;
				max-height: 100%;
				border-bottom: 1px solid rgba(0, 0, 0, 0.2);
				transform: translateY(-100%);
			} 
			.offcanvas-bottom {
				right: 0;
				left: 0;
				height: 30vh;
				max-height: 100%;
				border-top: 1px solid rgba(0, 0, 0, 0.2);
				transform: translateY(100%);
			}
			.offcanvas.show {
				transform: none;
			}	
		';

		return $inline;
	}
}
