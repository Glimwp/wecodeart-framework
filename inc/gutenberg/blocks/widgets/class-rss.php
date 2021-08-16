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
 * @version		5.0.0
 */

namespace WeCodeArt\Gutenberg\Blocks\Widgets;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Core\Media;
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
	 * Shortcircuit Register
	 */
	public function register_block_type() {
		add_filter( 'render_block_core/rss', [ $this, 'render' ], 20, 2 );
	}

	/**
	 * Dynamically renders the `core/rss` block.
	 *
	 * @param 	string 	$content 	The block markup.
	 * @param 	array 	$block 		The parsed block.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $content = '', $block = [], $data = null ) {
		$attributes = get_prop( $block, 'attrs', [] );

		$rss = fetch_feed( $attributes['feedURL'] );

		if ( is_wp_error( $rss ) ) {
			return '<div class="components-placeholder"><div class="notice notice-error"><strong>' . __( 'RSS Error:' ) . '</strong> ' . $rss->get_error_message() . '</div></div>';
		}

		if ( ! $rss->get_item_quantity() ) {
			return '<div class="components-placeholder"><div class="notice notice-error">' . __( 'An error has occurred, which probably means the feed is down. Try again later.' ) . '</div></div>';
		}

		$rss_items  = $rss->get_items( 0, get_prop( $attributes, [ 'itemsToShow' ], 5 ) );

		$display_author		= get_prop( $attributes, [ 'displayAuthor' ], false );
		$display_date		= get_prop( $attributes, [ 'displayDate' ], false );
		$display_excerpt	= get_prop( $attributes, [ 'displayExcerpt' ], false );

		$classnames = [ 'wp-block-posts', 'wp-block-posts--rss' ];

		if ( $display_author ) {
			$classnames[] = 'has-avatars';
		}
		if ( $display_date ) {
			$classnames[] = 'has-dates';
		}
		if ( $display_excerpt ) {
			$classnames[] = 'has-excerpts';
		}
		if ( get_prop( $attributes, [ 'blockLayout' ], false ) ) {
			$cols = get_prop( $attributes, [ 'columns' ], '2' );
			$classnames[] = 'is-grid row row-cols-md-2 row-cols-lg-' . $cols;
		}

		$classnames[] = 'list-unstyled';
		$classnames[] = 'mb-0';

		$content = Markup::wrap( 'wp-block-rss', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'class' => implode( ' ', $classnames )
				]
			]
		], function( $posts, $attributes) {
			
			$display_author		= get_prop( $attributes, [ 'displayAuthor' ], false );
			$display_date		= get_prop( $attributes, [ 'displayDate' ], false );
			$display_excerpt	= get_prop( $attributes, [ 'displayExcerpt' ], false );
			
			$list_items_markup 	= '';

			foreach ( $posts as $item ) {
				$list_items_markup .= '<li class="wp-block-posts__post mb-3">';
				$list_items_markup .= '<div>';

				// Title
				$title = esc_html( trim( strip_tags( $item->get_title() ) ) );
				if ( empty( $title ) ) {
					$title = __( '(no title)' );
				}
				$link = esc_url( $item->get_link() );
				if ( $link ) {
					$title = "<a href='{$link}'>{$title}</a>";
				}
				$list_items_markup .= "<h3 class='wp-block-post-title'>{$title}</h3>";

				// Meta
				if( $display_author || $display_date ) {
					$list_items_markup .= '<div class="wp-block-posts__post-meta d-flex align-items-center text-muted mb-3">';
					// Author
					if ( $display_author ) {
						$author = $item->get_author();
						if ( is_object( $author ) ) {
							$author = $author->get_name();
						}

						$list_items_markup .= wecodeart_template( 'entry/meta/author', [
							'attributes'=> [
								'isLink'	=> false,
								'className' => 'd-inline-flex me-3',
							],
							'author' 	=> (object) [
								'name'  => $author,
							]
						], false );
					}

					// Date
					if ( $display_date ) {
						$date = $item->get_date( 'U' );
						$list_items_markup .= wecodeart_template( 'entry/meta/date', [
							'attributes'=> [
								'className' => 'd-inline-flex me-3',
							],
							'published'	=> [
								'robot'	=> date_i18n( get_option( 'c' ), $date ),
								'human'	=> date_i18n( get_option( 'date_format' ), $date )
							]
						], false );
					}
					$list_items_markup .= '</div>';
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

					$list_items_markup .= sprintf( '<div class="%1$s">%2$s</div>', 'wp-block-post-excerpt', $excerpt );
				}

				$list_items_markup .= "</div>\n";
				$list_items_markup .= "</li>\n";
			}

			echo $list_items_markup;

		}, [ $rss_items, $attributes ], false );

		return $content;
	}
}
