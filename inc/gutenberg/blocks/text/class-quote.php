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
 * @since		5.0.0
 * @version		5.7.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Text;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Quote block.
 */
class Quote extends Dynamic {

	use Singleton;

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'core';

	/**
	 * Block namespace.
	 *
	 * @var string
	 */
	protected $block_name = 'quote';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		\add_filter( 'render_block_' . $this->get_block_type(), [ $this, 'render' ], 10, 2 );
	}

	/**
	 * Dynamically renders the `core/quote` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {		
		$dom = $this->dom( $content );
		
		// Quote Changes
		$quote	= $dom->getElementsByTagName( 'blockquote' )->item(0);
		if( $quote ) {
			$figure	= $dom->createElement( 'figure' );
			$figure->setAttribute( 'class', $quote->getAttribute( 'class' ) );
			$quote->setAttribute( 'class', 'blockquote' );
			$quote->parentNode->replaceChild( $figure, $quote );
			$figure->appendChild( $quote );
		}

		// Cite Changes
		$cite	= $dom->getElementsByTagName( 'cite' )->item(0);
		if( $cite ) {
			$caption = $dom->createElement( 'figcaption' );
			$caption->setAttribute( 'class', 'blockquote-footer' );
			$cite->parentNode->replaceChild( $caption, $cite );
			$caption->appendChild( $cite );
			$figure->appendChild( $caption );
		}

		return $dom->saveHTML();
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return '
		.wp-block-quote:not(.is-style-plain) {
			border-left: 4px solid var( --wp--preset--color--primary );
			padding-left: 1rem;
		}
		.wp-block-quote .blockquote {
			font-size: inherit;
		}
		.wp-block-quote .blockquote-footer {
			margin-top: -1rem;
			text-align: left;
			color: inherit;
			opacity: .8;
		}
		.wp-block-quote .blockquote-footer::before {
			content: "\2014\00A0";
		}
		';
	}
}
