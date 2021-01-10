<?php
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Core\Entry
 * @copyright   Copyright (c) 2021, WeCodeArt Framework
 * @since 		3.5
 * @version		4.2
 */

namespace WeCodeArt\Core;

defined( 'ABSPATH' ) || exit();

use WeCodeArt\Core\Loops;
use WeCodeArt\Core\Pagination;
use WeCodeArt\Markup;
use WeCodeArt\Singleton;
use WeCodeArt\Markup\SVG;

/**
 * Handles WP CPT outputs
 */
class Entry {

	use Singleton;

	/**
	 * Send to Constructor
	 * @since 3.6.2
	 */
	public function init() {
		add_filter( 'the_password_form', [ $this, 'render_pasword_protected' ] );

		add_action( 'wecodeart/entry', 	[ $this, 'render_header' 	], 20 ); 
		add_action( 'wecodeart/entry', 	[ $this, 'render_content' 	], 30 );
		add_action( 'wecodeart/entry',	[ $this, 'render_footer' 	], 40 );  
		
		add_action( 'wecodeart/hook/entry/header',	[ $this, 'render_title' 		], 10 );
		add_action( 'wecodeart/hook/entry/footer', 	[ $this, 'render_read_more'		], 10 );
        add_action( 'wecodeart/hook/entry/footer',  [ Pagination::get_instance(), 	'entry_content'		], 10 );
		add_action( 'wecodeart/hook/entry/footer', 	[ Author::get_instance(),		'author_box_single'	], 20 );
        add_action( 'wecodeart/hook/entry/footer',  [ Pagination::get_instance(), 	'entry_prev_next' 	], 40 );

		add_action( 'wecodeart/hook/loop/else', [ $this, 'render_no_posts' ], 10 );

		/**
		 * Child classes
		 */
		Entry\Meta::get_instance();
		Entry\Media::get_instance();
		Entry\Comments::get_instance();
	} 

	/**
	 * Render Header
	 *
	 * @since	3.6.4
	 * @version	3.9.5
	 * @uses	WeCodeArt\Markup::wrap()
	 *
	 * @return 	void
	 */
	public function render_header() {
		/**
		 * @see 	WeCodeArt\Markup::wrap()
		 * @see		do_action(); WP Function
		 * @hook	'wecodeart/hook/entry/header' 	
		 * @hooked 	{
		 * - WeCodeArt\Core\Entry->render_title()		- 10 Entry Title
		 * - WeCodeArt\Core\Entry\Media->render_image()	- 20 Entry Image ( Archive only ) 
		 * - WeCodeArt\Core\Entry\Meta->render()		- 30 Entry Meta Information ( author, date, comments etc )
		 * }
		 */
		Markup::wrap( 'entry-header', [ [
			'tag' 	=> 'header',
			'attrs' => [ 
				'class' => 'entry-header'
			]
		] ], 'do_action', [ 'wecodeart/hook/entry/header', get_the_ID() ] );  
	} 

	/**
	 * Render Footer
	 *
	 * @since	3.6.4
	 * @version	3.9.5
	 * @uses	WeCodeArt\Markup::wrap()
	 *
	 * @return 	void
	 */
	public function render_footer() {
		/**
		 * @see 	WeCodeArt\Markup::wrap()
		 * @see		do_action(); WP Function
		 * @hook	'wecodeart/hook/entry/footer' 	
		 * @hooked 	{
		 * - WeCodeArt\Core\Entry		->render_read_more()		- 10 Entry Read More ( Archive only )
		 * - WeCodeArt\Core\Entry		->render_author_box()		- 20 Author Info
		 * - WeCodeArt\Core\Comments	->get_comments_template()	- 30 Entry Comments
		 * - WeCodeArt\Core\Pagination	->post_content_nav()		- 10 Entry Nav ( Single only ) 
		 * - WeCodeArt\Core\Author		->author_box_single()		- 20 Author Box
		 * - WeCodeArt\Core\Pagination	->prev_next_post_nav()		- 40 Entry Prev/Next Nav
		 * }
		 */
		Markup::wrap( 'entry-footer', [ [
			'tag' 	=> 'footer',
			'attrs' => [ 
				'class' => 'entry-footer' 
			]
		] ], 'do_action', [ 'wecodeart/hook/entry/footer', get_the_ID() ] );
	} 

	/**
	 * Retrieve entry title with link
	 *
	 * @since 	3.6.4
	 * @version	3.9.5
	 *
	 * @param	bool	$link
	 * @param 	bool 	$echo 
	 *
	 * @return 	string
	 */
	public function the_title( $link = true, $echo = true ) {
		$disabled = apply_filters( 'wecodeart/filter/entry/title/disabled', false, get_the_ID() );

		$title = get_the_title();

		if ( 0 === mb_strlen( $title ) || $disabled === true ) {
			return;
		}
		
		if ( $link && ! is_singular() ) {
			$title = sprintf(
				'<a href="%s" class="text-dark" rel="bookmark">%s</a>', 
				esc_url( get_permalink() ), 
				esc_html( $title )
			);
		}
		
		if( $echo ) {
			echo wp_kses_post( $title );
			return;
		}

		return wp_kses_post( $title );
	}

	/**
	 * Echo the Entry Title Markup
	 *
	 * @since 	1.0
	 * @uses	WeCodeArt\Markup::wrap()
	 * @version 4.2
	 *
	 * @return 	void
	 */
	public function render_title( $link = true, $echo = true ) {
		Markup::wrap( 'entry-title', [ [
			'tag' 	=> is_singular() ? 'h1' : 'h2',
			'attrs' => [
				'class' => 'entry-title text-break text-wrap pt-3 mb-3'
			]
		] ], [ $this, 'the_title' ], [ $link, $echo ], $echo );
	}  

	/**
	 * Echo the Entry Content/Excerpt Markup
	 *
	 * @since	1.0
	 * @uses	WeCodeArt\Markup::wrap()
	 * @version 4.2.0
	 *
	 * @return	void
	 */
	public function render_content() {
		Markup::wrap( 'entry-content', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => is_singular() ? 'entry-content' : 'entry-excerpt text-muted'
			]
		] ], is_singular() ? 'the_content' : 'the_excerpt' );
	}

	/**
	 * Entry Meta Read More Template
	 *
	 * @since	1.0
	 * @version	3.9.6
	 *
	 * @param 	array	$args
	 * @param 	bool	$echo
	 *
	 * @return 	string
	 */
	public function render_read_more( $args = [], $echo = true ) {
		if ( is_singular() ) {
			return;
		}

		Markup::template( [ 'entry/read', 'more' ], wp_parse_args( $args, [
			'permalink'	=> get_the_permalink(),
			'title'		=> get_the_title(),
			'post_id' 	=> get_the_ID(),
		] ), $echo );
	}

	/**
	 * Return the content for No Posts
	 *
	 * @since	2.2
	 * @version	4.2
	 *
	 * @return	string
	 */
	public function render_no_posts() {
		Markup::wrap( 'entry-noposts', [ [
			'tag' 	=> 'div',
			'attrs' => [
				'class' => 'noposts-message w-100'
			]
		] ], esc_html( apply_filters( 
			'wecodeart/filter/entry/noposts_message', 
			__( 'There are no posts matching your criteria.', 'wecodeart' ) 
		) ) );
	}

	/**
	 * Return the content for No Posts
	 *
	 * @since	3.5
	 * @version	3.9.6
	 *
	 * @return 	void
	 */
	public function render_pasword_protected( $template ) {
		$template = Markup::template( 'entry/protected', [], false );
		$template = trim( preg_replace( '/\s+/', ' ', $template ) );
		$template = preg_replace( '/>\s*</', '><', $template );
		return $template;
	}
}