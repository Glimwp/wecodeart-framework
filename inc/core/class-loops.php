<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Loops
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since		3.5
 * @version		4.2.0
 */ 

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Markup;
use WeCodeArt\Singleton;

/**
 * Loops
 */
class Loops {

	use Singleton;

	/**
	 * Standard WP Loop, meant to be executed anywhere needed in templates.
	 *
	 * @since	1.0
	 * @version	3.9.5
	 *
	 * @return 	void
	 */
	public static function default() {
		global $wp_query;

		do_action( 'wecodeart/hook/loop/before' );
		
		// The WordPress Loop.
		if( have_posts() ) :

			do_action( 'wecodeart/hook/loop/while/before' );

			while( have_posts() ) : the_post();

				$post_id 	= get_the_ID();
				$index 		= $wp_query->current_post;
				
				do_action( 'wecodeart/hook/loop/entry/before', $post_id, $index );

				/**
				 * Entry Hook
				 * 
				 * @see 	WeCodeArt\Markup::wrap()
				 * @see		do_action(); WP Function
				 * @hook	'wecodeart/entry' 	
				 * @hooked 	{
				 * - WeCodeArt\Core\Entry->render_header()	- 20 Entry Header Hook
				 * - WeCodeArt\Core\Entry->render_content()	- 30 Entry Content Hook 
				 * - WeCodeArt\Core\Entry->render_footer()	- 40 Entry Footer Hook
				 * }
				 */
				Markup::wrap( 'entry', [ [
					'tag' 	=> 'article',
					'attrs' => [
						'id' 	=> get_post_type() . '-' . $post_id, 
						'class'	=> implode( ' ', get_post_class() ) 
					]
				] ], 'do_action', [ 'wecodeart/entry', $post_id, $index  ] );

				do_action( 'wecodeart/hook/loop/entry/after', $post_id, $index );

			endwhile; // end of one post.

			do_action( 'wecodeart/hook/loop/while/after' );

		else : // if no posts exist.

			do_action( 'wecodeart/hook/loop/else' );

		endif; // end loop.

		do_action( 'wecodeart/hook/loop/after' );
	}

	/**
	 * Content WP Loop, meant to used to render custom content.
	 *
	 * @since	4.2.0
	 * @version	4.2.0
	 *
	 * @return 	void
	 */
	public static function render_content( $id ) {
		$blocks = parse_blocks( get_post( $id )->post_content );
		// Checking if empty is redundant anyway / echo without sanitize since is using WP function
		foreach( $blocks as $block ) echo render_block( $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}