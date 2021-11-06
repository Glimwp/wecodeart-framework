<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Comments Template
 * @since 		1.0
 * @version		5.0.0
 */

use WeCodeArt\Markup;
use WeCodeArt\Core\Comments;

/**
 * Hooks
 */
if( post_password_required() ) {
	add_action( 'wecodeart/entry/comments', [ Comments::get_instance(),	'render_protected' 	], 20 );
} else {
	add_action( 'wecodeart/entry/comments', [ Comments::get_instance(),	'render_meta'		], 10 );
	add_action( 'wecodeart/entry/comments', [ Comments::get_instance(),	'render_pagination'	], 15 ); 
	add_action( 'wecodeart/entry/comments', [ Comments::get_instance(),	'render_comments'	], 20 );
	add_action( 'wecodeart/entry/comments', [ Comments::get_instance(),	'render_pings'		], 30 );
	add_action( 'wecodeart/entry/comments', [ Comments::get_instance(),	'render_pagination'	], 35 );
}

// Before Hook
do_action( 'wecodeart/hook/comments/before' );

// Comments
Markup::wrap( 'comments', [ [
	'tag' 	=> 'div',
	'attrs' => [
		'id' 	=> 'comments',
		'class' => 'comments'
	]
] ], 'do_action', [ 'wecodeart/entry/comments', get_post_type() ] );

// After Hook
do_action( 'wecodeart/hook/comments/after' );