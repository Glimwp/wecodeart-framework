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
 * @since		6.2.8
 * @version		6.2.8
 */

namespace WeCodeArt\Support\Styles\Components;

defined( 'ABSPATH' ) || exit();

use function WeCodeArt\Functions\get_prop;

/**
 * Lightbox Styles
 */
class Lightbox extends Base {
	/**
     * Component's Style Deps.
     *
     * @var     array
     */
    static $deps = [];

    /**
	 * Component styles.
	 *
	 * @return 	string
	 */
	public static function styles(): string {
		$breaks	= wecodeart_json( [ 'settings', 'custom', 'breakpoints' ], [] );
		
		$inline = '
			/* Lightbox Container */
			.wp-lightbox-container {
				position: relative;
				display: flex;
				flex-direction: column;
			}
			
			.wp-lightbox-container img {
				cursor: zoom-in;
			}
			
			.wp-lightbox-container img:hover + button {
				opacity: 1;
			}
			
			.wp-lightbox-container button {
				position: absolute;
				top: 5px;
				right: 5px;
				display: flex;
				justify-content: center;
				align-items: center;
				width: 20px;
				height: 20px;
				background-color: rgb(90 90 90 / 25%);
				backdrop-filter: blur(20px) saturate(180%);
				border: none;
				border-radius: 0;
				padding: 0;
				opacity: 0;
				text-align: center;
				transition: opacity 0.2s ease;
				cursor: zoom-in;
				z-index: 100;
			}
			
			.wp-lightbox-container button:focus-visible {
				outline: 3px auto rgb(90 90 90 / 25%);
				outline: 3px auto -webkit-focus-ring-color;
				outline-offset: 3px;
			}
	
			.wp-lightbox-container button:hover {
				cursor: pointer;
				opacity: 1;
			}
	
			.wp-lightbox-container button:focus {
				opacity: 1;
			}
			
			.wp-lightbox-container button:where(:hover,:focus,:not(:hover):not(:active):not(.has-background)) {
				background-color: rgb(90 90 90 / 25%);
				border: none;
			}  
			
			/* Lightbox Overlay */
			.wp-lightbox-overlay {
				position: fixed;
				width: 100vw;
				height: 100vh;
				top: 0;
				left: 0;
				visibility: hidden;
				overflow: hidden;
				cursor: zoom-out;
				z-index:100000;
			}
			
			.wp-lightbox-overlay .close-button {
				position: absolute;
				top: calc(env(safe-area-inset-top) + 16px);
				right: calc(env(safe-area-inset-right) + 16px);
				padding: 0;
				min-width: 40px; 
				min-height: 40px;
				display: flex;
				align-items: center;
				justify-content: center;
				z-index: 5000000;
				cursor: pointer;
			}
			
			.wp-lightbox-overlay::where(:hover,:focus,:not(:hover):not(:active):not(.has-background)) {
				background: none;
				border: none; 
			}
			
			.wp-lightbox-overlay .lightbox-image-container {
				position: absolute;
				overflow: hidden;
				top: 50%;
				left: 50%;
				transform-origin: top left;
				transform: translate(-50%, -50%);
				width: var(--wp--lightbox-container-width);
				height: var(--wp--lightbox-container-height);
				z-index: 9999999999;
			}
			
			.wp-lightbox-overlay .wp-block-image {
				position: relative;
				display: flex;
				justify-content: center;
				align-items: center;
				width: 100%;
				height: 100%;
				margin: 0!important;
				box-sizing: border-box;
				transform-origin: 0 0;
				z-index: 3000000;
			}
			
			.wp-lightbox-overlay .wp-block-image img {
				min-width: var(--wp--lightbox-image-width);
				min-height: var(--wp--lightbox-image-height);
				width: var(--wp--lightbox-image-width);
				height: var(--wp--lightbox-image-height);
			}

			.wp-lightbox-overlay .wp-block-image img[src=""] {
				display: none;
			}
			
			.wp-lightbox-overlay .wp-block-image figcaption {
				display: none;
			} 
			
			.wp-lightbox-overlay button {
				border: none;
				background: none;
			}
			
			.wp-lightbox-overlay .scrim {
				width: 100%;
				height: 100%;
				position: absolute;
				z-index: 2000000;
				background-color: rgb(255, 255, 255);
				opacity: 0.9;
			}
			
			.wp-lightbox-overlay.active {
				visibility: visible;
				animation: both turn-on-visibility 0.25s;
			}

			.wp-lightbox-overlay.active img {
				animation: both turn-on-visibility 0.35s;
			}

			.wp-lightbox-overlay.hideanimationenabled:not(.active) {
				animation: both turn-off-visibility 0.35s;
			}

			.wp-lightbox-overlay.hideanimationenabled:not(.active) img {
				animation: both turn-off-visibility 0.25s;
			}
			
			@media (prefers-reduced-motion: no-preference) {
				.wp-lightbox-overlay.zoom.active {
					animation: none;
					opacity: 1;
					visibility: visible;
				}
				.wp-lightbox-overlay.zoom.active .lightbox-image-container {
					animation: lightbox-zoom-in .4s;
				}
				.wp-lightbox-overlay.zoom.active .lightbox-image-container img {
					animation: none;
				}
				.wp-lightbox-overlay.zoom.active .scrim {
					animation: turn-on-visibility .4s forwards;
				}
				.wp-lightbox-overlay.zoom.hideanimationenabled:not(.active) {
					animation: none;
				}
				.wp-lightbox-overlay.zoom.hideanimationenabled:not(.active) .lightbox-image-container {
					animation: lightbox-zoom-out .4s;
				}
				.wp-lightbox-overlay.zoom.hideanimationenabled:not(.active) .lightbox-image-container img {
					animation: none;
				}
				.wp-lightbox-overlay.zoom.hideanimationenabled:not(.active) .scrim {
					animation: turn-off-visibility .4s forwards;
				}
			}
			
			@keyframes turn-on-visibility {
				0% {
					opacity: 0;
				}
				to {
					opacity: 1;
				}
			}
			
			@keyframes turn-off-visibility {
				0% {
					opacity: 1;
					visibility: visible;
				}
				99% {
					opacity: 0;
					visibility: visible;
				}
				to {
					opacity: 0;
					visibility: hidden;
				}
			}
			
			@keyframes lightbox-zoom-in {
				0% {
					transform: translate(calc(-50vw + var(--wp--lightbox-initial-left-position)), calc(-50vh + var(--wp--lightbox-initial-top-position))) scale(var(--wp--lightbox-scale));
				}
				to {
					transform: translate(-50%, -50%) scale(1, 1);
				}
			}
			
			@keyframes lightbox-zoom-out {
				0% {
					visibility: visible;
					transform: translate(-50%, -50%) scale(1, 1);
				}
				99% {
					visibility: visible;
				}
				to {
					visibility: hidden;
					transform: translate(calc(-50vw + var(--wp--lightbox-initial-left-position)), calc(-50vh + var(--wp--lightbox-initial-top-position))) scale(var(--wp--lightbox-scale));
				}
			}
        ';

		return $inline;
	}
}
