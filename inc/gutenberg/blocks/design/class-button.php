<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2024, WeCodeArt Framework
 * @since		5.0.0
 * @version		6.3.7
 */

namespace WeCodeArt\Gutenberg\Blocks\Design;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Button blocks.
 */
class Button extends Dynamic {

	use Singleton;

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'button';
	
	/**
	 * Init.
	 */
	public function init() {
		\register_block_style( $this->get_block_type(), [
			'name' 			=> 'link',
            'label'			=> __( 'Link', 'wecodeart' ),
			'inline_style' 	=> static::get_style( 'link' )
		] );
		
		\register_block_style( $this->get_block_type(), [
			'name' 			=> 'outline',
            'label'			=> __( 'Outline', 'wecodeart' ),
			'inline_style' 	=> static::get_style( 'outline' )
		] );
	}

	/**
	 * Block args.
	 *
	 * @return 	array
	 */
	public function block_type_args(): array {
		return [
			'render_callback' => [ $this, 'render' ]
		];
	}

	/**
	 * Dynamically renders the `core/button` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '' ): string {
		if ( str_contains( $content, 'javascript:void' ) ) {
			$content = str_replace(
				[
					'http://javascript:void',
					'target="_blank"',
				],
				[
					'javascript:void(0)',
					'disabled',
				],
				$content
			);
		}
	
		if ( str_contains( $content, 'href="http://#"' ) ) {
			$content = str_replace(
				[
					'href="http://#"',
					'target="_blank"',
				],
				[
					'href="#"',
					'',
				],
				$content
			);
		}

		return str_replace( '-button__width', '-button--width', $content );
	}

	/**
	 * Get Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function get_style( $class = 'link' ) {
		$inline = '';

		switch( $class ) :
			case 'outline' :
				$inline = <<<CSS
					.is-style-outline :where(.wp-block-button__link) {
						--wp--color--rgb: 52, 58, 64;
					}
					.is-style-outline.wp-block-button .wp-block-button__link.wp-block-button__link {
						--wp--bg-opacity: 0;
						background-color: rgba(var(--wp--color--rgb), var(--wp--bg-opacity));
						border-color: currentColor;
						color: var(--wp--preset--color--dark);
					}
					.is-style-outline.wp-block-button .wp-block-button__link.wp-block-button__link:is(:hover,:focus) {
						--wp--bg-opacity: 1;
						border-color: rgb(var(--wp--color--rgb));
						color: var(--wp--color--hover, white)!important;
					}
				CSS;
				break;
			case 'link' :
				$inline = <<<CSS
					.is-style-link.wp-block-button .wp-block-button__link {
						background: transparent;
						border: none;
						box-shadow: none;
						padding: 0;
					}
				CSS;
				break;
			default :
				break;
		endswitch;

		return wecodeart( 'styles' )::compress( $inline );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return <<<CSS
			.wp-block-button.has-custom-width {
				max-width: none;
			}
			.wp-block-button.has-custom-width .wp-block-button__link {
				width: 100%;
			} 
			.wp-block-button.has-custom-font-size .wp-block-button__link {
				font-size: inherit;
			}
			.wp-block-button--width-25 {
				width: calc(25% - (var(--wp--style--block-gap, 1em) * 0.75));
			}
			.wp-block-button--width-50 {
				width: calc(50% - (var(--wp--style--block-gap, 1em) * 0.5));
			}
			.wp-block-button--width-75 {
				width: calc(75% - (var(--wp--style--block-gap, 1em) * 0.25));
			}
			.wp-block-button--width-100 {
				width: 100%;
			}
			.wp-block-buttons.is-vertical > .wp-block-button--width-25 {
				width: 25%;
			}
			.wp-block-buttons.is-vertical > .wp-block-button--width-50 {
				width: 50%;
			}
			.wp-block-buttons.is-vertical > .wp-block-button--width-75 {
				width: 75%;
			}
			.wp-block-button.aligncenter {
				text-align: center;
			}
			.wp-block-button.alignright {
				text-align: right;
			}
			.wp-block-button__link.no-border-radius {
				border-radius: 0;
			}
			.wp-element-button,
			.wp-block-button__link {
				display: inline-block;
				vertical-align: middle;
				text-align: center;
				transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
				user-select: none;
				cursor: pointer;
			}
			.wp-element-button:hover,
			.wp-block-button__link:hover {
				color: var(--wp--preset--color--white);
				text-decoration: none;
			}
			.wp-element-button:focus,
			.wp-block-button__link:focus {
				outline-style: dotted;
				outline-width: thin;
			}
			.wp-element-button:is(:disabled,.disabled),
			.wp-block-button__link:is(:disabled,.disabled) {
				pointer-events: none;
				box-shadow: none;
				opacity: .7;
			}
		CSS;
	}
}
