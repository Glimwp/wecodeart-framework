<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2023, WeCodeArt Framework
 * @since		6.0.0
 * @version		6.0.0
 */

namespace WeCodeArt\Gutenberg\Blocks\Text;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;

/**
 * Gutenberg Heading block.
 */
class Heading extends Dynamic {

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
	protected $block_name = 'heading';

	/**
	 * Init.
	 */
	public function init() {
		\register_block_style( $this->get_block_type(), [
			'name' 			=> 'underline-brush',
            'label'			=> esc_html__( 'Underline Brush', 'wecodeart' ),
			'inline_style' 	=> static::get_style( 'underline-brush' )
		] );
	}

	/**
	 * Block styles
	 *
	 * @param 	string	$class
	 *
	 * @return 	string 	The block styles.
	 */
	public static function get_style( string $class = '' ): string {
		$inline = '';

		switch( $class ) :
			case 'underline-brush' :
				$symbol = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 381 14.2"%3E%3Cpath d="M2.6 12.7A6.2 6.2 0 0 1 0 9.8V9a4 4 0 0 1 3-2 56.5 56.5 0 0 0 2-.4 5.2 5.2 0 0 1 2-.3A3 3 0 0 0 8 6c.2 0 .6.2.9-.2H9l3.3-.3 1.4-.2c.5 0 1 .2 1.3-.2h.3a27.6 27.6 0 0 0 3.7-.3 13.7 13.7 0 0 0 1.7-.2c.5 0 1 .1 1.6-.3 1.3.2 2.6-.2 3.8-.2 1.3 0 2.6-.4 4-.2 1-.5 2-.1 3-.3 1.3-.2 2.5 0 3.8-.3s2.5 0 3.7-.2c1.7-.3 3.3 0 5-.2a24.6 24.6 0 0 1 3.9-.2c1 0 2.1-.4 3.2-.3a11.2 11.2 0 0 0 3.3 0 40.2 40.2 0 0 1 5.8-.2c1.3 0 2.5-.3 3.9-.3a17 17 0 0 0 3.8 0c2.3-.4 4.5 0 6.8-.2 2.6-.3 5.2 0 7.8-.3a42.7 42.7 0 0 1 3.4-.2 33.4 33.4 0 0 0 3.5 0c3-.4 5.8 0 8.7-.3l4.3-.2a36.8 36.8 0 0 0 4.3 0 46.5 46.5 0 0 1 5.2-.2 48 48 0 0 0 5.2 0 60.7 60.7 0 0 1 6.6-.2h6.6c3.8-.4 7.5-.2 11.3-.3h34.1c1.1 0 2.2.4 3.3.3 1-.1 2.1 0 3.2 0h6.5a19.5 19.5 0 0 1 3.4 0 38.7 38.7 0 0 0 6.0.0c2 0 3.9-.2 5.7 0 3 .3 6.1 0 9.1.2 2.3.3 4.7 0 7 .3 2.2.2 4.4 0 6.7.2 2.3.3 4.7 0 7 .2 3 .4 6 .1 9 .2a10.7 10.7 0 0 0 3.5.3h3.5c1.6-.1 3 .3 4.6.2a22.1 22.1 0 0 1 4.5 0c2.3.4 4.5 0 6.7.3 2.3.3 4.5 0 6.7.2 2 .3 3.8 0 5.7.3 1.8.3 3.7 0 5.6.2 1.6.3 3.3 0 4.9.3 1.6.2 3.2 0 4.8.2 1.5.2 3 0 4.5.2 1.4.3 2.8 0 4.1.3 1.5.2 3 0 4.3.2 1.5.3 3 0 4.3.3 1.5.2 3 0 4.5.2 1.4.2 2.8 0 4.2.2 1.2.2 2.4 0 3.6.2 1.3.3 2.6 0 3.8.3 1 .2 2 0 3 .2 1.3.3 2.6 0 3.9.3 1 .1 2 0 3 .2 1.2.2 2.3 0 3.3.2 1.1.2 2.2 0 3.3.3 1 .2 2 0 3 .2s2 0 3 .2a23 23 0 0 0 2.5.3 8.6 8.6 0 0 0 1.6.2 2.4 2.4 0 0 1 1.5.2h.4c1.2 0 2.5 0 3.7.3a18.6 18.6 0 0 1 2 .2c.7.2 1.4 0 2 .3.6.2 1.2 0 1.8.2a6 6 0 0 0 1.8.2c.5 0 .9.3 1.4.3a1.3 1.3 0 0 1 1.2.2h.1l3.4.2a34.2 34.2 0 0 0 3.4.3 2.3 2.3 0 0 1 1.3.6l2.6 2.2v.9h-.4l-.1.5a.8.8 0 0 1-.2 0 7.2 7.2 0 0 1-2.7-.4.7.7 0 0 0-.2 0 19.7 19.7 0 0 1-3.1-.3 8.5 8.5 0 0 1-2.6-.5 3.8 3.8 0 0 0-.7-.2 6.4 6.4 0 0 0-1 0h-3.6a53.3 53.3 0 0 1-5.5-.3c-.9-.1-1.8 0-2.7-.2-1-.2-1.9 0-2.8-.3a18.4 18.4 0 0 0-2.5-.2 8.6 8.6 0 0 0-1.6-.2 2.3 2.3 0 0 1-1.6-.2h-.1l-4.7-.3-2.4-.2a13.2 13.2 0 0 1-2.4 0 17.4 17.4 0 0 0-3.8-.2 16.2 16.2 0 0 0-2.4-.3 6.7 6.7 0 0 1-2.4 0 23.2 23.2 0 0 0-3.8-.2l-2.4-.3a14.6 14.6 0 0 1-2.5 0c-1.5-.3-3 0-4.5-.2-1.6-.3-3.3 0-4.9-.3-1.4-.2-2.7 0-4-.2-2-.3-3.8 0-5.7-.3-1.7-.2-3.4 0-5.1-.2-1.9-.3-3.7 0-5.5-.2-2-.3-3.8 0-5.7-.3-2.2-.3-4.4 0-6.5-.2-2.1-.3-4.1 0-6.1-.2-2.6-.4-5.2 0-7.8-.3a49.8 49.8 0 0 0-3.4-.2 33.4 33.4 0 0 1-3.4 0c-2.8-.4-5.5 0-8.2-.3-2.5-.2-5.1.1-7.6-.2-2.7-.3-5.5 0-8.2-.2-2.8-.3-5.6 0-8.3-.2-4-.3-8 0-12.1-.2-2.6-.2-5.1-.1-7.7-.2h-23a19.5 19.5 0 0 1-3.3 0 37.1 37.1 0 0 0-5.9-.2 49.7 49.7 0 0 0-6 0c-3 .3-5.9.2-8.8.2h-17.8a106.7 106.7 0 0 1-5.5.3 54.8 54.8 0 0 0-5.6 0c-2.8.3-5.6 0-8.4.2-2.5.3-5 0-7.6.3-2.6.3-5.1 0-7.7.2-2.1.3-4.3 0-6.4.3a46 46 0 0 1-4.8.1h-4.8c-1.7 0-3.4-.3-5.1-.2a7.2 7.2 0 0 1-1.2-.2 8.1 8.1 0 0 1-1.1-.2 5.1 5.1 0 0 0-2.6.2 7.9 7.9 0 0 1-2 .4 13.7 13.7 0 0 0-1.7.3c-.5 0-1.1-.2-1.6.2H77a17 17 0 0 0-4 .3c-1.5.2-3 0-4.4.2-1.2.2-2.5 0-3.6.2-1.3.3-2.5 0-3.7.3-1.5.3-3 0-4.4.2-1.2.2-2.5 0-3.8.3-1.2.2-2.4 0-3.5.2a19.8 19.8 0 0 1-2.9.2l-1.4.2a5.7 5.7 0 0 0-1.5 0 17.2 17.2 0 0 1-2.1.3c-1 .2-2 0-3 .2-.8.2-1.7 0-2.4.3-.8.2-1.7 0-2.4.2a17 17 0 0 1-3 .2l-1.4.2a7.1 7.1 0 0 0-1.4 0 15.3 15.3 0 0 1-2.5.3c-1.2.2-2.5 0-3.6.3-1 .1-1.8 0-2.7.2-.8.2-1.6.1-2.4.2l-1.3.3a2.5 2.5 0 0 0-1.4.2c-.3.2-1 0-1.4 0-.6.3-1.3.1-2 .3a6.7 6.7 0 0 1-2 .2h-2a19.5 19.5 0 0 1-3.3-.6l-.3-.1a2 2 0 0 0-.6 0Z"/%3E%3C/svg%3E';
				$inline = "
				.is-style-underline-brush .has-underline {
					position: relative;
					z-index: 1;
					text-decoration: none!important;
				}
				.is-style-underline-brush .has-underline::after {
					content: '';
					position: absolute;
					left: 0;
					right: 0;
					bottom: -.5em;
					height: 1em;
					width: 100%;
					max-width: 100%;
					margin: 0;
					background: var(--wp--preset--color--primary, currentColor);
					-webkit-mask-repeat: no-repeat;
					-webkit-mask-position: center;
					-webkit-mask-size: 100%;
					-webkit-mask-image: url('$symbol');
					mask: url('$symbol');
					z-index: -1;
				}
				";
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
		return "
		:where(h1,h2,h3,h4,h5,h6).has-background {
			padding: .125em .25em;
		}
		";
	}
}
