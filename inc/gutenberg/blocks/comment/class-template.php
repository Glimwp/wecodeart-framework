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
 * @since		5.2.2
 * @version		5.5.1
 */

namespace WeCodeArt\Gutenberg\Blocks\Comment;

defined( 'ABSPATH' ) || exit();

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
	 * Filter template markup
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
	 * Dynamically renders the `core/comment-template` block.
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

		if( $post_id !== 0 && post_password_required( $post_id ) ) {
			return wecodeart( 'markup' )::wrap( 'wp-block-comments-protected', [ [
				'tag' 	=> 'p',
			] ], 'printf', [ 
				esc_html__( 'This post is password protected. Enter the password to view comments or leave a comment.', 'wecodeart' )
			], false );
		}

        // Get what we need about comments for the current post.
		$comments_query		= new \WP_Comment_Query( self::build_args( $block ) );
        $comments 			= $comments_query->get_comments();
		$comments_number 	= count( $comments );

		$output	= wecodeart( 'markup' )->SVG::compile( 'comments' );
		
		if ( 0 === $comments_number ) {
			$header	= esc_html__( 'No comments', 'wecodeart' );
		} else {
			// Required Utilities
			wecodeart( 'styles' )->Utilities->load( [ 'ps-3', 'ps-md-5', 'mt-5', 'mb-5', 'my-1' ] );

			$header = sprintf(
				_nx( '%1$s comment', '%1$s comments', $comments_number, 'comments title', 'wecodeart' ),
				number_format_i18n( $comments_number )
			);
		}

		$output = sprintf( '%s %s', $output, $header );

		if( comments_open( $post_id ) ) {
			$output .= sprintf(
				'<a class="comments__add-new float-end my-1 has-small-font-size" href="#respond" rel="nofollow">%s</a>',
				esc_html__( 'add one', 'wecodeart' )
			);
		}
		
		$content = '';
		
		// Head
		$content .= wecodeart( 'markup' )::wrap( 'wp-block-comments-head', [ [
			'tag' 	=> 'h3',
			'attrs' => [
				'class' => 'wp-block-comments-query-loop__head'
			]
		] ], $output, [], false );

		// List
        $content .= wecodeart( 'markup' )::wrap( 'wp-block-comments-list', [
			[
				'tag' 	=> 'ul',
				'attrs'	=> [
					'id'	=> 'comments',
					'class' => 'wp-block-comments-query-loop__list list-unstyled'
				]
			]
		], [ __CLASS__, 'render_comments' ], [ $comments, $block ], false );

		return $content;
	}

	/**
	 * Render Threaded Comments
	 *
	 * @since	5.3.3
	 * @version	5.3.5
	 *
	 * @return 	string
	 */
	public static function render_comments( $comments, $block ) {
		$content = '';

		foreach ( $comments as $comment ) {
	
			$block_content = ( new \WP_Block( $block->parsed_block, [
				'commentId' => $comment->comment_ID,
			] ) )->render( [ 'dynamic' => false ] );
	
			$children = $comment->get_children();
	
			// If the comment has children, recurse to create the HTML for the nested comments.
			if ( ! empty( $children ) ) {
				$block_content .= wecodeart( 'markup' )::wrap( 'wp-block-comments-query-children', [
					[
						'tag' 	=> 'ol',
						'attrs'	=> [
							'class' => 'comment__children list-unstyled ps-3 ps-md-5 mt-5'
						]
					]
				], [ __CLASS__, 'render_comments' ], [ $children, $block ], false );
			}
	
			$content .= wecodeart( 'markup' )::wrap( 'wp-block-comment', [
				[
					'tag' 	=> 'li',
					'attrs'	=> [
						'id'	=> 'comment-' . $comment->comment_ID,
						'class' => implode( ' ', get_comment_class( 'mb-5', $comment ) )
					]
				]
			], $block_content, [], false );
		}
	
		// This is ok, we render gutenberg blocks here.
		echo $content;
	}

	/**
	 * Temporary until WP 5.9
	 *
	 * @return 	array
	 */
	public static function build_args( $block ) {
		$comment_args = array(
			'orderby'                   => 'comment_date_gmt',
			'order'                     => 'ASC',
			'status'                    => 'approve',
			'no_found_rows'             => false,
			'update_comment_meta_cache' => false, // We lazy-load comment meta for performance.
		);
		
		if ( ! empty( $block->context['postId'] ) ) {
			$comment_args['post_id'] = (int) $block->context['postId'];
		}

		if ( get_option( 'thread_comments' ) ) {
			$comment_args['hierarchical'] = 'threaded';
		} else {
			$comment_args['hierarchical'] = false;
		}

		$per_page = ! empty( $block->context['comments/perPage'] ) ? (int) $block->context['comments/perPage'] : 0;
		if ( 0 === $per_page && get_option( 'page_comments' ) ) {
			$per_page = (int) get_query_var( 'comments_per_page' );
			if ( 0 === $per_page ) {
				$per_page = (int) get_option( 'comments_per_page' );
			}
		}
		if ( $per_page > 0 ) {
			$comment_args['number'] = $per_page;
			$page                   = (int) get_query_var( 'cpage' );

			if ( $page ) {
				$comment_args['offset'] = ( $page - 1 ) * $per_page;
			} elseif ( 'oldest' === get_option( 'default_comments_page' ) ) {
				$comment_args['offset'] = 0;
			}
		}

		return $comment_args;
	}

	/**
	 * Block styles
	 *
	 * @return 	string 	The block styles.
	 */
	public static function styles() {
		return "
		.wp-block-comments-query-loop:empty {
			display: none;
		}
		.wp-block-comments-query-loop__head:only-child {
			margin-bottom: 0;
		}
		.wp-block-comments-query-loop__head svg {
			margin-right: .5rem;
		}
		";
	}
}
