<?php
/**
 * WeCodeArt Framework
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package		WeCodeArt Framework
 * @subpackage  Gutenberg\Blocks
 * @copyright   Copyright (c) 2022, WeCodeArt Framework
 * @since		5.0.0
 * @version		5.6.7
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Query Latest Posts block.
 */
class Posts extends Dynamic {

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
	protected $block_name = 'latest-posts';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		\add_action( 'wp_print_styles', function() {
			\wp_deregister_style( 'wp-block-' . $this->block_name );
		} );

		\add_filter( 'block_type_metadata_settings', [ $this, 'filter_render' ], 10, 2 );
	}

	/**
	 * Filter block markup
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
	 * Dynamically renders the `core/latest-posts` block.
	 *
	 * @param 	string 	$attributes	The block attrs.
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		global $block_core_latest_posts_excerpt_length;

		$args = [
			// 'queryId'	=> '7',
			'displayLayout' => get_prop( $attributes, [ 'postLayout' ] ) ? [
				'columns'	=> get_prop( $attributes, [ 'columns' ], null ),
				'type' 		=> get_prop( $attributes, [ 'postLayout' ], 'list' ) === 'grid' ? 'flex' : 'list' 
			] : null,
			'query' 	=> [
				'post_type' => 'post',
				'perPage' 	=> get_prop( $attributes, 'postsToShow', 5 ),
				'pages'		=> 0,
				'offset'	=> 0,
				'order'		=> get_prop( $attributes, 'order' ),
				'orderBy'	=> get_prop( $attributes, 'orderBy' ),
				'author'		=> get_prop( $attributes, 'selectedAuthor', '' ),
				'categoryIds' 	=> get_prop( $attributes, 'categories', [] ),
				'search'	=> '',
				'exclude'	=> '',
				'sticky'	=> '',
				'inherit'	=> false
			],
		];

		$display_author		= get_prop( $attributes, [ 'displayAuthor' ], false );
		$display_image		= get_prop( $attributes, [ 'displayFeaturedImage' ], false );
		$display_date		= get_prop( $attributes, [ 'displayPostDate' ], false );
		$display_content	= get_prop( $attributes, [ 'displayPostContent' ], false );

		// Start Template
		$template = '<!-- wp:query ' . wp_json_encode( $args ) . ' -->';
		$template .= '<div class="wp-block-query">';
		$template .= '<!-- wp:post-template {"className":"wp-block-post-template--latest"} -->';
		
		// Image
		if ( $display_image ) {
			$align	= (string) get_prop( $attributes, [ 'featuredImageAlign' ], 'none' );
			$linked	= (string) get_prop( $attributes, [ 'addLinkToFeaturedImage' ], 'false' );
			$template .= '<!-- wp:post-featured-image {"align":"' . $align . '","isLink":' . $linked . '} /-->';
		}

		$template .= '<!-- wp:post-title {"level":3,"isLink":true} /-->';
		
		// Meta
		if( $display_author || $display_date ) {
			$template .= '<!-- wp:group {"className":"hstack gap-1 mb-1","layout":{"type":"flex","flexWrap":"wrap"}} -->';
			$template .= '<div class="wp-block-group hstack gap-1 mb-1">';

			// Author
			if ( $display_author ) {
				$template .= '<!-- wp:post-author {"showAvatar":false} /-->';
			}

			// Date
			if ( $display_date ) {
				$template .= '<!-- wp:post-date /-->';
			}

			$template .= '</div>';
			$template .= '<!-- /wp:group -->';
		}

		// Content
		if ( $display_content ) {
			$content_type	= get_prop( $attributes, [ 'displayPostContentRadio' ], 'excerpt' );
			if( $content_type === 'excerpt' ) {
				$length	= (string) get_prop( $attributes, 'excerptLength', 55 );
				$template .= '<!-- wp:post-excerpt {"moreText":"Continue reading"} /-->';
			}
			if( $content_type === 'full_post' ) {
				$template .= '<!-- wp:post-content /-->';
			}
		}
		
		$template .= '<!-- /wp:post-template -->';
		$template .= '</div>';
		$template .= '<!-- /wp:query -->';

		// Allow users to change this template
		$template = apply_filters( 'wecodeart/filter/gutenberg/latest-posts/template', parse_blocks( $template ), $attributes );
		// End Template
		
		$blocks = new \WP_Block_List( $template, $args );
		
		$content = '';

		$block_core_latest_posts_excerpt_length = get_prop( $attributes, 'excerptLength', 55 );
        add_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );
		foreach( $blocks as $block ) $content .= $block->render( $block );
		remove_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

		return $content;
	}
}
