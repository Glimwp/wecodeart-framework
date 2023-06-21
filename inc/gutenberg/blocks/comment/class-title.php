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
 * @since		5.5.8
 * @version		6.0.0
 */

namespace WeCodeArt\Gutenberg\Blocks\Comment;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Comments Title block.
 */
class Title extends Dynamic {

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
	protected $block_name = 'comments-title';

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
	 * Dynamically renders the `core/comments-title` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( array $attributes = [] ): string {
		$show_post_title     = get_prop( $attributes, [ 'showPostTitle' ] );
		$show_comments_count = get_prop( $attributes, [ 'showCommentsCount' ] );
		$comments_count      = absint( get_comments_number() );

		$output	= wecodeart( 'markup' )->SVG::compile( 'comments', [
			'class' => 'fa-fw'
		] );
		
		// No Comments
		if ( 0 === $comments_count ) {
			$header	= esc_html__( 'No responses', 'wecodeart' );
		// Both Count/Title Disabled
		} elseif( ! $show_post_title && ! $show_comments_count ) {
			$header = _nx( 'One response', 'Responses', $comments_count, 'comments title', 'wecodeart' );
		// Both Count/Title Enabled
		} elseif( $show_post_title && $show_comments_count ) {
			$header = sprintf(
				_nx( '%1$s response on "%2$s"', '%1$s responses on "%2$s"', $comments_count, 'comments title', 'wecodeart' ),
				number_format_i18n( $comments_count ),
				get_the_title()
			);
		// Only Title Enabled
		} elseif( $show_post_title ) {
			$header = sprintf(
				_nx( 'One response on "%1$s"', 'Responses on "%1$s"', $comments_count, 'comments title', 'wecodeart' ),
				get_the_title()
			);
		// Only Count Enabled
		} elseif( $show_comments_count ) {
			$header = sprintf(
				_nx( '%1$s response', '%1$s responses', $comments_count, 'comments title', 'wecodeart' ),
				number_format_i18n( $comments_count )
			);
		}

		$output .= sprintf( '<span>%s</span>', $header );

		if( comments_open() ) {
			// Required Utilities
			wecodeart( 'styles' )->Utilities->load( [ 'my-1', 'float-end' ] );

			$output .= sprintf(
				'<a class="comments__add-new float-end my-1 has-small-font-size" href="#respond" rel="nofollow">%s</a>',
				esc_html__( 'add one', 'wecodeart' )
			);
		}

		$classname = '';

		if( $align = get_prop( $attributes, [ 'textAlign' ] ) ) {
			$classname = 'has-text-align-' . $align;
		}
		
		return wecodeart( 'markup' )::wrap( 'wp-block-comments-title', [ [
			'tag' 	=> 'h' . get_prop( $attributes, [ 'level' ], 2 ),
			'attrs' => $this->get_block_wrapper_attributes( [
				'id'	=> 'comments',
				'class' => $classname
			] )
		] ], $output, [], false );
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public function styles() {
		return "
			.wp-block-comments-title:only-child {
				margin-bottom: 0;
			}
			.wp-block-comments-title svg {
				margin-right: .5rem;
			}
		";
	}
}
