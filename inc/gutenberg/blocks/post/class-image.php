<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.2.4
 */

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Core\Media;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Post Image block.
 */
class Image extends Dynamic {

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
	protected $block_name = 'post-featured-image';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_image_size( 'wca_featured', 500, 300, false );
		add_filter( 'block_type_metadata_settings', [ $this, 'filter_render' ], 10, 2 );
	}

	/**
	 * Filter table markup
	 *
	 * @param	array 	$settings
	 * @param	array 	$data
	 */
	public function filter_render( $settings, $data ) {
		if ( $this->get_block_type() === $data['name'] ) {
			$settings = wp_parse_args( [
				'render_callback' => [ $this, 'render' ]
			], $settings );
		}
		
		return $settings;
	}

	/**
	 * Dynamically renders the `core/post-featured-image` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}
	
		$post_ID	= $block->context['postId'];
		$link 		= get_prop( $attributes, 'isLink', false );
		$classnames = [ 'wp-block-featured-image', 'ratio', 'overflow-hidden' ];

		if( $link ) {
			$classnames = array_diff( $classnames, [ 'ratio', 'overflow-hidden' ] );
			$filter = function( $wrappers ) use ( $post_ID ) {
				$wrappers = array_merge( $wrappers, [
					[
						'tag' 	=> 'a',
						'attrs'	=> [
							'href' 	=> get_permalink( $post_ID ),
							'class'	=> 'ratio',
						]
					]
				] );

				return $wrappers;
			};

			add_filter( 'wecodeart/filter/wrappers/get-media', $filter );
		}

		$image = Media::get_image( [
			'media_id' 	=> get_the_post_thumbnail( $post_ID ),
			'size'		=> 'wca_featured',
			'attrs' 	=> [
				'class' => implode( ' ', $classnames )
			]
		] );

		if( $link ) {
			remove_filter( 'wecodeart/filter/wrappers/get-media', $filter );
		}

		return $image;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
		.wp-block-featured-image .ration {
			overflow: hidden;
		}
		.wp-block-featured-image a {
			display: block;
		}
		.wp-block-featured-image :where(.ratio > *, img) {
			display: block;
			top: 50%;
			left: 50%;
			min-width: 100%;
			min-height: 100%;
			transform: translate3d(-50%,-50%,0);
			max-width: none;
			object-fit: cover;
		}
		.wp-block-featured-image :where(.ratio > *,img).match {
			width: 100%; 
			height: 100%;
		}
		.wp-block-featured-image :where(.ratio > *,img).taller {
			width: 100%; 
			height: auto;
		}
		.wp-block-featured-image :where(.ratio > *,img).wider {
			width: auto;
			height: 100%;
		}
		.wp-block-featured-image :where(.ratio > *,img)[data-placeholder-resp] {
			transition: filter .5s cubic-bezier(.6,-.28,.735,.045);
			filter: blur(5px);
		}	
		.wp-block-featured-image :where(.ratio > *,img).litespeed-loaded {
			filter: none;
		}
		";
	}
}
