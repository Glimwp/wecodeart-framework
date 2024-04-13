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
 * @version		6.4.1
 */

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Post Image block.
 */
class Image extends Dynamic {

	use Singleton;

	/**
	 * Block static.
	 *
	 * @var mixed
	 */
	protected static $ratio = null;

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
	protected $block_name = 'post-featured-image';

	/**
	 * Init.
	 */
	public function init() {
		set_post_thumbnail_size( 1920, 1080 );

		\add_filter( 'post_thumbnail_html', [ $this, 'filter_html' ], 20, 3 );
		\add_filter( 'render_block_' . $this->get_block_type(), [ $this, 'render' ], 20, 2 );
	}

	/**
	 * Dynamically renders the `core/cover` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( string $content = '', array $block = [] ): string {
		if ( get_prop( $block, [ 'attrs', 'isLink' ] ) ) {
			$content = wecodeart( 'dom' )::processor( $content );
			
			if( $content->next_tag( [
				'tag_name' 	=> 'A',
			] ) ) {
				$content->add_class( 'wp-block-post-featured-image__link' );
			}

			$content = $content->get_updated_html();
		}

		return $content;
	}

	/**
	 * Add placeholder
	 *
	 * @param 	string 	$html 		The image HTML.
	 * @param 	string 	$post 		The post ID.
	 * @param 	string 	$thumbnail 	The thumbnail ID.
	 *
	 * @return 	string 	The placeholder markup.
	 */
	public function filter_html( $html, $post, $thumbnail ) {
		if( $html === '' && $thumbnail === 0 ) {
			$placeholder = wecodeart_config( 'placeholder', false );

			if( $placeholder === false ) return $html;
			
			$source = get_prop( $placeholder, [ 'src' ] );

			$html 	= sprintf(
				get_prop( $placeholder, 'html', '<img class="%s" src="%s" alt="%s" />' ),
				'wp-block-post-featured-image__src',
				esc_url( $source, strpos( $source, 'data:image' ) !== false ? [ 'data' ] : null ),
				esc_attr( get_prop( $placeholder, [ 'text' ] ) )
			);
		}

		return preg_replace( '/(width|height)="\d+"\s/', "", $html );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return <<<CSS
			.wp-block-post-featured-image {
				position: relative;
				overflow: hidden;
				max-width: 100%;
				margin: 0 0 var(--wp--style--block-gap);
			}
			.wp-block-post-featured-image__link {
				display: block;
				height: 100%;
			}
			.wp-block-post-featured-image__overlay {
				position: absolute;
				inset: 0;
				background-color: black;
				opacity: var(--wp--bg--opacity);
			}
			.wp-block-post-featured-image__overlay.has-background-gradient {
				background-color: transparent;
			}
			.wp-block-post-featured-image img {
				width: 100%;
				height: 100%;
				object-fit: cover;
			}
			.wp-block-post-featured-image img:is(.lazyload,[data-placeholder-resp]) {
				transition: filter .5s cubic-bezier(.6,-.28,.735,.045);
				filter: blur(5px);
			}	
			.wp-block-post-featured-image img:is(.lazyloaded,.litespeed-loaded) {
				filter: none;
			}
		CSS;
	}
}
