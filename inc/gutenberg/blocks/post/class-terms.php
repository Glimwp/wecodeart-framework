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
 * @version		6.1.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Post;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Site Terms block.
 */
class Terms extends Dynamic {

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
	protected $block_name = 'post-terms';

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
	 * Dynamically renders the `core/post-terms` block.
	 *
	 * @param 	array 	$attributes	The attributes.
	 * @param 	string 	$content 	The block markup.
	 * @param 	string 	$block 		The block data.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [], string $content = '', $block = null ): string {
		if ( ! isset( $block->context['postId'] ) || ! isset( $attributes['term'] ) ) {
			return '';
		}

		$terms = get_the_terms( $block->context['postId'], $attributes['term'] );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}

		add_filter( 'wecodeart/filter/template/context', [ $this, 'filter_tags_context' ], 10 );

		$args = [
			'post_id'		=> $block->context['postId'],
			'count'			=> count( $terms ),
			'attributes'	=> $attributes,
			'terms'			=> str_replace(
				'<a href=',
				'<a class="wp-block-post-terms__link" href=',
				get_the_term_list( $block->context['postId'], $attributes['term'], '', ', ' )
			),
		];

		$template = wecodeart_template( 'blocks/post/terms', $args, false );

		remove_filter( 'wecodeart/filter/template/context',	[ $this, 'filter_tags_context' ], 10 );

		return $template;
	}

	/**
	 * Extend Terms block to change tag icon.
	 *
	 * @return 	array
	 */
	public function filter_tags_context( $args ) {
		$term = get_prop( $args, [ 'attributes', 'term' ], '' );

		if( in_array( $term, [ 'post_tag', 'product_tag' ] ) ) {
			$args['icon'] = 'tags';
		}
		
		return $args;
	}
}
