<?php namespace WeCodeArt\Core;
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit();
// Use 
use WeCodeArt\Core\Entry;
/**
 * WeCodeArt Framework.
 *
 * WARNING: This file is part of the core WeCodeArt Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package 	WeCodeArt Framework
 * @subpackage 	Loops
 * @copyright   Copyright (c) 2019, WeCodeArt Framework
 * @since 		v3.5
 * @version		v3.5
 */ 
class Loops {
	/**
	 * Instance
	 *
	 * @var $_instance
	 */
	private static $_instance = NULL;

	private static $page_for_404 = NULL;
	/**
	 * Initiator
	 *
	 * @since 	v3.5
	 * @return 	object
	 */
	public static function get_instance() {
		if( self::$_instance == NULL ) self::$_instance = new self;
		return self::$_instance;
	}

	public function __construct() {
		self::$page_for_404 = get_theme_mod( 'page_for_404' );

		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Init hooks and actions
	 * @since	v3.5
	 * @version v3.5
	 */
	public function init() {
		if( self::$page_for_404 > 0 ) {
			add_filter( 'display_post_states', 			[ $this, 'filter_404_post_state' ], 10, 2 );
			// Remove 404 error page from Search Results
			add_action( 'pre_get_posts',				[ $this, 'exclude_404_page' ] );
			// Remove 404 error page from YOAST sitemap
			add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function () {
				return array( self::$page_for_404 );
			} );

			add_action( 'wp_head', function() {
				if( is_page( self::$page_for_404 ) ) {
					global $wp_query;
					$wp_query->set_404();
					status_header( 404 );
					
					do_action( 'wecodeart/hook/header/before' 	);	// Hook Before Header
					do_action( 'wecodeart_header_markup' 		);	// WeCodeArt Header
					do_action( 'wecodeart/hook/header/after' 	);	// Hook After Header
	
					do_action( 'wecodeart_inner_markup' );  		// Inner Template

					get_footer(); 

					exit(); 
				}
			} );
		}
	}

	/**
	 * Standard WP Loop, meant to be executed anywhere needed in templates.
	 * @since	v1.0
	 * @version v3.3
	 */
	public static function default() {
		do_action( 'wecodeart/hook/loop/before' );
		// The WordPress Loop
		if( have_posts() ) :
			do_action( 'wecodeart/hook/loop/while/before' );
			while( have_posts() ) : the_post();
				do_action( 'wecodeart_entry_open' );
				do_action( 'wecodeart_entry_header' );
				do_action( 'wecodeart_entry_content' );
				do_action( 'wecodeart_entry_footer' );
				do_action( 'wecodeart_entry_close' );
			endwhile; // end of one post
			do_action( 'wecodeart/hook/loop/while/after' );
		else : // if no posts exist
			do_action( 'wecodeart/hook/loop/else' );
		endif; // end loop
		do_action( 'wecodeart/hook/loop/after' );
	}

	/**
	 * Returns the 404 Page Loop
	 * @since	v3.5
	 * @version v3.5
	 */
	public static function fourofour() {
		if( self::$page_for_404 > 0 ) {
			global $wp_query;
			$wp_query = new \WP_Query( 'page_id=' . self::$page_for_404 );
			$wp_query->is_404 = true;
			status_header( 404 );
			// Remove 404 Error page Title
			$Entry = Entry::get_instance();
			remove_action( 'wecodeart_entry_header', [ $Entry, 'render_title_markup' ], 15 );
		}

		self::default();
		
		wp_reset_query();
	}

	/**
	 * Filter the 404 page state
	 */
	public function filter_404_post_state( $states, $post ) {
		if( self::$page_for_404 === $post->ID ) $states['page_for_404'] = __( '404 Page', 'wecodeart' ); 
		return $states;
	}

	/**
	 * Exclude 404 page from results
	 * @param  object $query WP_Query
	 */
	public function exclude_404_page( $query ) {
		$pageid = self::$page_for_404;
		if ( $pageid > 0 ) {
			global $pagenow;
			$post_type = $query->get( 'post_type' );
			if( ( is_admin() && ( 'edit.php' == $pagenow && ! current_user_can( 'create_users' ) ) ) || 
				( ! is_admin() && 
					( is_search() || 
						( ! empty( $post_type) && 
							( 
								( 'page' === $post_type || 'any' === $post_type ) || 
								( is_array( $post_type ) && in_array( 'page', $post_type ) ) 
							) 
						) 
					) 
				) 
			) {
				if ( is_admin() || ( ! is_admin() && is_search() ) ) $pageids = get_all_page_ids();
				else $pageids = array( $pageid ); 
				$query->set( 'post__not_in', array_merge( ( array ) $query->get( 'post__not_in', array() ), $pageids ) );
			}
		}
	}
}