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
 * @since		5.2.2
 * @version		5.2.2
 */

namespace WeCodeArt\Gutenberg\Blocks\Comment;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Markup\SVG;
use WeCodeArt\Singleton;
use WeCodeArt\Gutenberg\Blocks\Dynamic;
use function WeCodeArt\Functions\get_prop;

/**
 * Gutenberg Post Template block.
 */
class Template extends Dynamic {

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
	protected $block_name = 'comment-template';

	/**
	 * Shortcircuit Register
	 */
	public function register() {
		add_filter( 'block_type_metadata_settings', [ $this, 'filter_render' 	], 10, 2 );
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
	 * Dynamically renders the `core/post-template` block.
	 *
	 * @param 	array 	$attributes The block attributes.
	 *
	 * @return 	string 	The block markup.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		$post_id = $block->context['postId'];

        // Bail out early if the post ID is not set for some reason.
        if ( ! isset( $post_id ) ) {
            return '';
        }

		if( post_password_required( $post_id ) ) {
			return Markup::wrap( 'comment-form-protected', [ [
				'tag' 	=> 'p',
				'attrs' => [
					'class' => 'alert alert-danger shadow-soft'
				]
			] ], 'printf', [ 
				esc_html__( 'This post is password protected. Enter the password to view comments or leave a comment.', 'wecodeart' )
			], false );
		}

		$number = $block->context['queryPerPage'];

        // Get an array of comments for the current post.
        $comments = get_approved_comments( $post_id, [ 'number' => $number ] );

        if ( count( $comments ) === 0 ) {
            return '';
        }

		// Content
		$content = '';

		// Head
		$content .= $this->get_head();

		// List
        $content .= Markup::wrap( 'wp-block-comments-list', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'id'	=> 'comments',
					'class' => 'wp-block-comments-query-loop__list list-unstyled'
				]
			]
		], function( $comments, $block ) {

			foreach ( $comments as $comment ) {

				Markup::wrap( 'wp-block-comment', [
					[
						'tag' 	=> 'li',
						'attrs'	=> [
							'id'	=> 'comment-' . $comment->comment_ID,
							'class' => implode( ' ', get_comment_class( 'mb-5', $comment ) )
						]
					]
				], function( $block, $comment ) {

					echo ( new \WP_Block( $block->parsed_block, [ 
                        'commentId' => $comment->comment_ID
                    ] ) )->render( [ 'dynamic' => false ] );

				}, [ $block, $comment ] );

			}

		}, [ $comments, $block ], false );

		// Pagination
		$content .= $this->get_pagination();

		return $content;
	}

	/**
	 * Render Comments Info
	 *
	 * @since	5.2.2
	 * @version	5.2.2
	 *
	 * @return 	string
	 */
	public function get_head() {
		$defaults = [
			'icon' 		=> SVG::compile( 'comments', [ 'class' => 'me-2' ] ), // Escaped with kses inside fn.
			'empty' 	=> esc_html__( 'No comments', 'wecodeart' ),
			'closed'	=> false,
			'add_one'	=> esc_html__( 'add one', 'wecodeart' ) 
		]; 

		$args = apply_filters( 'wecodeart/filter/comments/head/args', $defaults, get_post_type() );

		$comments_number = intval( get_comments_number() );

		$icon_html 	= '';
		$header_tx 	= '';
		if( comments_open() || $comments_number !== 0 ) {
			$icon_html = (string) $args['icon'];
			if ( 0 !== $comments_number ) {
				$header_tx = sprintf(
					_nx( '%1$s comment', '%1$s comments', $comments_number, 'comments title', 'wecodeart' ),
					number_format_i18n( $comments_number )
				);
			} else {
				$header_tx = (string) $args['empty']; 
			} 
		} else {
			if( $args['closed'] !== false && is_string( $args['closed'] ) ) $header_tx = $args['closed']; 
		} 

		// Prepare HTML output
		$output = sprintf( '%1$s %2$s', $icon_html, $header_tx );

		// Append `add comment` link
		if( comments_open() ) {
			$output .= sprintf(
				'<a class="comments__add-new float-end has-small-font-size my-2" href="#respond" rel="nofollow">%s</a>',
				(string) $args['add_one']
			); 
		}

		return Markup::wrap( 'wp-block-comments-head', [ [
			'tag' 	=> 'h3',
			'attrs' => [
				'class' => 'wp-block-comments-query-loop__head'
			]
		] ], $output, [], false );
	}
	
	/**
	 * Render Comments Pagination - not working yet but it will be implemented.
	 *
	 * @since 	5.2.2
	 * @version 5.2.2
	 *
	 * @return 	string|null
	 */
	public function get_pagination() {
		/**
		 * Early Break
		 */
		if( empty( get_previous_comments_link() || get_next_comments_link() ) ) return;

		return Markup::wrap( 'wp-block-comments-nav', [
			[
                'tag'   => 'nav',
                'attrs' => [
					'class' 		=> 'wp-block-comments-query-loop__pagination',
					'aria-label'    => esc_html__( 'Comments Navigation', 'wecodeart' ),
                ]
            ],
			[
                'tag'   => 'div',
                'attrs' => [
                    'class' => 'row pb-3'
                ]
            ]
		], function() {
			?>
            <h3 class="screen-reader-text"><?php esc_html_e( 'Comments Navigation', 'wecodeart' ); ?></h3>
			<?php

			Markup::wrap( 'comments-prev-link', [ [
				'tag' 	=> 'div',
				'attrs' => [ 
					'class' => 'col-sm-12 col-md'
				] 
			] ], 'previous_comments_link' );
	
			Markup::wrap( 'comments-next-link', [ [
				'tag' 	=> 'div',
				'attrs' => [
					'class' => 'col-sm-12 col-md text-md-end'
				] 
			] ], 'next_comments_link' );
		}, [], false );
    }
}
