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
 * @version		5.1.8
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Core\Media;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use WeCodeArt\Gutenberg\Blocks\Post\Title;
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
		add_filter( 'render_block_core/latest-posts', [ $this, 'render' ], 20, 2 );
	}

	/**
	 * Dynamically renders the `core/latest-posts` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		global $post, $block_core_latest_posts_excerpt_length;

		$attributes = get_prop( $block, 'attrs', [] );

		$args = [
			'post_status'      => 'publish',
			'posts_per_page'   => get_prop( $attributes, 'postsToShow', 5 ),
			'order'            => get_prop( $attributes, 'order' ),
			'orderby'          => get_prop( $attributes, 'orderBy' ),
			'suppress_filters' => false,
		];

		$block_core_latest_posts_excerpt_length = get_prop( $attributes, 'excerptLength', 55 );
		add_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

		if ( $categories = get_prop( $attributes, 'categories', false ) ) {
			$args['category__in'] = array_column( $categories, 'id' );
		}

		if ( $selected_author = get_prop( $attributes, 'selectedAuthor', false ) ) {
			$args['author'] = $selected_author;
		}

		$recent_posts = get_posts( $args );

		if ( empty( $recent_posts ) ) return '';

		$classnames = [ 'wp-block-posts' ];

		if ( get_prop( $attributes, [ 'displayAuthor' ], true ) ) {
			$classnames[] = 'has-avatars';
		}
		if ( get_prop( $attributes, [ 'displayDate' ], true ) ) {
			$classnames[] = 'has-dates';
		}
		if ( get_prop( $attributes, [ 'displayExcerpt' ], true ) ) {
			$classnames[] = 'has-excerpts';
		}
		if ( get_prop( $attributes, [ 'postLayout' ], false ) ) {
			$cols = get_prop( $attributes, [ 'columns' ], '3' );
			$classnames[] = 'is-grid row row-cols-md-2 row-cols-lg-' . $cols;
		}

		$classnames[] = 'list-unstyled';
		$classnames[] = 'mb-0';

		$content = Markup::wrap( 'wp-block-latest-posts', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'class' => implode( ' ', $classnames )
				]
			]
		], function( $posts, $attributes ) {

			$link_image 		= get_prop( $attributes, [ 'addLinkToFeaturedImage' ], false );
			$display_author		= get_prop( $attributes, [ 'displayAuthor' ], false );
			$display_image		= get_prop( $attributes, [ 'displayFeaturedImage' ], false );
			$display_date		= get_prop( $attributes, [ 'displayPostDate' ], false );
			$display_content	= get_prop( $attributes, [ 'displayPostContent' ], false );
			$content_type		= get_prop( $attributes, [ 'displayPostContentRadio' ], 'excerpt' );
			$list_items_markup 	= '';

			foreach ( $posts as $post ) {
				$post_link = esc_url( get_permalink( $post ) );
				$list_items_markup .= '<li class="wp-block-posts__post mb-3">';
				$list_items_markup .= '<div class="row flex-column flex-sm-row gx-3">';

				// Image
				if ( $display_image ) {
					$image_classes	= [ 'wp-block-posts__post-image', 'ratio', 'overflow-hidden' ];
					$image_column 	= 'col-12';
					if ( $image_align = get_prop( $attributes, 'featuredImageAlign', 'center' ) ) {
						if( in_array( $image_align, [ 'left', 'right' ] ) ) {
							$image_column .= ' col-sm-4';
						}
						if( $image_align === 'right' ) {
							$image_column .= ' order-sm-last';
						}
					}

					if ( $link_image ) {
						$image_classes = array_diff( $image_classes, [ 'ratio' ] );
						$filter = function( $wrappers ) use ( $post_link ) {
							$wrappers = array_merge( $wrappers, [
								[
									'tag' 	=> 'a',
									'attrs'	=> [
										'href' 	=> $post_link,
										'class'	=> 'd-block ratio overflow-hidden',
									]
								]
							] );
			
							return $wrappers;
						};
			
						add_filter( 'wecodeart/filter/wrappers/get-media', $filter );
					}

					$featured_image = Media::get_image( [
						'post_id' 	=> $post->ID,
						'size'		=> get_prop( $attributes, 'featuredImageSizeSlug', 'thumbnail' ),
						'attrs' 	=> [
							'class' => implode( ' ', $image_classes )
						]
					], false );

					if( $link_image ) {
						remove_filter( 'wecodeart/filter/wrappers/get-media', $filter );
					}

					$list_items_markup .= sprintf( '<div class="%1$s">%2$s</div>', $image_column, $featured_image );
				}

				$list_items_markup .= '<div class="col-12 col-sm">';

				// Title
				$list_items_markup .= Title::get_instance()->render( [ 'isLink' => true ], '', (object) [
					'context' => [
						'postId' => $post->ID,
					]
				] );

				// Meta
				if( $display_author || $display_date ) {
					$list_items_markup .= '<div class="wp-block-posts__post-meta d-flex align-items-center text-muted mb-3">';
					// Author
					if ( $display_author ) {
						$list_items_markup .= wecodeart_template( 'meta/author', [
							'attributes'=> [
								'className' => 'd-inline-flex me-3',
							],
							'author' 	=> (object) [
								'name'  => get_the_author_meta( 'display_name', $post->post_author ),
								'url'	=> get_author_posts_url( $post->post_author )
							]
						], false );
					}

					// Date
					if ( $display_date ) {
						$list_items_markup .= wecodeart_template( 'meta/date', [
							'post_id'	=> $post->ID,
							'attributes'=> [
								'className' => 'd-inline-flex me-3',
							],
							'published'	=> [
								'robot'	=> get_the_date( DATE_W3C, $post ),
								'human'	=> get_the_date( '', $post )
							]
						], false );
					}
					$list_items_markup .= '</div>';
				}

				// Content
				if ( $display_content ) {
					$post_protected	= esc_html__( 'This content is password protected.', 'wecodeart' );
					$content_html 	= '<div class="%1$s">%2$s</div>';
					if( $content_type === 'excerpt' ) {
						$content 	= wpautop( get_the_excerpt( $post ) );
						$classname 	= 'wp-block-post-excerpt';
					}

					if( $content_type === 'full_post' ) {
						$content = wp_kses_post( html_entity_decode( $post->post_content, ENT_QUOTES, get_option( 'blog_charset' ) ) );
						$classname 	= 'wp-block-post-content';
					}
					
					if ( post_password_required( $post ) ) {
						$content = wpautop( $post_protected );
					}

					$list_items_markup .= sprintf( $content_html, $classname, $content );
				}

				$list_items_markup .= "</div>\n";
				$list_items_markup .= "</div>\n";
				$list_items_markup .= "</li>\n";
			}

			echo $list_items_markup;

		}, [ $recent_posts, $attributes ], false );

		remove_filter( 'excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20 );

		return $content;
	}
}
