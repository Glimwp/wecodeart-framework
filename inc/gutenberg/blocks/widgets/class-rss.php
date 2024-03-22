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
 * @version		5.4.9
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit;

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Query Latest Posts block.
 */
class RSS extends Dynamic {

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
	protected $block_name = 'rss';

	/**
	 * Init.
	 */
	public function init() {
		\add_filter( 'render_block_' . $this->get_block_type(), [ $this, 'render' ], 20, 2 );
	}

	/**
	 * Dynamically renders the `core/rss` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( string $content = '', $block = [] ) {
		$attributes = get_prop( $block, 'attrs', [] );

		$rss = fetch_feed( $attributes['feedURL'] );

		if ( is_wp_error( $rss ) ) {
			return '<div class="components-placeholder"><div class="notice notice-error"><strong>' . esc_html__( 'RSS Error', 'wecodeart' ) . ':</strong> ' . $rss->get_error_message() . '</div></div>';
		}

		if ( ! $rss->get_item_quantity() ) {
			return '<div class="components-placeholder"><div class="notice notice-error">' . esc_html__( 'An error has occurred, which probably means the feed is down. Try again later.', 'wecodeart' ) . '</div></div>';
		}

		$rss_items  = $rss->get_items( 0, get_prop( $attributes, [ 'itemsToShow' ], 5 ) );

		$display_author		= get_prop( $attributes, [ 'displayAuthor' ] );
		$display_date		= get_prop( $attributes, [ 'displayDate' ] );
		$display_excerpt	= get_prop( $attributes, [ 'displayExcerpt' ] );
		$is_grid_layout		= get_prop( $attributes, [ 'blockLayout' ] );

		$classnames = [ 'wp-block-rss' ];

		if ( $display_author ) {
			$classnames[] = 'has-avatars';
		}
		
		if ( $display_date ) {
			$classnames[] = 'has-dates';
		}

		if ( $display_excerpt ) {
			$classnames[] = 'has-excerpts';
		}

		if ( $is_grid_layout ) {
			$classnames[] = 'grid';
		}

		$classnames[] = 'list-unstyled';

		$content = wecodeart( 'markup' )::wrap( 'wp-block-rss', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'class' => implode( ' ', $classnames ),
					'style' => $is_grid_layout ? '--wp--columns:' . get_prop( $attributes, [ 'columns' ], '2' ) : null
				]
			]
		], function( $posts, $attributes ) {
			
			$display_author		= get_prop( $attributes, [ 'displayAuthor' ] );
			$display_date		= get_prop( $attributes, [ 'displayDate' ] );
			$display_excerpt	= get_prop( $attributes, [ 'displayExcerpt' ] );
			$columns 			= get_prop( $attributes, [ 'columns' ], 2 );
			
			$list_items_markup 	= '';

			$item_class = [ 'wp-block-rss__item' ];

			if ( get_prop( $attributes, [ 'blockLayout' ], false ) ) {
				$item_class[] = 'span-' . $columns;
				$item_class[] = 'span-lg-1';
			}

			foreach ( $posts as $item ) {
				$list_items_markup .= '<li class="' . implode( ' ', $item_class ) . '">';

				// Title
				$title = esc_html( trim( strip_tags( $item->get_title() ) ) );
				if ( empty( $title ) ) {
					$title = esc_html__( '(no title)', 'wecodeart' );
				}
				$link = esc_url( $item->get_link() );
				if ( $link ) {
					$title = "<a href='{$link}'>{$title}</a>";
				}
				$list_items_markup .= "<h3 class='wp-block-post-title wp-block-rss__item-title'>{$title}</h3>";

				// Author
				if ( $display_author ) {
					$author = $item->get_author();
					if ( is_object( $author ) ) {
						$author = $author->get_name();
					}

					$author_template = wecodeart_template( 'meta/author', [
						'attributes'=> [
							'isLink'	=> false,
							'className' => 'wp-block-rss__item-author'
						],
						'author' 	=> (object) [
							'name'  => $author,
						]
					], false );

					if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
						$author_template = strip_tags( $author_template, '<div><span>' );
					}

					$list_items_markup .= $author_template;
				}

				// Date
				if ( $display_date ) {
					$date = $item->get_date( 'U' );
					$date_template = wecodeart_template( 'meta/date', [
						'attributes'=> [
							'className' => 'wp-block-rss__item-publish-date'
						],
						'published'	=> [
							'robot'	=> date_i18n( get_option( 'c' ), $date ),
							'human'	=> date_i18n( get_option( 'date_format' ), $date )
						]
					], false );

					if( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
						$date_template = strip_tags( $date_template, '<div><span>' );
					}

					$list_items_markup .= $date_template;
				}

				// Excerpt
				if ( $display_excerpt ) {
					$excerpt = html_entity_decode( $item->get_description(), ENT_QUOTES, get_option( 'blog_charset' ) );
					$excerpt = esc_attr( wp_trim_words( $excerpt, get_prop( $attributes, 'excerptLength', 55 ), ' [&hellip;]' ) );

					// Change existing [...] to [&hellip;].
					if ( '[...]' === substr( $excerpt, -5 ) ) {
						$excerpt = substr( $excerpt, 0, -5 ) . '[&hellip;]';
					}

					$excerpt = wpautop( esc_html( $excerpt ) );

					$list_items_markup .= sprintf( '<div class="%1$s">%2$s</div>', 'wp-block-post-excerpt wp-block-rss__item-excerpt', $excerpt );
				}

				$list_items_markup .= "</li>\n";
			}

			echo $list_items_markup;

		}, [ $rss_items, $attributes ], false );

		return $content;
	}
}
